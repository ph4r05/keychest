<?php

namespace Tests\Unit\Keychest\Services;

use App\Keychest\Services\CredentialsManager;
use App\Keychest\Utils\CertificateTools;
use App\Keychest\Utils\DataTools;
use Tests\CreatesApplication;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DataToolsTest extends TestCase
{
    /**
     * capitalizeFirstWord()
     *
     * @return void
     */
    public function testCapitalizeFirstWord()
    {
        $this->assertEquals('Terena', DataTools::capitalizeFirstWord('terena'));
        $this->assertEquals('Terena', DataTools::capitalizeFirstWord('TERENA'));
        $this->assertEquals('cloudFlare', DataTools::capitalizeFirstWord('cloudFlare'));
        $this->assertEquals('LetsEncrypt', DataTools::capitalizeFirstWord('LetsEncrypt'));
    }

}
