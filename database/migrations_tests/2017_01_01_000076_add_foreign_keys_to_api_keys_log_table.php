<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToApiKeysLogTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('api_keys_log', function(Blueprint $table)
		{
			$table->foreign('api_key_id', 'fk_api_keys_log_api_key_id')->references('id')->on('api_keys')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('api_keys_log', function(Blueprint $table)
		{
			$table->dropForeign('fk_api_keys_log_api_key_id');
		});
	}

}
