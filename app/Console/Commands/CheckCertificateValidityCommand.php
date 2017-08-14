<?php

namespace App\Console\Commands;

use App\Keychest\Services\ScanManager;
use App\Keychest\Services\ServerManager;
use App\Keychest\Utils\DataTools;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\VarDumper\Cloner\Data;

class CheckCertificateValidityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-validity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check certificate validity job & send emails with warnings';

    /**
     * Scan manager
     * @var ScanManager
     */
    protected $scanManager;

    /**
     * @var ServerManager
     */
    protected $serverManager;

    /**
     * Create a new command instance.
     * @param ServerManager $serverManager
     * @param ScanManager $scanManager
     */
    public function __construct(ServerManager $serverManager, ScanManager $scanManager)
    {
        parent::__construct();

        $this->serverManager = $serverManager;
        $this->scanManager = $scanManager;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = $this->loadUsers();
        foreach($users->all() as $user){
            $this->processUser($user);
        }

        return 0;
    }

    /**
     * Check for one particular user.
     * @param $user
     */
    protected function processUser($user){
        $activeWatches = $user->watchTargets()->get();  // type: Collection
        $activeWatches = $activeWatches->filter(function($value, $key){
            return empty($value->pivot->deleted_at);
        });
        $activeWatchesIds = $activeWatches->pluck('id');

        // Load all newest DNS scans for active watches
        $q = $this->scanManager->getNewestDnsScansOptim($activeWatchesIds);
        $dnsScans = $this->scanManager->processDnsScans($q->get());

        Log::info('--------------------');

        // Load latest TLS scans for active watchers for primary IP addresses.
        $q = $this->scanManager->getNewestTlsScansOptim($activeWatchesIds);
        $tlsScans = $this->scanManager->processTlsScans($q->get());

        // Latest CRTsh scan
        $crtshScans = $this->scanManager->getNewestCrtshScansOptim($activeWatchesIds)->get();
        $crtshScans = $this->scanManager->processCrtshScans($crtshScans);
        Log::info(var_export($crtshScans->count(), true));

        // Certificate IDs from TLS scans - more important certs.
        // Load also crtsh certificates.
        $tlsCertMap = DataTools::multiMap($tlsScans, function($item, $key){
            return empty($item->cert_id_leaf) ? [] : [$item->watch_id => $item->cert_id_leaf]; // wid => cid
        })->transform(function($item, $key) {
            return $item->unique()->values();
        });

        // watch_id -> leaf cert from the last tls scanning
        $tlsCertsIds = $tlsCertMap->flatten()->values()->reject(function($item){
            return empty($item);
        })->unique()->values();

        // watch_id -> array of certificate ids
        $crtshCertMap = DataTools::multiMap($crtshScans, function($item, $key){
            return empty($item->certs_ids) ? [] : [$item->watch_id => $item->certs_ids];  // wid => []
        }, true)->transform(function($item, $key) {
            return $item->unique()->values();
        });

        $crtshCertIds = $crtshScans->reduce(function($carry, $item){
            return $carry->union(collect($item->certs_ids));
        }, collect())->unique()->sort()->reverse()->take(300);

        // cert id -> watches contained in, tls watch, crtsh watch detection
        $cert2watchTls = DataTools::invertMap($tlsCertMap);
        $cert2watchCrtsh = DataTools::invertMap($crtshCertMap);

        $certsToLoad = $tlsCertsIds->union($crtshCertIds)->values()->unique()->values();
        $certs = $this->scanManager->loadCertificates($certsToLoad)->get();
        $certs = $certs->transform(
            function ($item, $key) use ($tlsCertsIds, $certsToLoad, $cert2watchTls, $cert2watchCrtsh) {
                $this->attributeCertificate($item, $tlsCertsIds->values(), 'found_tls_scan');
                $this->attributeCertificate($item, $certsToLoad->values(), 'found_crt_sh');
                $this->augmentCertificate($item);
                $this->addWatchIdToCert($item, $cert2watchTls, $cert2watchCrtsh);
                return $item;
            })->mapWithKeys(function ($item){
            return [$item->id => $item];
        });

        Log::info(var_export($certs->count(), true));

        // Whois scan load
        $topDomainsMap = $activeWatches->mapWithKeys(function($item){
            return empty($item->top_domain_id) ? [] : [$item->watch_id => $item->top_domain_id];
        });
        $topDomainToWatch = DataTools::invertMap($topDomainsMap);
        $topDomainIds = $activeWatches->reject(function($item){
            return empty($item->top_domain_id);
        })->pluck('top_domain_id')->unique();
        $whoisScans = $this->scanManager->getNewestWhoisScansOptim($topDomainIds)->get();
        $whoisScans = $this->scanManager->processWhoisScans($whoisScans);

        //
        // Processing section
        //



        //var_dump($activeWatchesIds->toJSON());
        //var_dump($activeWatches->toJSON());
        //Log::warning(var_export($activeWatches, true));
    }

    /**
     * Loads all users from the DB
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    protected function loadUsers(){
        return User::query()->get();
    }

    /**
     * Adds watch_id to the certificate record
     * @param $certificate
     * @param Collection $tls_map
     * @param Collection $crt_sh_map
     */
    protected function addWatchIdToCert($certificate, $tls_map, $crt_sh_map){
        $certificate->tls_watches = $tls_map->get($certificate->id, []);
        $certificate->crtsh_watches = $crt_sh_map->get($certificate->id, []);
    }

    /**
     * @param $certificate
     * @param \Illuminate\Support\Collection $idset
     * @param string $val
     */
    protected function attributeCertificate($certificate, $idset, $val)
    {
        $certificate->$val = $idset->contains($certificate->id);
        return $certificate;
    }

    /**
     * Extends certificate record
     * @param $certificate
     * @return mixed
     */
    protected function augmentCertificate($certificate)
    {
        $certificate->alt_names = json_decode($certificate->alt_names);
        $alts = collect($certificate->alt_names);
        if (!$alts->contains($certificate->cname)){
            $alts->push($certificate->cname);
        }

        $certificate->created_at_utc = $certificate->created_at->getTimestamp();
        $certificate->updated_at_utc = $certificate->updated_at->getTimestamp();
        $certificate->valid_from_utc = $certificate->valid_from->getTimestamp();
        $certificate->valid_to_utc = $certificate->valid_to->getTimestamp();

        $certificate->is_legacy = false;  // will be defined by a separate relation
        $certificate->is_expired = $certificate->valid_to->lt(Carbon::now());
        $certificate->is_le = strpos($certificate->issuer, 'Let\'s Encrypt') !== false;
        $certificate->is_cloudflare = $alts->filter(function($val, $key){
            return strpos($val, '.cloudflaressl.com') !== false;
        })->isNotEmpty();

        return $certificate;
    }
}
