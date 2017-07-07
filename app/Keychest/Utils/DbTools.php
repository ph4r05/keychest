<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 17:03
 */

namespace App\Keychest\Utils;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Traversable;

/**
 * Minor static collection of DB utilities.
 *
 * Class DbTools
 * @package App\Keychest\Utils
 */
class DbTools {
    /**
     * Applies order by parsed from Vue string to the query builder
     * @param $query
     * @param Collection $sortDef
     * @return mixed
     */
    public static function sortQuery($query, $sortDef){
        if (empty($sortDef) || $sortDef->isEmpty()){
            return $query;
        }

        foreach ($sortDef->values()->all() as $cur){
            $query = $query->orderBy($cur[0], $cur[1] ? 'asc' : 'desc');
        }

        return $query;
    }
}

