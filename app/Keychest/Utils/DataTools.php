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
     * Inverts collection mapping / inverts multi-map
     * watch id -> [certs] mapping turns into cert -> [watches]
     *
     *  k1 => [v1, v2]
     *  k2 => [v1, v3, v4]
     * to
     *  v1 => [k1, k2]
     *  v2 => [k1]
     *  v3 => [k2]
     *  v4 => [k2]
     *
     * @param Collection $map
     * @param bool $asCol
     * @return Collection
     */
    public static function invertMap($map, $asCol=false){
        $inverted = collect();
        $map->map(function($item, $key) use ($inverted, $asCol){
            $nitem = $item;
            if (!is_array($nitem) && !($item instanceof Traversable)){
                $nitem = [$nitem];
            }

            foreach($nitem as $cur){
                $submap = $inverted->get($cur, $asCol ? collect() : array());
                if ($asCol || $submap instanceof Collection){
                    $submap->push($key);
                } else {
                    $submap[] = $key;
                }
                $inverted->put($cur, $submap);
            }
        });
        return $inverted;
    }

    /**
     * Appends to an list within the collection.
     * @param array|Collection $col
     * @param mixed $key
     * @param mixed $val
     * @param bool $asCol
     * @return array|Collection
     */
    public static function appendTo($col, $key, $val, $asCol=true){
        if ($col instanceof Collection){
            if ($col->has($key)){
                $x = $col->get($key);
                if ($asCol){
                    $x->push($val);
                } else {
                    $x[] = $val;
                }
            } else {
                $col->put($key, $asCol ? collect([$val]) : [$val]);
            }

        } else {  // array
            if (array_key_exists($key, $col)){
                if ($asCol){
                    $col[$key]->push($val);
                } else {
                    $col[$key][] = $val;
                }
            } else {
                $col[$key] = $asCol ? collect([$val]) : [$val];
            }
        }

        return $col;
    }

    /**
     * Appends a value to the collection.
     * @param $col
     * @param $val
     * @return Collection
     */
    public static function appendToCol($col, $val){
        if (empty($col)){
            return collect([$val]);
        }

        $col->push($val);
        return $col;
    }

    /**
     * Transforms [key=>collect([val1, val2])] to [key=>[val1, val2]]
     * @param Collection $col
     * @return mixed
     */
    public static function multiListToArray($col){
        if (empty($col)){
            return $col;
        }

        return $col->map(function ($item, $key) {
            return ($item instanceof Collection) ? $item->all() : [];
        });
    }

    /**
     * Transforms [key=>[val1, val2]] to [key=>collect([val1, val2])]
     * @param $col
     * @return mixed
     */
    public static function muliListToCollection($col){
        if (empty($col)){
            return $col;
        }

        return $col->map(function ($item, $key) {
            return collect($item);
        });
    }

    /**
     * Creates a multimap from the collection by the $callback.
     * Function has to return [$key => $val].
     *
     * @param Collection $col
     * @param \Closure $callback
     * @param bool $mergeResult if true returned value is merged if multiple values are returned (arr / collection)
     * @return Collection
     */
    public static function multiMap($col, $callback, $mergeResult=false){
        $result = collect();

        $col->map(function($item, $key) use ($result, $callback, $mergeResult) {
            $pair = $callback($item, $key);
            foreach($pair as $nkey => $nval) {
                if ($mergeResult && (is_array($nval) || ($nval instanceof Traversable))){
                    foreach($nval as $nnval) {
                        self::appendTo($result, $nkey, $nnval, true);
                    }
                } else {
                    self::appendTo($result, $nkey, $nval, true);
                }
            }
        });

        return $result;
    }

    /**
     * In-place union to dst
     * @param Collection $dst
     * @param Collection $src
     * @return Collection
     */
    public static function addAll($dst, $src){
        $src->each(function ($item, $key) use ($dst) {
            $dst->push($item);
        });
        return $dst;
    }

    /**
     * Returns subset of the collection / map by the given keys.
     * Could be implemented as a filter on the key. Syntactic sugar.
     * @param $col
     * @param $ids
     * @return Collection
     */
    public static function pick($col, $ids){
        $idsArr = is_array($ids) || $ids instanceof Traversable;
        if (!$idsArr){
            $ids = [$ids];
        }

        $idSet = array_fill_keys(($ids instanceof Collection) ? $ids->values()->all() : $ids, true);
        $ret = [];

        foreach($col as $key => $val){
            if (array_key_exists($key, $idSet)){
                $ret[$key] = $val;
            }
        }

        return collect($ret);
    }

    /**
     * Processes sort string produced by Vue table, returns parsed result for query.
     * @param $sort
     * @return Collection
     */
    public static function vueSortToDb($sort){
        if (empty(trim($sort))){
            return collect();
        }

        $sorts = collect(explode(',', $sort));
        return $sorts->map(function($item, $key){
            $asc = true;
            if (strpos($item, '|') !== false){
                list($item, $ordtxt) = explode('|', $item, 2);
                $asc = $ordtxt == 'asc';
            }
            return [$item, $asc];
        })->values();
    }
}
