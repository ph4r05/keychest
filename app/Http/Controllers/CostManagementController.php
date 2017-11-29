<?php

/*
 * Dashboard controller, loading data.
 */

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Keychest\DataClasses\ValidityDataModel;
use App\Keychest\Services\AnalysisManager;
use App\Keychest\Services\ScanManager;
use App\Keychest\Utils\CertificateTools;
use App\Keychest\Utils\Pricelist\CertificatePriceListProcessor;
use App\Keychest\Utils\DataTools;


use App\Models\CertificatePriceList;
use Barryvdh\Debugbar\LaravelDebugbar;
use Barryvdh\Debugbar\Middleware\Debugbar;
use Carbon\Carbon;
use Exception;


use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Log;


/**
 * Class CostManagementController
 *
 * @package App\Http\Controllers
 */
class CostManagementController extends Controller
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

    /**
     * Basic cost management view
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(){
        return view('cost_management');
    }

    /**
     * Data loading method
     * @return \Illuminate\Http\JsonResponse
     */
    public function loadActiveCerts()
    {
        $curUser = Auth::user();
        $md = new ValidityDataModel($curUser);

        // Host load, dns scans
        $dbg = app('debugbar');

        start_measure('loadCosts');
        start_measure('loadHosts');
        $this->analysisManager->loadHosts($curUser, $md, false);
        stop_measure('loadHosts');

        // Cert loading and processing, tls scan, crtsh scan load
        start_measure('loadCerts');
        $this->analysisManager->loadCerts($md, false);
        stop_measure('loadCerts');

        $priceList = CertificatePriceList::query()->get();
        $priceProc = new CertificatePriceListProcessor($priceList);

        $precerts = $this->thinCertsModel($md->getCerts());
        $precerts->transform(function($item, $key) use ($priceProc){
            $item->issuer_o = CertificateTools::normalizeIssuerOrg($item->issuer_o);

            $priceRule = $priceProc->findMatch($item);
            if (empty($priceRule)){
                $item->price_default = true;
                $priceProc->priceCertificate($item, $priceProc->defaultRule());

            } else {
                $priceProc->priceCertificate($item, $priceRule);
            }

            return $item;
        });

        // Search based on crt.sh search.
        $data = [
            'status' => 'success',
            'watches' => $this->thinWatches($md->getActiveWatches()),
            'wids' => $md->getActiveWatchesIds(),
            'tls' => $md->getTlsScans(),
            'price_list' => $priceList,

            // Watch id -> [leaf certs ids] map.
            // Watch id mapping to the leaf certificates found by the latest TLS scan.
            'watch_to_tls_certs' => $md->getWatch2certsTls(),

            'certificates' => $precerts
        ];

        stop_measure('loadCosts');
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




}