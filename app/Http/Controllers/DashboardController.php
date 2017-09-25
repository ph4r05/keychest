<?php

/*
 * Dashboard controller, loading data.
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Keychest\DataClasses\ValidityDataModel;
use App\Keychest\Services\AnalysisManager;
use App\Keychest\Services\ScanManager;
use App\Keychest\Utils\DataTools;


use Carbon\Carbon;
use Exception;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;


/**
 * Class DashboardController
 * TODO: move logic to the manager, this will be needed also for reporting (scheduled emails)
 *
 * @package App\Http\Controllers
 */
class DashboardController extends Controller
{
    /**
     * Scan manager
     * @var ScanManager
     */
    protected $scanManager;

    /**
     * @var AnalysisManager
     */
    protected $analysisManager;

    /**
     * Create a new controller instance.
     *
     * @param ScanManager $scanManager
     */
    public function __construct(ScanManager $scanManager, AnalysisManager $analysisManager)
    {
        $this->middleware('auth');
        $this->scanManager = $scanManager;
        $this->analysisManager = $analysisManager;
    }

    public function loadActiveCerts()
    {
        $curUser = Auth::user();
        $md = new ValidityDataModel($curUser);

        // Host load, dns scans
        $this->analysisManager->loadHosts($curUser, $md, false);

        // Cert loading and processing, tls scan, crtsh scan load
        $this->analysisManager->loadCerts($md, false);

        Log::info('Certificate count: ' . var_export($md->getCerts()->count(), true));
        $this->analysisManager->loadWhois($md);

        // Processing section
        $this->analysisManager->processExpiring($md);

        // TODO: downtime computation?
        // TODO: CAs?
        // TODO: self signed?
        // TODO: custom PEM certs?
        // TODO: %. crtsh / CT wildcard search?
        // TODO: wildcard scan search - from neighbourhood

        // Search based on crt.sh search.
        $data = [
            'status' => 'success',
            'watches' => $this->thinWatches($md->getActiveWatches()),
            'wids' => $md->getActiveWatchesIds(),
            'dns' => $md->getDnsScans(),
            'whois' => $this->thinWhois($md->getWhoisScans()),
            'tls' => $md->getTlsScans(),

            // Watch id -> [leaf certs ids] map.
            // Watch id mapping to the leaf certificates found by the latest TLS scan.
            'tls_cert_map' => $md->getWatch2certsTls(),

            // As before, just with crtsh certificates
            'crtsh_cert_map' => $md->getWatch2certsCrtsh(),

            // cert id to watch id (tls)
            'crt_to_watch_tls' => $md->getCert2watchTls(),

            // cert id to watch id (ct)
            'crt_to_watch_crtsh' => $md->getCert2watchCrtsh(),
            'domain_to_watch' => $md->getTopDomainToWatch(),
            'certificates' => $this->thinCertsModel($md->getCerts())

        ];

        return response()->json($data, 200);
    }

    /**
     * Watches thinning - lightweight answer
     * @param Collection $watches
     * @return Collection
     */
    protected function thinWatches(Collection $watches){
        return $watches->map(function($item, $key){
            unset($item->dns_scan);
            unset($item->tls_scans);
            return $item;
        });
    }

    /**
     * Removes unnecessary data from the certs model - removes the serialization overhead.
     * @param Collection $certs
     * @return Collection
     */
    protected function thinCertsModel(Collection $certs){
        if (!$certs){
            return collect();
        }

        return $certs->map(function($item, $key){
            unset($item->tls_watches);
            unset($item->crtsh_watches);
            return $item;
        });
    }

    /**
     * Whois thinning - lightweight answer
     * @param Collection $whois
     * @return Collection
     */
    protected function thinWhois(Collection $whois){
        return $whois->map(function($item, $key){
            unset($item->dns);
            return $item;
        });
    }



}