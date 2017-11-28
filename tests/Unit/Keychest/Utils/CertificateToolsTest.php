<?php

namespace Tests\Unit\Keychest\Services;

use App\Keychest\Services\CredentialsManager;
use App\Keychest\Utils\CertificateTools;
use Tests\CreatesApplication;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CertificateToolsTest extends TestCase
{
    /**
     * normalizeIssuerOrg()
     *
     * @return void
     */
    public function testNormalizeIssuerOrg()
    {
        $this->assertEquals('Test, Inc.', CertificateTools::normalizeIssuerOrg('test inc'));
        $this->assertEquals('Test, Inc.', CertificateTools::normalizeIssuerOrg('test Inc'));
        $this->assertEquals('Test, Inc.', CertificateTools::normalizeIssuerOrg('test Inc.'));
    }

}
