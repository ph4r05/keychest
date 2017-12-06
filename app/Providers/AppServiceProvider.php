<?php

namespace App\Providers;

use App\Keychest\Services\AnalysisManager;
use App\Keychest\Services\ApiKeyTokenManager;
use App\Keychest\Services\ApiManager;
use App\Keychest\Services\CredentialsManager;
use App\Keychest\Services\EmailManager;
use App\Keychest\Services\IpScanManager;
use App\Keychest\Services\LicenseManager;
use App\Keychest\Services\Management\HostGroupManager;
use App\Keychest\Services\Management\HostManager;
use App\Keychest\Services\ScanManager;
use App\Keychest\Services\ServerManager;
use App\Keychest\Services\SubdomainManager;
use App\Keychest\Services\UserManager;
use App\Keychest\Services\UserTokenManager;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Dusk\DuskServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        $this->app->alias('request', 'App\Http\Request\ParamRequest');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }

        // Registering sub-components, services, managers.
        $this->app->bind(UserTokenManager::class, function(Application $app){
            return new UserTokenManager($app);
        });
        $this->app->bind(ApiKeyTokenManager::class, function(Application $app){
            return new ApiKeyTokenManager($app);
        });
        $this->app->bind(ServerManager::class, function($app){
            return new ServerManager($app);
        });
        $this->app->bind(SubdomainManager::class, function($app){
            return new SubdomainManager($app);
        });
        $this->app->bind(ScanManager::class, function($app){
            return new ScanManager($app);
        });
        $this->app->bind(IpScanManager::class, function($app){
            return new IpScanManager($app);
        });
        $this->app->bind(EmailManager::class, function($app){
            return new EmailManager($app);
        });
        $this->app->bind(LicenseManager::class, function($app){
            return new LicenseManager($app);
        });
        $this->app->bind(AnalysisManager::class, function(Application $app){
            return new AnalysisManager($app);
        });
        $this->app->bind(UserManager::class, function(Application $app){
            return new UserManager($app);
        });
        $this->app->bind(ApiManager::class, function(Application $app){
            return new ApiManager($app);
        });
        $this->app->bind(CredentialsManager::class, function(Application $app){
            return new CredentialsManager($app);
        });
        $this->app->bind(HostManager::class, function(Application $app){
            return new HostManager($app);
        });
        $this->app->bind(HostGroupManager::class, function(Application $app){
            return new HostGroupManager($app);
        });
    }
}
