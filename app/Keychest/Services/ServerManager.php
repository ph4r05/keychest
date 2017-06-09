<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\Models\WatchTarget;
use Illuminate\Contracts\Auth\Factory as FactoryContract;

class ServerManager {

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Create a new Auth manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Used to load hosts for update / add to detect duplicates
     * @param $criteria
     * @param $userId
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getHostsBy($criteria, $userId){
        $query = WatchTarget::query();

        if (!empty($userId)){
            $query = $query->where('user_id', $userId);
        }

        foreach ($criteria as $key => $val){
            $query = $query->where($key, $val);
        }

        return $query->get();
    }

    /**
     * Builds simple criteria from parsed domain
     * @param $parsed
     * @param $server
     * @return array
     */
    public function buildCriteria($parsed, $server){
        return [
            'scan_scheme' => isset($parsed['scheme']) && !empty($parsed['scheme']) ? $parsed['scheme'] : 'https',
            'scan_host' => isset($parsed['host']) && !empty($parsed['host']) ? $parsed['host'] : $server,
            'scan_port' => isset($parsed['port']) && !empty($parsed['port']) ? $parsed['port'] : 443,
        ];
    }

}
