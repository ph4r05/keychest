<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUserToOwnerTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_to_owner', function(Blueprint $table)
		{
			$table->foreign('owner_id', 'fk_user_to_owner_owner_id')->references('id')->on('owners')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('user_id', 'fk_user_to_owner_user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_to_owner', function(Blueprint $table)
		{
			$table->dropForeign('fk_user_to_owner_owner_id');
			$table->dropForeign('fk_user_to_owner_user_id');
		});
	}

}
