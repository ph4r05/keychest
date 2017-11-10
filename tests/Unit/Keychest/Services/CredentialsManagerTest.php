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
        $this->assertTrue(true);
        $cmag = $this->app->make(CredentialsManager::class);

        $genKey = $cmag->generateSshKey(1024);
        $this->assertNotNull($genKey);

        $this->assertNotNull($genKey->getPrivateKey());
        $this->assertNotNull($genKey->getPublicKey());
        $this->assertStringStartsWith('ssh-rsa ', $genKey->getPublicKey());
        $this->assertContains('BEGIN RSA PRIVATE KEY', $genKey->getPrivateKey());
    }
}
