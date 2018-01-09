<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManagedHostToGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('managed_host_to_groups', function(Blueprint $table)
		{
			$table->foreign('group_id', 'managed_host_to_groups_group_id')->references('id')->on('managed_host_groups')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('host_id', 'managed_host_to_groups_host_id')->references('id')->on('managed_hosts')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('managed_host_to_groups', function(Blueprint $table)
		{
			$table->dropForeign('managed_host_to_groups_group_id');
			$table->dropForeign('managed_host_to_groups_host_id');
		});
	}

}
