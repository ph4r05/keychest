<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserToOwnerTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_to_owner', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->integer('user_id')->unsigned()->index('ix_user_to_owner_user_id');
			$table->bigInteger('owner_id')->index('ix_user_to_owner_owner_id');
			$table->timestamps();
			$table->unique(['user_id','owner_id'], 'uk_user_to_owner_user_owner');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_to_owner');
	}

}
