<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManagedHostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('managed_hosts', function(Blueprint $table)
		{
			$table->foreign('agent_id', 'managed_hosts_agent_id')->references('id')->on('keychest_agent')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('owner_id', 'managed_hosts_owner_id')->references('id')->on('owners')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('ssh_key_id', 'managed_hosts_ssh_keys_id')->references('id')->on('ssh_keys')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('managed_hosts', function(Blueprint $table)
		{
			$table->dropForeign('managed_hosts_agent_id');
			$table->dropForeign('managed_hosts_owner_id');
			$table->dropForeign('managed_hosts_ssh_keys_id');
		});
	}

}
