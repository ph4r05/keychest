<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\User;
use Exception;
use Illuminate\Contracts\Auth\Factory as FactoryContract;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IpScanManager {

    /**
     * The application instance.
     *
     * @var Application
     */
    protected $app;

    /**
     * Create a new Auth manager instance.
     *
     * @param  Application  $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }


}
