<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAccessTokensTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('access_tokens', function(Blueprint $table)
		{
			$table->foreign('api_key_id', 'fk_access_tokens_api_key_id')->references('id')->on('api_keys')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('user_id', 'fk_access_tokens_user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('access_tokens', function(Blueprint $table)
		{
			$table->dropForeign('fk_access_tokens_api_key_id');
			$table->dropForeign('fk_access_tokens_user_id');
		});
	}

}
