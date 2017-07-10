<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 17:03
 */

namespace App\Keychest\Utils;

use TrueBV\Punycode;

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

        $punny = new Punycode();
        $host = $punny->encode($host);
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

    /**
     * Returns true if domain is wildcard
     * @param $url
     * @return bool
     */
    public static function isWildcard($url){
        return strpos($url, '*.') === 0 || strpos($url, '%.') === 0;
    }

    /**
     * Tries to normalize hostname given as user input
     * @param $inp
     * @param bool $forceScheme
     * @return string
     */
    public static function normalizeUserDomainInput($inp, $forceScheme=true){
        $inp = trim($inp);
        $inp = DomainTools::replaceHttp($inp);
        $inp = DomainTools::stripWildcard($inp);
        if (strpos($inp, '://') === false){
            $inp = 'https://' . $inp;
        }

        $parsed = parse_url($inp);
        $scheme = isset($parsed['scheme']) && !empty($parsed['scheme']) ? $parsed['scheme'] : 'https';
        if ($forceScheme && !in_array($scheme, ['https'])){
            $scheme = 'https';
        }

        $host = isset($parsed['host']) && !empty($parsed['host']) ? $parsed['host'] : self::removeUrlPath($inp);
        $port = isset($parsed['port']) && !empty($parsed['port']) ? intval($parsed['port']) : 443;

        $punny = new Punycode();
        $host = $punny->encode($host);
        return self::assembleUrl($scheme, $host, $port);
    }

    /**
     * Returns true if the domain name is well formed
     * @param $domain_name
     * @return bool
     */
    public static function isValidDomainName($domain_name)
    {
        return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
            && preg_match("/^.{1,253}$/", $domain_name) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
    }

    /**
     * Test IPv6 for validity
     * @param $inp
     * @return bool
     */
    public static function isIpv6Valid($inp){
        return filter_var($inp, FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV6 | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * Tests IPv4 for validity
     */
    public static function isIpv4Valid($inp){
        return filter_var($inp, FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * Tests IP for validity
     * @param $inp
     * @return bool
     */
    public static function isIpValid($inp){
        return filter_var($inp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * Tests parsed hostname for validity
     * @param $parsed
     * @return bool
     */
    public static function isValidParsedUrlHostname($parsed){
        if (empty($parsed) || !isset($parsed['host']) || empty($parsed['host'])){
            return false;
        }

        $host = $parsed['host'];

        $matches = null;
        if (preg_match('/^\\[(.+)\\]$/', $host, $matches)){
            if (self::isIpv6Valid($matches[1])){
                return true;
            }
        }

        if (self::isIpv4Valid($host)){
            return true;
        }

        if (strpos($host, '.') === false){
            return false;
        }

        if (self::isValidDomainName($host)){
            return true;
        }

        return false;
    }
}
