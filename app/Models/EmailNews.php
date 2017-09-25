<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.08.17
 * Time: 15:40
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailNews extends Model
{
    use SoftDeletes;

    const TABLE = 'email_news';

    protected $guarded = array();

    protected $table = self::TABLE;

    public function getDates()
    {
        return array('created_at', 'updated_at', 'schedule_at', 'deleted_at', 'disabled_at');
    }

    /**
     * Users these email news were already sent
     */
    public function users()
    {
        return $this->belongsToMany(
            'App\Models\User',
            'email_news_user',
            'email_news_id',
            'user_id')
            ->withTimestamps();
    }
}
