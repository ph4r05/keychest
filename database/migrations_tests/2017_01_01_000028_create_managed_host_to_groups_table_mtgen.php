<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManagedHostToGroupsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('managed_host_to_groups', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('host_id')->index('ix_managed_host_to_groups_host_id');
			$table->bigInteger('group_id')->index('ix_managed_host_to_groups_group_id');
			$table->timestamps();
			$table->softDeletes();
			$table->unique(['host_id','group_id'], 'uk_managed_host_to_groups_host_group');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('managed_host_to_groups');
	}

}
