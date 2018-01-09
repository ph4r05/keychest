<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEmailNewsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('email_news', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->timestamps();
			$table->dateTime('schedule_at')->nullable();
			$table->softDeletes();
			$table->dateTime('disabled_at')->nullable();
			$table->text('message', 65535)->nullable();
			$table->dateTime('valid_to')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('email_news');
	}

}
