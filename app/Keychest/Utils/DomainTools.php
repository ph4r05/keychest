<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 17:03
 */

namespace App\Keychest\Utils;


use App\Keychest\Utils\Exceptions\HostSpecParseException;
use Illuminate\Support\Collection;


use IPLib\Address\AddressInterface;
use IPLib\Factory;

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
     * Similar to assembleUrl but port is optional. Equal fnc than in the frontend Req.buildUrl().
     * @param $scheme
     * @param $host
     * @param $port
     * @return string
     */
    public static function buildUrl($scheme, $host, $port){
        if (empty($scheme)){
            $scheme = 'https';
        }

        $ret = $scheme . '://' . $host;
        return !$port ? $ret : $ret . ':' . $port;
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
     * @param $inp
     * @return bool
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
     * Returns true if port is in valid range.
     * @param $p
     * @return bool
     */
    public static function isValidPort($p){
        $p = intval($p);
        return $p > 0 && $p <= 65535;
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

    /**
     * Sorts the collection by hierarchical domain
     * @param array|Collection $col
     * @param $callback
     * @return \Illuminate\Support\Collection
     */
    public static function sortByDomains($col, $callback){
        // global ordering, id -> ranking, sort by the ranking
        $results = [];
        $callback = DataTools::valueRetriever($callback);

        // First we will loop through the items and get the comparator from a callback
        // function which we were given. Then, we will sort the returned values and
        // and grab the corresponding values for the sorted keys from this array.
        foreach ($col as $key => $value) {
            $results[$key] = self::computeDomainSortKey($callback($value, $key));
        }

        uasort($results, '\App\Keychest\Utils\DataTools::compareArrays');

        // Once we have sorted all of the keys in the array, we will loop through them
        // and grab the corresponding model so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $col->get($key);
        }

        return collect($results);
    }

    /**
     * Simple domain sort key for optimized domain sort.
     * @param $domain
     * @return array
     */
    public static function computeDomainSortKey($domain){
        return array_reverse(explode('.', $domain));
    }

    /**
     * Comparator on domains - hierarchical
     * @param $a
     * @param $b
     * @return int
     */
    public static function compareDomainsHierarchical($a, $b){
        $aPart = array_reverse(explode('.', $a));
        $bPart = array_reverse(explode('.', $b));
        return DataTools::compareArrays($aPart, $bPart);
    }

    /**
     * Converts IP to its object form if not already
     * @param $ip
     * @return AddressInterface|null
     */
    public static function ipEnsureObject($ip){
        return $ip instanceof AddressInterface ? $ip : Factory::addressFromString($ip, false);
    }

    /**
     * Transforms IP to the octet array
     * @param $ip
     * @return array|null
     */
    public static function ipToOctets($ip){
        if (empty($ip)){
            return null;
        }

        return self::ipEnsureObject($ip)->getBytes();
    }

    /**
     * Tranforms IP address to 32bit int
     * @param $ip
     * @return int
     */
    public static function ipv4ToIdx($ip){
        if (empty($ip)){
            return -1;
        }

        $bytes = self::ipEnsureObject($ip)->getBytes();
        $ret = 0;
        for ($i = 0; $i < 4; $i++) {
            $ret += $bytes[$i] * (2 ** ((3 - $i) * 8));
        }

        return $ret;
    }

    /**
     * Sorts the collection by IPs
     * @param array|Collection $col
     * @param $callback
     * @return \Illuminate\Support\Collection
     */
    public static function sortByIPs($col, $callback){
        if ($col === null){
            return collect();
        }

        $cnt = $col instanceof Collection ? $col->count() : count($col);
        if ($cnt <= 1){
            return collect($col);
        }

        // global ordering, id -> ranking, sort by the ranking
        $results = [];
        $callback = DataTools::valueRetriever($callback);

        // First we will loop through the items and get the comparator from a callback
        // function which we were given. Then, we will sort the returned values and
        // and grab the corresponding values for the sorted keys from this array.
        foreach ($col as $key => $value) {
            $results[$key] = self::computeIpSortKey($callback($value, $key));
        }

        uasort($results, function($a, $b){
            $aCat = $a[0];
            $bCat = $b[0];
            if ($aCat != $bCat){
                return $aCat - $bCat;
            }

            return DataTools::compareArrays($a[1], $b[1]);
        });

        // Once we have sorted all of the keys in the array, we will loop through them
        // and grab the corresponding model so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $col->get($key);
        }

        return collect($results);
    }

    /**
     * Computes sort key for sorting by IP - optimized sort.
     * Otherwise this is computed with each comparison - overhead.
     * @param $ip
     * @return array
     */
    public static function computeIpSortKey($ip){
        if (empty($ip)){
            return [0, null];
        }

        $cat = self::quickIpCat($ip);
        $numConv = $cat == 2 ? function($x) {
            return intval($x);
        } : function($x){
            return intval($x, 16);
        };

        $oct = array_map($numConv, explode($cat == 2 ? '.' : ':', $ip));
        return [$cat, $oct];
    }

    /**
     * Quick & unreliable IP address categorization. IPv4 vs IPv6
     * @param $ip
     * @return int 10 for IPv6, 2 for IPv4
     */
    public static function quickIpCat($ip){
        return strpos($ip, ':') !== false ? 10 : 2;
    }

    /**
     * Comparator on IP addresses.
     * @param $a
     * @param $b
     * @return int
     */
    public static function compareIps($a, $b){
        // exact
        if ($a === $b){
            return 0;
        }

        // emptyness
        if (empty($a)){
            return -1;
        } else if (empty($b)){
            return 1;
        }

        // categories, ipv4 first
        $aCat = self::quickIpCat($a);
        $bCat = self::quickIpCat($b);
        if ($aCat != $bCat){
            return $aCat == 2 ? -1 : 1;
        }

        // octets
        $numConv = $aCat == 2 ? function($x) {
            return intval($x);
        } : function($x){
            return intval($x, 16);
        };

        $aOct = array_map($numConv, explode($aCat == 2 ? '.' : ':', $a));
        $bOct = array_map($numConv, explode($aCat == 2 ? '.' : ':', $b));
        return DataTools::compareArrays($aOct, $bOct);
    }

    /**
     * Parses host spec addr[:port]
     * @param $str
     * @return HostSpec
     * @throws HostSpecParseException
     */
    public static function hostSpecParse($str){
        $mIpv4 = null;
        $mIpv6 = null;
        $mHostname = null;

        $ip4Match = preg_match(
            '/^\s*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})(?::([0-9]{1,5}))?\s*$/', $str, $mIpv4);
        $ip6Match = preg_match(
            '/^\s*\[([0-9a-fA-F:.]+)\]:([0-9]{1,5})\s*$/', $str, $mIpv6);
        $hostMatch = preg_match(
            '/^\b((?=[a-z0-9-]{1,63}\.)(xn--)?[a-z0-9]+(-[a-z0-9]+)*\.)+([a-z]{2,63})\b(?::([0-9]{1,5}))?$/', $str, $mHostname);

        if ($hostMatch){
            return new HostSpec($mHostname[1], $mHostname[5], HostSpec::ADDR_DOMAIN);
        }

        if ($ip6Match){
            return new HostSpec($mIpv6[1], $mIpv6[2], HostSpec::ADDR_IPv6);
        }

        if ($ip4Match){
            return new HostSpec($mIpv4[1], $mIpv4[2], HostSpec::ADDR_IPv4);
        }

        throw new HostSpecParseException('HostSpec parse error');
    }

    /**
     * Parses host spec addr[:port], returns null on parse error.
     * @param $str
     * @return HostSpec|null
     */
    public static function tryHostSpecParse($str){
        try {
            return self::hostSpecParse($str);
        } catch(HostSpecParseException $e){
            return null;
        }
    }

}
