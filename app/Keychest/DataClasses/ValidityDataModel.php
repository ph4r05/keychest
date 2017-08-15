<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 15.08.17
 * Time: 14:15
 */

namespace App\Keychest\DataClasses;

use Illuminate\Support\Collection;

class ValidityDataModel {
    /**
     * @var Collection
     */
    public $activeWatches;

    /**
     * @derived
     * @var Collection
     */
    public $activeWatchesIds;

    /**
     * @var Collection
     */
    public $dnsScans;

    /**
     * @var Collection
     */
    public $tlsScans;

    /**
     * @derived
     * @var Collection
     */
    public $tlsScansGrp;

    /**
     * @var Collection
     */
    public $crtshScans;

    /**
     * @var Collection
     */
    public $watch2certsTls;

    /**
     * Mapping cert back to IP & watch id it was taken from
     * @var Collection
     */
    public $cert2tls;

    /**
     * Mapping
     * @var Collection
     */
    public $tlsCertsIds;

    /**
     * Mapping, watch_id -> array of certificate ids
     * @var Collection
     */
    public $watch2certsCrtsh;

    /**
     * Mapping, watch_id -> array of certificate ids
     * @var Collection
     */
    public $crtshCertIds;

    /**
     * Mapping, cert id -> watches contained in, tls watch, crtsh watch detection
     * @var Collection
     */
    public $cert2watchTls;

    /**
     * Mapping
     * @var Collection
     */
    public $cert2watchCrtsh;

    /**
     * @var Collection
     */
    public $certsToLoad;

    /**
     * @var Collection
     */
    public $certs;

    /**
     * @var Collection
     */
    public $topDomainsMap;

    /**
     * @var Collection
     */
    public $topDomainToWatch;

    /**
     * @var Collection
     */
    public $topDomainIds;

    /**
     * @var Collection
     */
    public $whoisScans;

    //
    // Processed
    //

    /*
    |--------------------------------------------------------------------------
    | Getters & Setters
    |--------------------------------------------------------------------------
    |
    */

    /**
     * @var Collection
     */
    public $tlsCerts;

    /**
     * @return Collection
     */
    public function getActiveWatches()
    {
        return $this->activeWatches;
    }

    /**
     * @param Collection $activeWatches
     */
    public function setActiveWatches($activeWatches)
    {
        $this->activeWatches = $activeWatches;
    }

    /**
     * @return Collection
     */
    public function getActiveWatchesIds()
    {
        return $this->activeWatchesIds;
    }

    /**
     * @param Collection $activeWatchesIds
     */
    public function setActiveWatchesIds($activeWatchesIds)
    {
        $this->activeWatchesIds = $activeWatchesIds;
    }

    /**
     * @return Collection
     */
    public function getDnsScans()
    {
        return $this->dnsScans;
    }

    /**
     * @param Collection $dnsScans
     */
    public function setDnsScans($dnsScans)
    {
        $this->dnsScans = $dnsScans;
    }

    /**
     * @return Collection
     */
    public function getTlsScans()
    {
        return $this->tlsScans;
    }

    /**
     * @param Collection $tlsScans
     */
    public function setTlsScans($tlsScans)
    {
        $this->tlsScans = $tlsScans;
    }

    /**
     * @return Collection
     */
    public function getTlsScansGrp()
    {
        return $this->tlsScansGrp;
    }

    /**
     * @param Collection $tlsScansGrp
     */
    public function setTlsScansGrp($tlsScansGrp)
    {
        $this->tlsScansGrp = $tlsScansGrp;
    }

    /**
     * @return Collection
     */
    public function getCrtshScans()
    {
        return $this->crtshScans;
    }

    /**
     * @param Collection $crtshScans
     */
    public function setCrtshScans($crtshScans)
    {
        $this->crtshScans = $crtshScans;
    }

    /**
     * @return Collection
     */
    public function getWatch2certsTls()
    {
        return $this->watch2certsTls;
    }

    /**
     * @param Collection $watch2certsTls
     */
    public function setWatch2certsTls($watch2certsTls)
    {
        $this->watch2certsTls = $watch2certsTls;
    }

    /**
     * @return Collection
     */
    public function getCert2tls()
    {
        return $this->cert2tls;
    }

    /**
     * @param Collection $cert2tls
     */
    public function setCert2tls($cert2tls)
    {
        $this->cert2tls = $cert2tls;
    }

    /**
     * @return Collection
     */
    public function getTlsCertsIds()
    {
        return $this->tlsCertsIds;
    }

    /**
     * @param Collection $tlsCertsIds
     */
    public function setTlsCertsIds($tlsCertsIds)
    {
        $this->tlsCertsIds = $tlsCertsIds;
    }

    /**
     * @return Collection
     */
    public function getWatch2certsCrtsh()
    {
        return $this->watch2certsCrtsh;
    }

    /**
     * @param Collection $watch2certsCrtsh
     */
    public function setWatch2certsCrtsh($watch2certsCrtsh)
    {
        $this->watch2certsCrtsh = $watch2certsCrtsh;
    }

    /**
     * @return Collection
     */
    public function getCrtshCertIds()
    {
        return $this->crtshCertIds;
    }

    /**
     * @param Collection $crtshCertIds
     */
    public function setCrtshCertIds($crtshCertIds)
    {
        $this->crtshCertIds = $crtshCertIds;
    }

    /**
     * @return Collection
     */
    public function getCert2watchTls()
    {
        return $this->cert2watchTls;
    }

    /**
     * @param Collection $cert2watchTls
     */
    public function setCert2watchTls($cert2watchTls)
    {
        $this->cert2watchTls = $cert2watchTls;
    }

    /**
     * @return Collection
     */
    public function getCert2watchCrtsh()
    {
        return $this->cert2watchCrtsh;
    }

    /**
     * @param Collection $cert2watchCrtsh
     */
    public function setCert2watchCrtsh($cert2watchCrtsh)
    {
        $this->cert2watchCrtsh = $cert2watchCrtsh;
    }

    /**
     * @return Collection
     */
    public function getCertsToLoad()
    {
        return $this->certsToLoad;
    }

    /**
     * @param Collection $certsToLoad
     */
    public function setCertsToLoad($certsToLoad)
    {
        $this->certsToLoad = $certsToLoad;
    }

    /**
     * @return Collection
     */
    public function getCerts()
    {
        return $this->certs;
    }

    /**
     * @param Collection $certs
     */
    public function setCerts($certs)
    {
        $this->certs = $certs;
    }

    /**
     * @return Collection
     */
    public function getTopDomainsMap()
    {
        return $this->topDomainsMap;
    }

    /**
     * @param Collection $topDomainsMap
     */
    public function setTopDomainsMap($topDomainsMap)
    {
        $this->topDomainsMap = $topDomainsMap;
    }

    /**
     * @return Collection
     */
    public function getTopDomainToWatch()
    {
        return $this->topDomainToWatch;
    }

    /**
     * @param Collection $topDomainToWatch
     */
    public function setTopDomainToWatch($topDomainToWatch)
    {
        $this->topDomainToWatch = $topDomainToWatch;
    }

    /**
     * @return Collection
     */
    public function getTopDomainIds()
    {
        return $this->topDomainIds;
    }

    /**
     * @param Collection $topDomainIds
     */
    public function setTopDomainIds($topDomainIds)
    {
        $this->topDomainIds = $topDomainIds;
    }

    /**
     * @return Collection
     */
    public function getWhoisScans()
    {
        return $this->whoisScans;
    }

    /**
     * @param Collection $whoisScans
     */
    public function setWhoisScans($whoisScans)
    {
        $this->whoisScans = $whoisScans;
    }

    /**
     * @return Collection
     */
    public function getTlsCerts()
    {
        return $this->tlsCerts;
    }

    /**
     * @param Collection $tlsCerts
     */
    public function setTlsCerts($tlsCerts)
    {
        $this->tlsCerts = $tlsCerts;
    }


}