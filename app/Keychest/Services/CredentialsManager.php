<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.08.17
 * Time: 18:16
 */

namespace App\Keychest\Services;

use App\Keychest\DataClasses\GeneratedSshKey;
use App\Keychest\DataClasses\ValidityDataModel;
use App\Keychest\Services\Exceptions\CertificateAlreadyInsertedException;
use App\Keychest\Services\Exceptions\SshKeyAllocationException;
use App\Keychest\Utils\ApiKeyLogger;
use App\Keychest\Utils\DataTools;
use App\Keychest\Utils\DomainTools;
use App\Models\ApiKey;
use App\Models\ApiWaitingObject;
use App\Models\SshKey;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use phpseclib\Crypt\RSA;
use Webpatser\Uuid\Uuid;


class CredentialsManager
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
     * SSH key generator, in the current thread, returns the result.
     * DB is not touched.
     *
     * @param int $bits
     * @return GeneratedSshKey
     */
    public function generateSshKey($bits=3072){
        $rsa = new RSA();
        $rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_OPENSSH);
        $rsa->setPrivateKeyFormat(RSA::PRIVATE_FORMAT_PKCS1);
        $rsa->setComment('KeyChest-management-key');
        $key = $rsa->createKey($bits);
        return new GeneratedSshKey($key['privatekey'], $key['publickey'], $bits);
    }

    /**
     * Generates SSH key with the given pool size and stores it to the SSH Key pool.
     * @param int|GeneratedSshKey $keySpec either bit size or the generated key directly.
     * @return SshKey
     */
    public function generateSshPoolKey($keySpec){
        $sshKey = $keySpec instanceof GeneratedSshKey ? $keySpec : $this->generateSshKey($keySpec);

        $now = Carbon::now();
        $dbKey = new SshKey([
            'key_id' => Uuid::generate()->string,
            'pub_key' => $sshKey->getPublicKey(),
            'priv_key' => $sshKey->getPrivateKey(),
            'bit_size' => $sshKey->getBitSize(),
            'key_type' => 0,
            'storage_type' => 0,
            'owner_id' => null,
            'rec_version' => 0,
            'created_at' => $now,
            'updated_at' => $now
        ]);

        $dbKey->save();
        return $dbKey;
    }

    /**
     * Expires / deletes all unused old SSH pool keys from the database.
     * Performed in transaction so pesimistic locking can be used for key allocation logic.
     * @return number of deleted rows
     */
    public function expireOldSshKeys(){
        $maxFreeKeyAgeDays = intval(config('keychest.ssh_key_free_max_age_days'));
        if ($maxFreeKeyAgeDays < 0){
            return 0;
        }

        $threshold = Carbon::now()->subDays($maxFreeKeyAgeDays);
        return DB::transaction(function() use ($threshold) {
            $deletedRows = SshKey
                ::whereNull('owner_id')
                ->where('rec_version', '=', 0)
                ->where('created_at', '<', $threshold)
                ->delete();
            return $deletedRows;

        }, 5);
    }

    /**
     * Checks the state of the SSH key pool, returns array
     * bitsize => keys to generate.
     */
    public function getSshKeyPoolDeficit(){
        $maxFreeKeyAgeDays = intval(config('keychest.ssh_key_free_max_age_days'));
        $threshold = $maxFreeKeyAgeDays > 0 ? Carbon::now()->subDays($maxFreeKeyAgeDays) : null;

        $q = SshKey::query()
            ->whereNull('owner_id')
            ->where('rec_version', '=', 0);

        if (!empty($threshold)){
            $q = $q->where('created_at', '>=', $threshold);
        }

        $keys = $q
            ->selectRaw('COUNT(*) AS key_count, bit_size')
            ->groupBy('bit_size')
            ->orderBy('bit_size')
            ->get();

        $requiredLengths = config('keychest.ssh_key_sizes');
        $requiredNumbers = intval(config('keychest.ssh_key_pool_size'));
        $toGenerate = array_combine(
            $requiredLengths,
            array_fill(0, count($requiredLengths), $requiredNumbers));

        foreach($keys->all() as $key){
            $toGenerate[$key->bit_size] -= $key->key_count;
        }

        return $toGenerate;
    }

    /**
     * Allocates one SSH key to the user from the pregenerated SSH key pool.
     *
     * @param $bitSize
     * @param $user
     * @return mixed
     */
    public function allocateSshKeyToUser($bitSize, $user){
        $maxFreeKeyAgeDays = intval(config('keychest.ssh_key_free_max_age_days'));
        $threshold = $maxFreeKeyAgeDays > 0 ? Carbon::now()->subDays($maxFreeKeyAgeDays) : null;

        // Pessimistic locking.
        // Optimistic locking variant would be:
        //  - until success / attempt reaches the maximum
        //  - - query random free ID from the database
        //  - - UPDATE ssh_keys
        //              SET rec_version = rec_version + 1, owner_id = $owner_id
        //              WHERE rec_version == $rec_version AND owner_id is NULL and ID = $id // make sure still free
        //
        //  - - check number of affected rows by the query. If 1 then success.
        //  - - load final key once again, last check (redundant, just get fresh eloquent model), return.
        return DB::transaction(function() use ($bitSize, $user, $threshold) {
            $candidate = SshKey::query()
                ->whereNull('owner_id')
                ->where('rec_version', '=', 0)
                ->where('created_at', '>=', $threshold)
                ->where('bit_size', '=', $bitSize)
                ->inRandomOrder()
                ->lockForUpdate()
                ->first();

            if (empty($candidate)){
                throw new SshKeyAllocationException('Could not find any suitable candidate key');
            }

            $candidate->owner_id = $user->primary_owner_id;
            $candidate->rec_version += 1;
            $candidate->save();
            return $candidate;

        }, 5);
    }
}
