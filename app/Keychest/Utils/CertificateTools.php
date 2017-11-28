<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 28.09.17
 * Time: 14:02
 */

namespace App\Keychest\Utils;


use App\Keychest\Utils\Exceptions\MultipleCertificatesException;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;

class CertificateTools
{
    /**
     * Parses the single certificate from the cert
     * @param string $pem certificate string encoded
     * @return string
     * @throws MultipleCertificatesException
     */
    public static function sanitizeCertificate($pem){
        if (substr_count($pem, '-----BEGIN') > 1){
            throw new MultipleCertificatesException('Only one certificate expected');
        }

        $pem = preg_replace('/\s*-----\s*BEGIN\s+CERTIFICATE\s*-----\s*/', '', $pem);
        $pem = preg_replace('/\s*-----\s*END\s+CERTIFICATE\s*-----\s*/', '', $pem);
        $pem = preg_replace('/[^A-Za-z0-9+=_\/]/', '', $pem);
        return $pem;
    }

    /**
     * Simple normalization of the certificate issuer organization for better matching and consistency.
     * @param $issuerOrg
     * @return mixed
     */
    public static function normalizeIssuerOrg($issuerOrg){
        if (empty($issuerOrg)){
            return $issuerOrg;
        }

        $n = DataTools::capitalizeFirstWord($issuerOrg);
        $n = preg_replace('/[iI]nc\.?$/', 'Inc.', $n);
        $n = preg_replace('/([a-zA-Z0-9]) Inc\.$/', '\\1, Inc.', $n);
        return $n;
    }
}
