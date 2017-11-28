<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

class AddBasicRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call('cache:forget', ['key' => 'spatie.permission.cache']);

        $this->ensureRole('readonly');
        $this->ensureRole('cio');
        $this->ensureRole('cfo');
        $this->ensureRole('ciso');
        $this->ensureRole('bso');
        $this->ensureRole('sa');
        $admin = $this->ensureRole('admin');
        $this->ensureRole('superadmin');

        // Add all users to admin as they have no structure at the moment and everybody is its own admin
        $users = \App\Models\User::query()->get();
        foreach($users as $user){
            $user->syncRoles([$admin]);
        }

        Artisan::call('cache:forget', ['key' => 'spatie.permission.cache']);
    }

    /**
     * Load or create a role
     * @param $role
     * @return $this|\Illuminate\Database\Eloquent\Model|\Spatie\Permission\Contracts\Role|Role
     */
    protected function ensureRole($role){
        return \App\Keychest\Utils\Roles::findOrCreate($role);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Artisan::call('cache:forget', ['key' => 'spatie.permission.cache']);
    }
}
