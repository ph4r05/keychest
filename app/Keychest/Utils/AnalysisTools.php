<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 17.08.17
 * Time: 13:43
 */

namespace App\Keychest\Utils;
use Carbon\Carbon;
use Illuminate\Support\Collection;


/**
 * Analysis tools, processing loading collections, filtering, ...
 *
 * Class DataTools
 * @package App\Keychest\Utils
 */
class AnalysisTools {
    public static function getTlsCerts(Collection $certs){
        return $certs ? $certs
            ->filter(function ($value, $key) {
                return $value->found_tls_scan;
            })
            ->sortBy('valid_to') : collect();
    }

    /**
     * TLS expired certs
     * @param Collection $certs
     * @return Collection
     */
    public static function getCertExpired(Collection $certs)
    {
        return $certs ? $certs->filter(function ($value, $key) {
            return Carbon::now()->greaterThanOrEqualTo($value->valid_to);
        }) : collect();
    }

    /**
     * TLS expire in 7 days
     * @param Collection $certs
     * @return Collection
     */
    public static function getCertExpire7days(Collection $certs)
    {
        return $certs ? $certs->filter(function ($value, $key) {
            return Carbon::now()->lessThanOrEqualTo($value->valid_to)
                && Carbon::now()->addDays(7)->greaterThanOrEqualTo($value->valid_to);
        }) : collect();
    }

    /**
     * TLS expire in 28 days
     * @param Collection $certs
     * @return Collection
     */
    public static function getCertExpire28days(Collection $certs)
    {
        return $certs ? $certs->filter(function ($value, $key) {
            return Carbon::now()->addDays(7)->lessThanOrEqualTo($value->valid_to)
                && Carbon::now()->addDays(28)->greaterThanOrEqualTo($value->valid_to);
        }) : collect();
    }
}
