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


use Barryvdh\Debugbar\LaravelDebugbar;
use Barryvdh\Debugbar\Middleware\Debugbar;
use Carbon\Carbon;
use Exception;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;


/**
 * Class DashboardController
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
        $dbg = app('debugbar');

        start_measure('loadDashboard');
        start_measure('loadHosts');
        $this->analysisManager->loadHosts($curUser, $md, false);
        stop_measure('loadHosts');

        // Cert loading and processing, tls scan, crtsh scan load
        start_measure('loadCerts');
        $this->analysisManager->loadCerts($md, false);
        stop_measure('loadCerts');

        Log::info('Certificate count: ' . var_export($md->getCerts()->count(), true));
        start_measure('loadWhois');
        $this->analysisManager->loadWhois($md);
        stop_measure('loadWhois');

        // Processing section
        start_measure('processExpiring');
        $this->analysisManager->processExpiring($md);
        stop_measure('processExpiring');

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
            'watch_to_tls_certs' => $md->getWatch2certsTls(),

            // Watch id -> [leaf certs ids] map.
            // Watch id mapping to the leaf certificates found by the latest CT scan.
            // 'watch_to_crtsh_certs' => $md->getWatch2certsCrtsh(),  // not used by dashboard right now

            // cert id to watch id (TLS)
            // 'crt_to_watch_tls' => $md->getCert2watchTls(), // not used by dashboard right now

            // cert id to watch id (CT)
            // 'crt_to_watch_crtsh' => $md->getCert2watchCrtsh(), // not used by dashboard right now

            // top domain id -> [watch ids]
            // Mapping of the top domain IDs to the related watch target ids (containing the top domain)
            // 'domain_to_watch' => $md->getTopDomainToWatch(), // not used by dashboard right now

            'certificates' => $this->thinCertsModel($md->getCerts())

        ];

        stop_measure('loadDashboard');
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