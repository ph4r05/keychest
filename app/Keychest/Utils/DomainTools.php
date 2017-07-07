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
            if (strpos($comp, '*') !== false || strpos($comp, '%') !== false){
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

    /**
     * Removes path part from the url
     * @param $url
     * @return string
     */
    public static function removeUrlPath($url){
        if (empty($url)){
            return $url;
        }

        $parts = explode('://', $url, 2);
        $host = count($parts) <= 1 ? $url : $parts[1];
        $pos = strpos($host, '/');
        return $pos === false ? $host : substr($host, 0, $pos);
    }

    /**
     * Normalizes URL (adds default https scheme and default port 443 if not present).
     * Strips path, query, fragment away.
     * @param $url
     * @return string
     */
    public static function normalizeUrl($url){
        $parsed = parse_url($url);
        $scheme = isset($parsed['scheme']) && !empty($parsed['scheme']) ? $parsed['scheme'] : 'https';
        $host = isset($parsed['host']) && !empty($parsed['host']) ? $parsed['host'] : self::removeUrlPath($url);
        $port = isset($parsed['port']) && !empty($parsed['port']) ? intval($parsed['port']) : 443;
        return self::assembleUrl($scheme, $host, $port);
    }

    /**
     * If http:// is present it is replaced with https://
     * @param string $url
     * @return mixed
     */
    public static function replaceHttp($url){
        return str_replace('http://', 'https://', $url);
    }

    /**
     * Strips wildcard domain
     * @param $url
     * @return mixed
     */
    public static function stripWildcard($url){
        $url = str_replace('*.', '', $url);
        $url = str_replace('%.', '', $url);
        $url = str_replace('*', '', $url);
        $url = str_replace('%', '', $url);
        return $url;
    }
}
