<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 17:03
 */

namespace App\Keychest\Utils;

/**
 * Minor static domain / string utilities.
 *
 * Class DomainTools
 * @package App\Keychest\Utils
 */
class DomainTools {

    /**
     * Returns wildcard domain for the given one
     * @param $domain
     * @return string
     */
    public static function wildcardDomain($domain){
        if (strpos($domain, '*.') === 0){
            return $domain;
        }

        $fqdn = self::fqdn($domain);
        if (empty($fqdn)){
            $fqdn = $domain;
        }

        return '*.' . $fqdn;
    }

    /**
     * Returns FQDN, strips wildcards
     * @param $domain
     * @return string
     */
    public static function fqdn($domain){
        $components = explode('.', $domain);
        $ret = [];

        foreach(array_reverse($components) as $comp){
            if (strpos($comp, '.') !== false || strpos($comp, '%') !== false){
                break;
            }

            $ret[] = $comp;
        }

        if (count($ret) < 2){
            return null;
        }

        return join('.', array_reverse($ret));
    }

    /**
     * Alt names matching the given domain search.
     * test.alpha.dev.domain.com ->
     *   - *.alpha.dev.domain.com
     *   - *.dev.domain.com
     *   - *.domain.com
     * @param $domain
     * @return array
     */
    public static function altNames($domain)
    {
        $components = explode('.', $domain);

        // If % wildcard is present, skip.
        foreach ($components as $comp){
            if (strpos($comp, '%') !== false){
                return [];
            }
        }

        $result = [];
        $ln = count($components);
        for($i = 1; $i < $ln - 1; $i++){
            $result[] = '*.' . join('.', array_slice($components, $i));
        }

        return $result;
    }

    /**
     * Assembless full url from parts
     * @param $scheme
     * @param $host
     * @param $port
     * @return string
     */
    public static function assembleUrl($scheme, $host, $port){
        if (empty($scheme)){
            $scheme = 'https';
        }
        if (empty($port)){
            $port = 443;
        }
        return $scheme . '://' . $host . ':' . $port;
    }
}
