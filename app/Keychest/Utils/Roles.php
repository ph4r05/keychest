<?php
/**
 * Created by PhpStorm.
 * User: dusanklinec
 * Date: 28.11.17
 * Time: 14:38
 */

namespace App\Keychest\Utils;


use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Models\Role;

class Roles
{
    /**
     * Finds the role or creates a new one if not found.
     *
     * @param $role
     * @return $this|\Illuminate\Database\Eloquent\Model|\Spatie\Permission\Contracts\Role|Role
     */
    public static function findOrCreate($role){
        try{
            return Role::findByName($role);
        } catch(RoleDoesNotExist $e){
            return Role::create(['name' => $role]);
        }
    }
}
