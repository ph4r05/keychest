<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManagedServiceToGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('managed_service_to_group', function(Blueprint $table)
		{
			$table->foreign('group_id', 'managed_service_to_group_group_id')->references('id')->on('managed_host_groups')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('service_id', 'managed_service_to_group_service_id')->references('id')->on('managed_services')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('managed_service_to_group', function(Blueprint $table)
		{
			$table->dropForeign('managed_service_to_group_group_id');
			$table->dropForeign('managed_service_to_group_service_id');
		});
	}

}
