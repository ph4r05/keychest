<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManagedHostGroupsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('managed_host_groups', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('group_name')->nullable();
			$table->text('group_desc', 65535)->nullable();
			$table->text('group_data', 65535)->nullable();
			$table->timestamps();
			$table->softDeletes();
			$table->bigInteger('owner_id')->nullable()->index('ix_managed_host_groups_owner_id');
			$table->unique(['group_name','owner_id'], 'uk_managed_host_groups_name_owner');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('managed_host_groups');
	}

}
