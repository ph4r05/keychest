<?php

namespace Tests\Feature\Keychest\Services;

use App\Keychest\Services\CredentialsManager;
use App\Keychest\Services\Exceptions\SshKeyAllocationException;
use App\Models\SshKey;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\CreatesApplication;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CredentialsManagerTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSshKeyStorage()
    {
        $bitSize = 1024;
        $credentialsManager = $this->app->make(CredentialsManager::class);
        $sshKey = $credentialsManager->generateSshKey($bitSize);
        $sshKeyDb = $credentialsManager->generateSshPoolKey($sshKey);

        $this->assertEquals($sshKey->getPublicKey(), $sshKeyDb->pub_key);
        $this->assertEquals($sshKey->getPrivateKey(), $sshKeyDb->priv_key);
        $this->assertEquals($bitSize, $sshKeyDb->bit_size);
        $this->assertEquals(0, $sshKeyDb->rec_version);
        $this->assertNotNull($sshKeyDb->key_id);

        $loadedElo = SshKey::query()
            ->where('key_id', '=', $sshKeyDb->key_id)
            ->get()->first();

        $this->assertNotNull($loadedElo);
        $this->assertEquals($sshKey->getPrivateKey(), $loadedElo->priv_key);

        // Test if DB encryption works
        $this->assertNotNull($loadedElo->getAttributes()['priv_key']);
        $this->assertStringStartsWith('{', $loadedElo->getAttributes()['priv_key']);
        $this->assertContains('"scheme"', $loadedElo->getAttributes()['priv_key']);
        $this->assertNotContains($sshKey->getPrivateKey(), $loadedElo->getAttributes()['priv_key']);

        $loaded = DB::table(SshKey::TABLE)
            ->selectRaw('pub_key, priv_key, priv_key AS privk')
            ->where('key_id', '=', $sshKeyDb->key_id)
            ->get()->first();

        $this->assertNotNull($loaded);
        $this->assertNotEmpty($loaded->priv_key);
        $this->assertNotEmpty($loaded->privk);
        $this->assertNotEquals($sshKey->getPrivateKey(), $loaded->privk);
        $this->assertEquals($loadedElo->getAttributes()['priv_key'], $loaded->privk);
    }

    /**
     * Tests ssh key allocation from the pool.
     */
    public function testSshKeyAllocationEmpty(){
        $credentialsManager = $this->app->make(CredentialsManager::class);
        $user = (new User(['name'=>'tester', 'email'=>'test@keychest.net']));
        $user->save();

        $this->expectException(SshKeyAllocationException::class);
        $credentialsManager->allocateSshKeyToUser(1024, $user);
    }

    /**
     * Test valid allocation.
     */
    public function testSshKeyAllocation(){
        $credentialsManager = $this->app->make(CredentialsManager::class);
        $user = (new User(['name'=>'tester', 'email'=>'test@keychest.net']));
        $user->save();
        $bitSize = 512;

        for($i=0; $i<5; $i++) {
            $key = $credentialsManager->generateSshPoolKey($bitSize);
            $this->assertNotNull($key);
        }

        for($i=0; $i<5; $i++) {
            $key = $credentialsManager->allocateSshKeyToUser($bitSize, $user);
            $this->assertNotNull($key);
            $this->assertEquals($user->id, $key->user_id);
            $this->assertEquals($bitSize, $key->bit_size);
            $this->assertStringStartsWith('-----BEGIN', $key->priv_key);
        }

        // 6th - exception.
        $this->expectException(SshKeyAllocationException::class);
        $credentialsManager->allocateSshKeyToUser($bitSize, $user);
    }

}
