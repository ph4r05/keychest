<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Carbon converted date fields
     * @return array
     */
    public function getDates()
    {
        return array('created_at', 'updated_at', 'last_email_report_sent_at',
            'last_email_no_servers_sent_at', 'last_email_report_enqueued_at');
    }

    /**
     * Watch targets that belongs to the user
     */
    public function watchTargets()
    {
        return $this->belongsToMany(
            'App\Models\WatchTarget',
            'user_watch_target',
            'user_id',
            'watch_id')
            ->withTimestamps()
            ->withPivot(['deleted_at', 'disabled_at', 'scan_periodicity', 'scan_type']);
    }

    /**
     * Active watches only
     * @return mixed
     */
    public function activeWatchTargets(){
        return $this->watchTargets()
            ->whereNull('deleted_at')
            ->whereNull('disabled_at');
    }

    /**
     * Latest DNS scan
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function latestDnsScan(){
        return $this->belongsTo('\App\Models\DnsResult', 'last_dns_scan_id');
    }

    /**
     * Email news already sent to the user
     */
    public function emailNews()
    {
        return $this->belongsToMany(
            'App\Models\EmailNews',
            'email_news_user',
            'user_id',
            'email_news_id')
            ->withTimestamps();
    }
}
