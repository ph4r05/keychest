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

    public function testToObject(){
        $data = [
            [
                'name' => 'John Doe',
                'emails' => [
                    'john@doe.com',
                    'john.doe@example.com',
                ],
                'contacts' => [
                    [
                        'name' => 'Naomi',
                        'emails' => [
                            'naomi@example.com',
                        ],
                    ],
                    [
                        'name' => 'Morgan1',
                        'emails' => [
                            'morgan@test.com',
                        ],
                    ],
                ],
            ],
        ];

        $dat = DataTools::asObject(collect($data));
        $this->assertEquals('John Doe', $dat[0]->name);

        $dat = collect($data)->recursiveObj();
        $this->assertEquals('John Doe', $dat[0]->name);
        $this->assertEquals('john.doe@example.com', $dat[0]->emails[1]);
        $this->assertEquals('Morgan1', $dat[0]->contacts[1]->name);
        $this->assertEquals('morgan@test.com', $dat[0]->contacts[1]->emails[0]);
    }
}
