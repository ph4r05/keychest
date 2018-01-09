<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToApiWaitingTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('api_waiting', function(Blueprint $table)
		{
			$table->foreign('api_key_id', 'fk_api_waiting_api_key_id')->references('id')->on('api_keys')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('certificate_id', 'fk_api_waiting_certificate_id')->references('id')->on('certificates')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('api_waiting', function(Blueprint $table)
		{
			$table->dropForeign('fk_api_waiting_api_key_id');
			$table->dropForeign('fk_api_waiting_certificate_id');
		});
	}

}
