<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 09.06.17
 * Time: 16:43
 */

namespace App\Keychest\Services;

use App\Keychest\DataClasses\UnsubscribeResult;
use App\Keychest\Services\Exceptions\CannotSendEmailException;
use App\Keychest\Services\Exceptions\NoEmailUserAccountBlockedException;
use App\Keychest\Services\Exceptions\NoEmailUserAccountClosedException;
use App\Keychest\Services\Exceptions\NoEmailUserDeletedException;
use App\Keychest\Services\Exceptions\NoEmailUserUnverifiedException;
use App\Models\EmailNews;
use App\Models\User;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EmailManager {

    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Create a new Auth manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Loads list of the email news to send to the user - not sent already.
     * @param User $user
     * @return Collection
     */
    public function loadEmailNewsToSend(User $user){
        $q = EmailNews::query()
            ->whereDoesntHave('users', function(Builder $query) use($user) {
                $query->where('users.id', '=', $user->id);
            })
            ->whereNull('deleted_at')
            ->whereNull('disabled_at')
            ->where(function(Builder $query){
                $query->whereNull('schedule_at')
                    ->orWhere('schedule_at', '<=', 'NOW()');
            })
            ->where(function(Builder $query){
                $query->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', 'NOW()');
            })->orderBy('created_at');

        $newsDb = $q->get();

        return $newsDb->transform(function ($item, $key) {
            $item->show_at = $item->schedule_at ? $item->schedule_at : $item->created_at;
            return $item;
        })->sortBy('show_at');
    }

    /**
     * Associates news to the user - it means they were sent already.
     * @param User $user
     * @param Collection $news
     */
    public function associateNewsToUser(User $user, Collection $news){
        if (!$news || $news->isEmpty()){
            return;
        }

        $user->emailNews()->attach(
            array_combine(
                $news->pluck('id')->values()->all(),
                array_fill(0, $news->count(), ['created_at' => Carbon::now()])
            )
        );
    }

    /**
     * Returns the user of the unsubscribe action
     * @param $token
     * @return UnsubscribeResult
     */
    public function unsubscribe($token){
        $res = new UnsubscribeResult();

        $u = User::query()->where('weekly_unsubscribe_token', '=', $token)->first();
        $res->setUser($u)->setTokenFound(!!$u);
        if (!$u){
            return $res;
        }

        $u->weekly_emails_disabled = 1;
        $u->save();

        return $res;
    }

    /**
     * General test if email can be sent to the user.
     * @param $user
     * @return bool
     * @throws NoEmailUserAccountBlockedException
     * @throws NoEmailUserAccountClosedException
     * @throws NoEmailUserDeletedException
     * @throws NoEmailUserUnverifiedException
     */
    public function canSendEmailToUser($user){
        if (!empty($user->deleted_at)){
            Log::debug('User deleted, no emails: ' . $user->id);
            throw new NoEmailUserDeletedException();
        }

        if (!empty($user->closed_at)){
            Log::debug('User closed, no emails: ' . $user->id);
            throw new NoEmailUserAccountClosedException();
        }

        if (!empty($user->blocked_at)){
            Log::debug('User blocked, no emails: ' . $user->id);
            throw new NoEmailUserAccountBlockedException();
        }

        // Not verified
        if (!empty($user->auto_created_at) && empty($user->verified_at)){
            Log::debug('User not verified, no emails: ' . $user->id);
            throw new NoEmailUserUnverifiedException();
        }

        return true;
    }

    /**
     * General test if email can be sent to the user.
     *
     * @param $user
     * @return bool true if email can be sent
     */
    public function tryCanSendEmailToUser($user){
        try {
            return $this->canSendEmailToUser($user);
        } catch (CannotSendEmailException $e){
            return false;
        }
    }
}
