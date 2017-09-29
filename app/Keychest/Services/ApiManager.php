<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.08.17
 * Time: 18:16
 */

namespace App\Keychest\Services;

use App\Keychest\DataClasses\ValidityDataModel;
use App\Keychest\Services\Exceptions\CertificateAlreadyInsertedException;
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DomainTools;
use App\Models\ApiKey;
use App\Models\ApiWaitingObject;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Webpatser\Uuid\Uuid;


class ApiManager
{

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Create a new Auth manager instance.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Adds certificate to the watching.
     *
     * @param User $user
     * @param ApiKey $apiKey
     * @param $certificate
     * @return ApiWaitingObject
     * @throws CertificateAlreadyInsertedException
     */
    public function addCertificateToWatch(User $user, ApiKey $apiKey, $certificate)
    {
        // hash of the certificate is the object key here, with little change
        $key = md5('CERT:' . $certificate);

        // duplicate check
        $q = ApiWaitingObject::query()
            ->where('api_key_id', '=', $apiKey->id)
            ->where('object_key', '=', $key)
            ->whereNull('finished_at')
            ->where('approval_status', '=', 0);
        $dups = $q->get();

        if ($dups->isNotEmpty()){
            throw new CertificateAlreadyInsertedException('Duplicate found');
        }

        // Insert new waiting object.
        $obj = new ApiWaitingObject([
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'api_key_id' => $apiKey->id,
            'waiting_id' => Uuid::generate()->string,
            'object_operation' => 'add',
            'object_type' => 'cert',
            'object_key' => $key,
            'object_value' => $certificate,
            'approval_status' => 0
        ]);

        $obj->save();
        return $obj;
    }



}
