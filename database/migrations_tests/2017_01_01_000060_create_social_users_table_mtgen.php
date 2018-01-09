<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSocialUsersTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('social_users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index('social_users_user_id_foreign');
			$table->string('social_id', 191);
			$table->string('social_type', 191);
			$table->string('nickname', 191)->nullable();
			$table->string('name', 191)->nullable();
			$table->string('email', 191);
			$table->string('avatar', 191)->nullable();
			$table->text('meta', 65535);
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
		Schema::drop('social_users');
	}

}
