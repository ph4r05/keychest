<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAccessTokensTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('access_tokens', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->timestamps();
			$table->dateTime('expires_at')->nullable();
			$table->dateTime('sent_at')->nullable();
			$table->dateTime('last_sent_at')->nullable();
			$table->integer('num_sent')->nullable()->default(0);
			$table->bigInteger('api_key_id')->nullable()->index('ix_access_tokens_api_key_id');
			$table->integer('user_id')->unsigned()->nullable()->index('ix_access_tokens_user_id');
			$table->string('token_id', 40)->index('ix_access_tokens_token_id');
			$table->string('token', 191);
			$table->string('action_type', 191)->nullable()->index('ix_access_tokens_action_type');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('access_tokens');
	}

}
