<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSshKeysTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('ssh_keys', function(Blueprint $table)
		{
			$table->foreign('owner_id', 'ssh_keys_owner_id')->references('id')->on('owners')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('user_id', 'ssh_keys_users_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('ssh_keys', function(Blueprint $table)
		{
			$table->dropForeign('ssh_keys_owner_id');
			$table->dropForeign('ssh_keys_users_id');
		});
	}

}
