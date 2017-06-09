<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\Models\WatchTarget;
use App\User;
use Illuminate\Contracts\Auth\Factory as FactoryContract;
use Illuminate\Support\Facades\Auth;

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
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Checks if the host can be added to the certificate monitor
     * @param $server
     * @param User|null $curUser
     * @return int
     */
    public function canAddHost($server, $curUser=null){
        $parsed = parse_url($server);
        if (empty($parsed) || strpos($server, '.') === false){
            return -1;
        }

        $criteria = $this->buildCriteria($parsed, $server);
        $userId = empty($curUser) ? null : $curUser->getAuthIdentifier();

        // Duplicity detection
        if ($this->getHostsBy($criteria, $userId)->isNotEmpty()){
            return 0;
        }

        return 1;
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
