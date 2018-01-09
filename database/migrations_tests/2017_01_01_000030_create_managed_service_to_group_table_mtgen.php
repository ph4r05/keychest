<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManagedServiceToGroupTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('managed_service_to_group', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('service_id')->index('ix_managed_service_to_group_service_id');
			$table->bigInteger('group_id')->index('ix_managed_service_to_group_group_id');
			$table->timestamps();
			$table->softDeletes();
			$table->unique(['service_id','group_id'], 'uk_managed_service_to_group_svc_group');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('managed_service_to_group');
	}

}
