<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->foreign('primary_owner_id', 'fk_users_primary_owner_id')->references('id')->on('owners')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('last_login_id', 'users_user_login_history_id')->references('id')->on('user_login_history')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('users', function(Blueprint $table)
		{
			$table->dropForeign('fk_users_primary_owner_id');
			$table->dropForeign('users_user_login_history_id');
		});
	}

}
