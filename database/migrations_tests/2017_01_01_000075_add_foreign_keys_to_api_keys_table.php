<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToApiKeysTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('api_keys', function(Blueprint $table)
		{
			$table->foreign('user_id', 'fk_api_keys_user_users_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('api_keys', function(Blueprint $table)
		{
			$table->dropForeign('fk_api_keys_user_users_id');
		});
	}

}
