<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.08.17
 * Time: 18:16
 */

namespace App\Keychest\Services;

use App\Keychest\DataClasses\ValidityDataModel;
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DomainTools;
use App\Models\EmailNews;
use App\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AnalysisManager
{

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

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
     * Create a new Auth manager instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->serverManager = $app->make(ServerManager::class);
        $this->scanManager = $app->make(ScanManager::class);
    }

    /**
     * Load hosts & dns scans.
     * @param User $user
     * @param ValidityDataModel $md
     * @return $this
     */
    public function loadHosts(User $user, ValidityDataModel $md){
        $md->setActiveWatches($user->watchTargets()->get());

        $md->setActiveWatches($md->getActiveWatches()->filter(function($value, $key){
            return empty($value->pivot->deleted_at);
        })->keyBy('id'));
        $md->setActiveWatchesIds($md->getActiveWatches()->pluck('id'));

        // Load all newest DNS scans for active watches
        $q = $this->scanManager->getNewestDnsScansOptim($md->getActiveWatchesIds());
        $md->setDnsScans($this->scanManager->processDnsScans($q->get()));

        // Augment watches with DNS scans
        $md->getActiveWatches()->transform(function($item, $key) use ($md) {
            $item->dns_scan = $md->getDnsScans()->get($item->id);

            $strPort = empty($item->scan_port) ? 443 : intval($item->scan_port);
            $item->url = DomainTools::buildUrl($item->scan_scheme, $item->scan_host, $strPort);
            $item->url_short = DomainTools::buildUrl($item->scan_scheme, $item->scan_host, $strPort === 443 ? null : $strPort);
            $item->host_port = $item->scan_host . ($strPort == 0 || $strPort === 443 ? '' : ':' . $strPort);
            return $item;
        });

        return $this;
    }

    /**
     * Loads Scans & certificate related data
     * @param ValidityDataModel $md
     */
    public function loadCerts(ValidityDataModel $md){
        // Load latest TLS scans for active watchers for primary IP addresses.
        $q = $this->scanManager->getNewestTlsScansOptim($md->getActiveWatchesIds());
        $md->setTlsScans($this->scanManager->processTlsScans($q->get())->keyBy('id'));
        $md->setTlsScansGrp($md->getTlsScans()->groupBy('watch_id'));

        // Latest CRTsh scan
        $md->setCrtshScans($this->scanManager->getNewestCrtshScansOptim($md->getActiveWatchesIds())->get());
        $md->setCrtshScans($this->scanManager->processCrtshScans($md->getCrtshScans()));

        // Certificate IDs from TLS scans - more important certs.
        // Load also crtsh certificates.
        $md->setWatch2certsTls(DataTools::multiMap($md->getTlsScans(), function($item, $key){
            return empty($item->cert_id_leaf) ? [] : [$item->watch_id => $item->cert_id_leaf]; // wid => cid, TODO: CA leaf cert?
        })->transform(function($item, $key) {
            return $item->unique()->values();
        }));

        // mapping cert back to IP & watch id it was taken from
        $md->setCert2tls(DataTools::multiMap($md->getTlsScans(), function($item, $key){
            return empty($item->cert_id_leaf) ? [] : [$item->cert_id_leaf => $item->id]; // wid => cid, TODO: CA leaf cert?
        }));

        // watch_id -> leaf cert from the last tls scanning
        $md->setTlsCertsIds($md->getWatch2certsTls()->flatten()->values()->reject(function($item){
            return empty($item);
        })->unique()->values());

        // watch_id -> array of certificate ids
        $md->setWatch2certsCrtsh(DataTools::multiMap($md->getCrtshScans(), function($item, $key){
            return empty($item->certs_ids) ? [] : [$item->watch_id => $item->certs_ids];  // wid => []
        }, true)->transform(function($item, $key) {
            return $item->unique()->values();
        }));

        $md->setCrtshCertIds($md->getCrtshScans()->reduce(function($carry, $item){
            return $carry->union(collect($item->certs_ids));
        }, collect())->unique()->sort()->reverse()->take(300));

        // cert id -> watches contained in, tls watch, crtsh watch detection
        $md->setCert2watchTls(DataTools::invertMap($md->getWatch2certsTls()));
        $md->setCert2watchCrtsh(DataTools::invertMap($md->getWatch2certsCrtsh()));

        $md->setCertsToLoad($md->getTlsCertsIds()->union($md->getCrtshCertIds())->values()->unique()->values());
        $md->setCerts($this->scanManager->loadCertificates($md->getCertsToLoad())->get());
        $md->setCerts($md->getCerts()->transform(
            function ($item, $key) use ($md)
            {
                $this->attributeCertificate($item, $md->tlsCertsIds->values(), 'found_tls_scan');
                $this->attributeCertificate($item, $md->certsToLoad->values(), 'found_crt_sh');
                $this->augmentCertificate($item);
                $this->addWatchIdToCert($item, $md->cert2watchTls, $md->cert2watchCrtsh);
                $item->tls_scans_ids = $md->cert2tls->get($item->id, collect());
                $item->tls_watches = DataTools::pick($md->activeWatches, $md->cert2watchTls->get($item->id, []));
                $item->crtsh_watches = DataTools::pick($md->activeWatches, $md->cert2watchCrtsh->get($item->id, []));

                $this->addTlsScanIpsInfo($item, $md->tlsScans, $md->cert2tls);

                return $item;
            })->mapWithKeys(function ($item){
            return [$item->id => $item];
        }));
    }

    /**
     * Loads whois related data
     * @param ValidityDataModel $md
     */
    public function loadWhois(ValidityDataModel $md){
        // Whois scan load
        $md->setTopDomainsMap($md->getActiveWatches()->mapWithKeys(function($item){
            return empty($item->top_domain_id) ? [] : [$item->watch_id => $item->top_domain_id];
        }));

        $md->setTopDomainToWatch(DataTools::invertMap($md->getTopDomainsMap()));
        $md->setTopDomainIds($md->getActiveWatches()->reject(function($item){
            return empty($item->top_domain_id);
        })->pluck('top_domain_id')->unique());

        $md->setWhoisScans($this->scanManager->getNewestWhoisScansOptim($md->getTopDomainIds())->get());
        $md->setWhoisScans($this->scanManager->processWhoisScans($md->getWhoisScans()));
    }

    /**
     * Process expiring certificates, generates collections for emailing.
     * @param ValidityDataModel $md
     */
    public function processExpiring(ValidityDataModel $md){
        $md->setTlsCerts($md->getCerts()->filter(function ($value, $key) {
            return $value->found_tls_scan;
        })->sortBy('valid_to'));

        // 1. expiring certs in 7, 28 days, cert, domain, ip, when
        $md->setCertExpired($md->getTlsCerts()->filter(function ($value, $key) {
            return Carbon::now()->greaterThanOrEqualTo($value->valid_to);
        }));

        $md->setCertExpire7days($md->getTlsCerts()->filter(function ($value, $key) {
            return Carbon::now()->lessThanOrEqualTo($value->valid_to)
                && Carbon::now()->addDays(7)->greaterThanOrEqualTo($value->valid_to);
        }));

        $md->setCertExpire28days($md->getTlsCerts()->filter(function ($value, $key) {
            return Carbon::now()->addDays(7)->lessThanOrEqualTo($value->valid_to)
                && Carbon::now()->addDays(28)->greaterThanOrEqualTo($value->valid_to);
        }));
    }

    /**
     * Adds watch_id to the certificate record
     * @param $certificate
     * @param Collection $tls_map
     * @param Collection $crt_sh_map
     */
    protected function addWatchIdToCert($certificate, $tls_map, $crt_sh_map){
        $certificate->tls_watches_ids = $tls_map->get($certificate->id, []);
        $certificate->crtsh_watches_ids = $crt_sh_map->get($certificate->id, []);
    }

    /**
     * Watches & IP related certificate record augmentation
     * @param $certificate
     * @param $tlsScans
     * @param $cert2tls
     * @return mixed
     */
    protected function addTlsScanIpsInfo($certificate, $tlsScans, $cert2tls){
        $certificate->tls_watches->transform(function($item, $key) use ($certificate, $tlsScans, $cert2tls)
        {
            $item->tls_scans = DataTools::pick($tlsScans, $cert2tls->get($certificate->id, []))
                ->filter(function($item2, $key2) use ($item) {
                    return $item->id == $item2->watch_id;
                });

            // All IPs the certificate was found on the given watch
            $item->tls_ips = $item->tls_scans
                ->pluck('ip_scanned')
                ->sort()
                ->unique()
                ->pipe(function($col){
                    return DomainTools::sortByIPs($col, null);
                })
                ->values();

            // All IPs available
            $item->tls_ips_all = $item->dns_scan ?
                collect($item->dns_scan->dns)
                    ->sort()
                    ->unique()
                    ->transform(function($item, $key){
                        return $item[1];
                    })
                    ->pipe(function($col){
                        return DomainTools::sortByIPs($col, null);
                    })
                    ->values() : collect();

            $item->tls_ips_are_all = $item->dns_scan ? $item->tls_ips->count() == count($item->dns_scan->dns) : false;
            return $item;
        });

        $certificate->tls_watches = DomainTools::sortByDomains($certificate->tls_watches, 'scan_host');
        return $certificate;
    }

    /**
     * @param $certificate
     * @param \Illuminate\Support\Collection $idset
     * @param string $val
     * @return mixed
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
        $certificate->valid_to_days = ($certificate->valid_to->getTimestamp() - time()) / 3600.0 / 24.0;
        $certificate->validity_sec = $certificate->valid_to_utc - $certificate->valid_from_utc;

        $certificate->is_legacy = false;  // will be defined by a separate relation
        $certificate->is_expired = $certificate->valid_to->lt(Carbon::now());
        $certificate->is_le = strpos($certificate->issuer, 'Let\'s Encrypt') !== false;
        $certificate->is_cloudflare = $alts->filter(function($val, $key){
            return strpos($val, '.cloudflaressl.com') !== false;
        })->isNotEmpty();

        return $certificate;
    }

}
