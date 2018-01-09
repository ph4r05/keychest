<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateApiKeysLogTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_keys_log', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->timestamps();
			$table->bigInteger('api_key_id')->nullable()->index('ix_api_keys_log_api_key_id');
			$table->string('req_ip', 191)->nullable();
			$table->string('req_email', 191)->nullable();
			$table->string('req_challenge', 191)->nullable();
			$table->string('action_type', 191)->nullable()->index('ix_api_keys_log_action_type');
			$table->text('action_data', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('api_keys_log');
	}

}
