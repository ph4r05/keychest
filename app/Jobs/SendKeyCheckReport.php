<?php

namespace App\Jobs;

use App\Keychest\Services\EmailManager;
use App\Keychest\Services\KeyCheck\PGPKeyResult;
use App\Keychest\Services\KeyCheck\SMIMEKeyResult;
use App\Mail\KeyCheckReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Class SendKeyCheckReport
 * Processes key check report, generates email from it.
 *
 * @package App\Jobs
 */
class SendKeyCheckReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Data with report
     * @var
     */
    public $reportData;

    /**
     * Options
     * @var Collection
     */
    protected $options;

    /**
     * @var EmailManager
     */
    protected $emailManager;

    /**
     * Create a new job instance.
     * @param Collection|array $options
     */
    public function __construct($report, $options = null)
    {
        $this->reportData = $report;
        $this->options = $options ? collect($options) : collect();
    }

    /**
     * Execute the job.
     * @param EmailManager $emailManager
     * @return void
     */
    public function handle(EmailManager $emailManager)
    {
        $this->emailManager = $emailManager;
        
        try{
            $this->tryHandle();
        } catch(\Exception $e){
            Log::warning('Exception in processing email job report: ' . $e);
        }
    }

    /**
     * Main handling logic, can throw an exception.
     */
    protected function tryHandle(){
        if (!isset($this->reportData->senders)
            || empty($this->reportData->senders)
            || !isset($this->reportData->results))
        {
            Log::info('Invalid job structure');
            return;
        }

        $sender = $this->reportData->senders[0];
        if (!is_array($sender)){
            $this->reportData->senders[0] = [$sender, $sender];
        }

        $senderEmail = $sender[count($sender)-1];
        $validator = Validator::make(['sender' => $senderEmail], [
            'sender' => 'required|email'
        ]);

        if ($validator->fails()){
            Log::info('Invalid email: ' . $sender);
            return;
        }

        $this->sendReport();
    }

    /**
     * Stub function for sending a report
     */
    protected function sendReport(){
        $sender = $this->reportData->senders[0];
        $senderEmail = $sender[count($sender)-1];

        $userObj = new \stdClass();
        $userObj->email = 'ph4r05@gmail.com';//$senderEmail;
        $userObj->name = $sender[0];

        $report = new KeyCheckReport();
        $this->processResults($report);

        $this->sendMail($userObj, $report, false);
    }

    /**
     * Processes results, fills in email template data.
     * @param $report
     */
    protected function processResults($report){
        $report->smimeKeys = [];
        $report->pgpKeys = [];
        $report->allSafe = true;

        foreach ($this->reportData->results as $result){
            $rtype = $result->type;

            if ($rtype == 'pgp-key' || $rtype == 'pgp-sign'){
                $this->processPgpKey($report, $result, $rtype == 'pgp-key');

            } elseif ($rtype == 'pkcs7'){
                $this->processSmimeKey($report, $result);

            } else {
                Log::warning('Unrecognized key type analysis: ' . $rtype);
            }
        }
    }

    /**
     * Processing SMIME key to the result model.
     * @param $report
     * @param $result
     */
    protected function processSmimeKey(KeyCheckReport $report, $result){
        if (!isset($result->fprint_sha256)){
            Log::info('Incorrect SMIME');
            return;
        }

        $res = new SMIMEKeyResult();
        $res->subject = empty($result->cname) ? $result->subject : $result->cname;
        if ($result->subject_email){
            $res->subject = $result->subject_email;
        }

        $res->fprint = $result->fprint_sha256;
        $res->createdAt = isset($result->not_before) && $result->not_before ?
            Carbon::createFromTimestampUTC($result->not_before) : null;

        $report->smimeKeys[] = $res;
        if (!isset($result->tests) || empty($result->tests)){
            $res->status = 'empty';
            return;
        }

        $test = $result->tests[0];
        if (!isset($test->n) || !isset($test->marked)){
            $res->status = 'invalid';
            return;
        }

        $res->modulus = $test->n;
        $res->bitSize = $this->getBitWidth($test->n);
        $res->marked = !!$test->marked;
        $report->allSafe &= !$res->marked;
        $res->status = 'ok';
    }

    /**
     * Processing PGP key to the result model.
     * @param $report
     * @param $result
     * @param bool $attached
     */
    protected function processPgpKey(KeyCheckReport $report, $result, $attached=false){
        // Convert to the same format as pgp-key.
        if (!$attached){
            if (!isset($result->results) || empty($result->results)){
                Log::info('Empty sub-results');
                return;
            }

            $tests = [];
            foreach ($result->results as $subRes){
                if (isset($subRes->tests)){
                    $tests += $subRes->tests;
                } elseif (isset($subRes->error)){
                    $res = new PGPKeyResult();
                    $res->status = 'error';
                    if (isset($subRes->key_id_resolve_error)){
                        $res->verdict = 'Cannot find the key';
                    } elseif (isset($subRes->no_keys)){
                        $res->verdict = 'No keys found';
                    } elseif ($subRes->error == 'key-fetch-error'){
                        $res->verdict = 'Key download error';
                    } elseif ($subRes->error == 'key-process-error'){
                        $res->verdict = 'Key processing error';
                    }
                }
            }
            $result->tests = $tests;
        }

        foreach ($result->tests as $test) {
            $res = new PGPKeyResult();
            try {
                $res->keyId = $test->kid;
                $res->status = 'error';

                if (isset($test->limit_reached)){
                    $res->verdict = 'Limit reached';

                } elseif ($test->n) {
                    $res->masterKeyId = $test->master_kid;
                    $res->modulus = $test->n;
                    $res->bitSize = $this->getBitWidth($test->n);
                    $res->marked = !!$test->marked;
                    $res->createdAt = Carbon::parse($test->created_at);

                    $fidentity = isset($test->identities) && !empty($test->identities) ? $test->identities[0] : null;
                    $res->identity = empty($fidentity) ? '' : [$fidentity->name, $fidentity->email];

                    $res->status = 'ok';
                    $res->verdict = $res->marked ? 'Vulnerable' : 'Safe';
                    $report->allSafe &= !$res->marked;

                } else {
                    $res->status = 'fetch-failed';
                    $res->verdict = 'Key download error';
                }

                $report->pgpKeys[] = $res;

            } catch(\Exception $e){
                Log::info('Exception in PGP result processing: ' . $e);
                if ($res->keyId){
                    $res->status = 'error';
                    $report->pgpKeys[] = $res;
                }
            }
        }
    }

    /**
     * Simple bitwidth computation from modulus hex string.
     * @param $modString
     * @return int
     */
    protected function getBitWidth($modString){
        if (empty($modString)){
            return null;
        }

        if (Str::startsWith($modString, ['0x'])){
            $modString = substr($modString, 2);
        }

        return strlen($modString) * 4;
    }

    /**
     * Actually sends the email, either synchronously or enqueue for sending.
     * @param $user
     * @param Mailable $mailable
     * @param bool $enqueue
     */
    protected function sendMail($user, Mailable $mailable, $enqueue=false){
        $s = Mail::to($user);
        if ($enqueue){
            $s->queue($mailable
                ->onConnection(config('keychest.wrk_weekly_emails_conn'))
                ->onQueue(config('keychest.wrk_weekly_emails_queue')));
        } else {
            $s->send($mailable);
        }
    }
}
