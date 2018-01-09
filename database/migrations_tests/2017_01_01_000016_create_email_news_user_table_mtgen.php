<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmailNewsUserTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('email_news_user', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('user_id')->unsigned()->index('ix_email_news_user_user_id');
			$table->bigInteger('email_news_id')->index('ix_email_news_user_email_news_id');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('email_news_user');
	}

}
