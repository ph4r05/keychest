<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 17:03
 */

namespace App\Keychest\Utils;
use Illuminate\Support\Collection;
use Traversable;

/**
 * Minor static collection utilities.
 *
 * Class DataTools
 * @package App\Keychest\Utils
 */
class DataTools {

    /**
     * Inverts collection mapping.
     * watch id -> [certs] mapping turns into cert -> [watches]
     * @param Collection $map
     * @return Collection
     */
    public static function invertMap($map){
        $inverted = collect();
        $map->map(function($item, $key) use ($inverted){
            $nitem = $item;
            if (!is_array($nitem) && !($item instanceof Traversable)){
                $nitem = [$nitem];
            }

            foreach($nitem as $cur){
                $submap = $inverted->get($cur, array());
                $submap[] = $key;
                $inverted->put($cur, $submap);
            }
        });
        return $inverted;
    }
}
