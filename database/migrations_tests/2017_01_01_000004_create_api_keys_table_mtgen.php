<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateApiKeysTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_keys', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->timestamps();
			$table->string('name', 191)->nullable();
			$table->string('api_key', 191)->nullable()->unique('ix_api_keys_api_key');
			$table->string('email_claim', 191)->nullable()->index('ix_api_keys_email_claim');
			$table->string('ip_registration', 191)->nullable()->index('ix_api_keys_ip_registration');
			$table->integer('user_id')->unsigned()->nullable()->index('ix_api_keys_user_id');
			$table->dateTime('last_seen_active_at')->nullable();
			$table->string('last_seen_ip', 191)->nullable();
			$table->dateTime('verified_at')->nullable();
			$table->dateTime('revoked_at')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('api_keys');
	}

}
