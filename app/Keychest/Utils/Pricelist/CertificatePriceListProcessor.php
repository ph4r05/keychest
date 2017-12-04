<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 29.11.17
 * Time: 14:53
 */

namespace App\Keychest\Utils\Pricelist;


use Illuminate\Foundation\Application;
use Illuminate\Support\Str;

class CertificatePriceListProcessor
{
    /**
     * Main pricelist holder
     * @var
     */
    protected $priceList;

    /**
     * Exact matches
     * @var
     */
    protected $matchMap = [];

    /**
     * Regex map
     * @var
     */
    protected $regexes = [];

    /**
     * Default entry
     * @var
     */
    protected $default;

    /**
     * CertificatePriceListProcessor constructor.
     * @param $priceList
     */
    public function __construct($priceList)
    {
        $this->priceList = $priceList;
        foreach($priceList as $entry){
            if ($entry->issuer_org == '*'){
                $this->default = $entry;
                continue;
            }

            if (starts_with($entry->issuer_org, ['/'])){
                $this->regexes[] = $entry;
            } else {
                $this->matchMap[strtolower($entry->issuer_org)] = $entry;
            }
        }
    }

    /**
     * Default rule
     * @return mixed
     */
    public function defaultRule(){
        return $this->default;
    }

    /**
     * Returns the matching rule
     * @param $certificate
     * @return mixed|null
     */
    public function findMatch($certificate, $issuerCol=null){
        $issuerCol = $issuerCol ?: 'issuer_o';
        $iorg = strtolower($certificate->$issuerCol);

        // 1. check for exact matches
        if (isset($this->matchMap[$iorg])){
            return $this->matchMap[$iorg];
        }

        // 2. check for regex
        foreach ($this->regexes as $rule) {
            if (preg_match($rule->name, $iorg)){
                return $rule;
            }
        }

        return null;
    }

    /**
     * Returns particular price for the certificate, directly sets the cert fields
     * @param $certificate
     * @param $rule
     */
    public function priceCertificate($certificate, $rule)
    {
        if (empty($rule)) {
            $certificate->rule_id = null;
            return;
        }

        $certificate->rule_id = $rule->id;
        $crt_ev = $certificate->is_ev;
        $crt_ov = $certificate->is_ov;
        $crt_wild = $certificate->is_cn_wildcard || $certificate->is_alt_wildcard;

        if ($crt_ev && $crt_wild) {
            if ($rule->price_ev_wildcard){
                $certificate->price = $rule->price_ev_wildcard;

            } elseif ($rule->price_ev){
                $certificate->price = $rule->price_ev;
                $certificate->price_approx = true;

            } elseif ($rule->price_wildcard){
                $certificate->price = $rule->price_wildcard;
                $certificate->price_approx = true;

            } else {
                $certificate->price = $rule->price;
                $certificate->price_approx = true;
            }

        } elseif ($crt_ev) {
            if ($rule->price_ev){
                $certificate->price = $rule->price_ev;

            } else {
                $certificate->price = $rule->price;
                $certificate->price_approx = true;
            }

        } elseif ($crt_ov && $crt_wild){
            if ($rule->price_ov_wildcard){
                $certificate->price = $rule->price_ov_wildcard;

            } elseif ($rule->price_ov){
                $certificate->price = $rule->price_ov;
                $certificate->price_approx = true;

            } elseif ($rule->price_wildcard){
                $certificate->price = $rule->price_wildcard;
                $certificate->price_approx = true;

            } else {
                $certificate->price = $rule->price;
                $certificate->price_approx = true;
            }

        } elseif ($crt_ov){
            if ($rule->price_ov) {
                $certificate->price = $rule->price_ov;

            } else {
                $certificate->price = $rule->price;
                $certificate->price_approx = true;
            }

        } elseif ($crt_wild) {
            if ($rule->price_wildcard){
                $certificate->price = $rule->price_wildcard;

            } else {
                $certificate->price = $rule->price;
                $certificate->price_approx = true;
            }


        } else {
            $certificate->price = $rule->price;

        }
    }

}