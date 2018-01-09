<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEmailNewsUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('email_news_user', function(Blueprint $table)
		{
			$table->foreign('email_news_id', 'fk_email_news_user_email_news_id')->references('id')->on('email_news')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('user_id', 'fk_email_news_user_users_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('email_news_user', function(Blueprint $table)
		{
			$table->dropForeign('fk_email_news_user_email_news_id');
			$table->dropForeign('fk_email_news_user_users_id');
		});
	}

}
