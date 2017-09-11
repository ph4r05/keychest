<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 16.08.17
 * Time: 15:40
 */

namespace App\Models;

use App\Keychest\Uuids;
use Illuminate\Database\Eloquent\Model;

class EmailNews extends Model
{
    const TABLE = 'email_news';

    public $incrementing = true;

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
            'App\User',
            'email_news_user',
            'email_news_id',
            'user_id')
            ->withTimestamps();
    }
}
