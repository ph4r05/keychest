<?php

namespace Tests\Unit\Keychest\Services;

use App\Keychest\Services\CredentialsManager;
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
    public function testRsaKeyGenerator()
    {
        $credentialsManager = $this->app->make(CredentialsManager::class);
        $bitSizes = [512, 1024, 1024, 2048];

        foreach($bitSizes as $bitSize) {
            $genKey = $credentialsManager->generateSshKey($bitSize);
            $this->assertNotNull($genKey);

            $this->assertNotNull($genKey->getPrivateKey());
            $this->assertNotNull($genKey->getPublicKey());
            $this->assertStringStartsWith('ssh-rsa ', $genKey->getPublicKey());
            $this->assertContains('BEGIN RSA PRIVATE KEY', $genKey->getPrivateKey());
            $this->assertGreaterThan(100, strlen($genKey->getPublicKey()));
            $this->assertGreaterThan(100, strlen($genKey->getPrivateKey()));
        }
    }

}
