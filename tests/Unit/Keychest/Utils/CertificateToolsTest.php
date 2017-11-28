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

    public function testNormalizer(){
        $data = [
            ['a' => 'thawte', 'b'=>0],
            ['a' => 'thawte', 'b'=>1],
            ['a' => 'thawte ', 'b'=>2],
            ['a' => 'thawte-', 'b'=>3],
            ['a' => 'thawte inc', 'b'=>4],
            ['a' => 'thawte Inc', 'b'=>5],
            ['a' => 'thawte, inc', 'b'=>6],
            ['a' => 'thawte, inc.', 'b'=>7],
            ['a' => 'terena', 'b'=>8],
            ['a' => 'Terena', 'b'=>9],
            ['a' => 'TERENA', 'b'=>10],
            ['a' => 'terena,', 'b'=>11],
            ['a' => 'LetsEncrypt', 'b'=>12],
            ['a' => 'Let\'sEncrypt', 'b'=>13],
        ];

        $res = CertificateTools::normalizeIssuerOrgs($data, 'a');
        $this->assertEquals('Thawte', $res[0]['a_norm']);
        $this->assertEquals('Thawte', $res[1]['a_norm']);
        $this->assertEquals('Thawte', $res[2]['a_norm']);
        $this->assertEquals('Thawte', $res[3]['a_norm']);
        $this->assertEquals('Thawte, Inc.', $res[4]['a_norm']);
        $this->assertEquals('Thawte, Inc.', $res[5]['a_norm']);
        $this->assertEquals('Thawte, Inc.', $res[6]['a_norm']);
        $this->assertEquals('Thawte, Inc.', $res[7]['a_norm']);
        $this->assertEquals('Terena', $res[8]['a_norm']);
        $this->assertEquals('Terena', $res[9]['a_norm']);
        $this->assertEquals('Terena', $res[10]['a_norm']);
        $this->assertEquals('Terena', $res[11]['a_norm']);
    }

}
