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
            ->withPivot(['deleted_at', 'scan_periodicity', 'scan_type']);
    }
}
