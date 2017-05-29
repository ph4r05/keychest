<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.05.17
 * Time: 17:39
 */
namespace App\Http\Controllers;

use App\Events\ScanJobProgress;
use App\Jobs\ScanHostJob;
use App\Models\Certificate;
use App\Models\CertificateAltName;
use App\Models\HandshakeScan;
use App\Models\ScanJob;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;

class SearchController extends Controller
{
    // use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * IndexController constructor.
     */
    public function __construct()
    {

    }

    /**
     * Show the main index page
     *
     * @return Response
     */
    public function show()
    {
        $data = [];
        return view('index', $data);
    }

    /**
     * Performs the search / check
     *
     * @return Response
     */
    public function search()
    {
        list($newJobDb, $elDb) = $this->submitJob();

        $data = ['job_id' => $newJobDb['uuid']];
        return view('index', $data);
    }

    /**
     * Rest endpoint for job submit
     * @return \Illuminate\Http\JsonResponse
     */
    public function restSubmitJob()
    {
        list($newJobDb, $elDb) = $this->submitJob();
        $data = [
            'status' => 'success',
            'uuid' => $newJobDb['uuid'],
            'scan_scheme' => $newJobDb['scan_scheme'],
            'scan_port' => $newJobDb['scan_port'],
            'scan_host' => $newJobDb['scan_host'],
        ];

        //return response($json_data, 200)->header('Content-Type', 'application/json');
        return response()->json($data, 200);
    }

    /**
     * Returns current job state
     */
    public function restGetJobState()
    {
        $uuid = trim(Input::get('job_uuid'));
        $job = ScanJob::query()->where('uuid', $uuid)->first();
        if (empty($job)){
            return response()->json(['status' => 'not-found'], 404);
        }

        // TODO: improvement, sleep 1-2 seconds for an event change.

        $data = [
            'status' => 'success',
            'job' => $job,
        ];

        return response()->json($data, 200);
    }

    /**
     * Basic job results
     */
    public function restJobResults()
    {
        $uuid = trim(Input::get('job_uuid'));
        $job = ScanJob::query()->where('uuid', $uuid)->first();
        if (empty($job)){
            return response()->json(['status' => 'not-found'], 404);
        }
        if ($job->state != 'finished'){
            return response()->json(['status' => 'not-finished'], 201);
        }

        // TLS scan results
        $tlsHandshakeScans = HandshakeScan::query()->where('job_id', $job->id)->get();

        // Fetch corresponding certificates
        $handshakeCertsId = [];
        foreach ($tlsHandshakeScans as $scan){
            $handshakeCertsId += json_decode($scan->certs_ids);
        }

        $handshakeCertsId = array_unique($handshakeCertsId);
        $scanCerts = Certificate::query()->whereIn('id', $handshakeCertsId)->get();

        // Search based on alt domain names from the scan query
        $altNames = $this->altNames($job->scan_host);
        $altNamesCertIds = CertificateAltName::query()->select('cert_id')->distinct()
                ->whereIn('alt_name', [$job->scan_host] + $altNames)->get();

        // Search based on crt.sh search.
        $data = [
            'status' => 'success',
            'job' => $job,
            'tlsScans' => $tlsHandshakeScans,
            'scanCerts' => $scanCerts->map(function($item, $key) {
                return $this->restizeCertificate($item);
            }),


        ];

        return response()->json($data, 200);
    }

    /**
     * Modifies certificate record before sending out
     * @param $certificate
     */
    protected function restizeCertificate($certificate)
    {
        $certificate->pem = null;
        $certificate->alt_names = json_decode($certificate->alt_names);
        return $certificate;
    }

    /**
     * Alt names matching the given domain search.
     * test.alpha.dev.domain.com ->
     *   - *.alpha.dev.domain.com
     *   - *.dev.domain.com
     *   - *.domain.com
     * @param $domain
     * @return array
     */
    protected function altNames($domain)
    {
        $components = explode('.', $domain);

        // If % wildcard is present, skip.
        foreach ($components as $comp){
            if (strpos($comp, '%') !== false){
                return [];
            }
        }

        $result = [];
        $ln = count($components);
        for($i = 1; $i < $ln - 1; $ln++){
            $result[] = '*.' . join('.', array_slice($components, $i));
        }

        return $result;
    }

    /**
     * Submits the scan job to the queue
     * @return array
     */
    protected function submitJob()
    {
        $uuid = Uuid::generate()->string;
        $server = trim(Input::get('scan-target'));
        Log::info(sprintf('UUID: %s, target: %s', $uuid, $server));

        $parsed = parse_url($server);
        if (empty($parsed)){
            return view('index', []);
        }

        // DB Job data
        $newJobDb = [
            'uuid' => $uuid,
            'scan_scheme' => isset($parsed['scheme']) ? $parsed['scheme'] : null,
            'scan_host' => isset($parsed['host']) ? $parsed['host'] : $server,
            'scan_port' => isset($parsed['port']) ? $parsed['port'] : null,
            'state' => 'init',
            'user_ip' => request()->ip(),
            'user_sess' => substr(md5(request()->session()->getId()), 0, 16),
        ];

        $curUser = Auth::user();
        if (!empty($curUser)){
            $newJobDb['user'] = $curUser;
            $newJobDb['user_id'] = $curUser->getAuthIdentifier();
        }

        $elJson = $newJobDb;
        $elDb = ScanJob::create($newJobDb);

        // Queue entry to the scanner queue
        dispatch((new ScanHostJob($elDb, $elJson))->onQueue('scanner'));

        return [$newJobDb, $elDb];
    }

}

