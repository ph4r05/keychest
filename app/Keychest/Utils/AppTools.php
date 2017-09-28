<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 28.09.17
 * Time: 14:02
 */

namespace App\Keychest\Utils;


use Illuminate\Foundation\Application;
use Illuminate\Support\Str;

class AppTools
{
    /**
     * Returns configuration key
     * @param \Illuminate\Foundation\Application $app
     * @return bool|string
     */
    public static function getKey(Application $app){
        $key = $app['config']['app.key'];
        if (Str::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        return $key;
    }


}