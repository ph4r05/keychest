<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 28.09.17
 * Time: 14:02
 */

namespace App\Keychest\Utils;


use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ParamTools
{
    /**
     * Returns the collection if not already
     * @param $options
     * @return Collection
     */
    public static function col($options){
        if ($options instanceof Collection){
            return $options;
        }

        return collect();
    }



}