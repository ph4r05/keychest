<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 15.08.17
 * Time: 14:15
 */

namespace App\Keychest\DataClasses;

use App\User;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class ValidityDataModel {
    // use SerializesModels;

    /**
     * @var User
     */
    public $user;

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
    | Constructor
    |--------------------------------------------------------------------------
    |
    */

    /**
     * ValidityDataModel constructor.
     * @param User|null $user
     */
    public function __construct(User $user=null)
    {
        $this->setUser($user);
    }

    /*
    |--------------------------------------------------------------------------
    | Derived data views
    |--------------------------------------------------------------------------
    |
    */

    /**
     * Number of all certificates loaded
     * @return mixed
     */
    public function getNumAllCerts()
    {
        return $this->getCerts() ? $this->getCerts()->count() : 0;
    }

    /**
     * Number of TLS certificates
     * @return int
     */
    public function getNumCertsActive()
    {
        return $this->getTlsCerts()->count();
    }

    /**
     * @return Collection
     */
    public function getTlsCerts()
    {
        return $this->getCerts() ? $this->getCerts()
            ->filter(function ($value, $key) {
                return $value->found_tls_scan;
            })
            ->sortBy('valid_to') : collect();
    }

    /**
     * TLS expired certs
     * @return Collection
     */
    public function getCertExpired()
    {
        return $this->getTlsCerts()->filter(function ($value, $key) {
            return Carbon::now()->greaterThanOrEqualTo($value->valid_to);
        });
    }

    /**
     * TLS expire in 7 days
     * @return Collection
     */
    public function getCertExpire7days()
    {
        return $this->getTlsCerts()->filter(function ($value, $key) {
            return Carbon::now()->lessThanOrEqualTo($value->valid_to)
                && Carbon::now()->addDays(7)->greaterThanOrEqualTo($value->valid_to);
        });
    }

    /**
     * TLS expire in 28 days
     * @return Collection
     */
    public function getCertExpire28days()
    {
        return $this->getTlsCerts()->filter(function ($value, $key) {
            return Carbon::now()->addDays(7)->lessThanOrEqualTo($value->valid_to)
                && Carbon::now()->addDays(28)->greaterThanOrEqualTo($value->valid_to);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Getters & Setters
    |--------------------------------------------------------------------------
    |
    */

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

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
}
