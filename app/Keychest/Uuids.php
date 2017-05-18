<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 18.05.17
 * Time: 16:21
 */

namespace App\Keychest;

use App;
use Webpatser\Uuid\Uuid;

trait Uuids
{

    /**
     * Boot function from laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Uuid::generate()->string;
            }
        });
    }
}

