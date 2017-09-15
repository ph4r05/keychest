<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\User;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LicenseManager {

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Create a new manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }


}
