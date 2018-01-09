<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManagedServicesTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('managed_services', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('svc_display')->nullable();
			$table->string('svc_name')->nullable();
			$table->string('svc_provider')->nullable();
			$table->string('svc_deployment')->nullable();
			$table->string('svc_domain_auth')->nullable();
			$table->string('svc_config')->nullable();
			$table->text('svc_desc', 65535)->nullable();
			$table->text('svc_data', 65535)->nullable();
			$table->bigInteger('owner_id')->index('ix_managed_services_owner_id');
			$table->bigInteger('agent_id')->nullable()->index('ix_managed_services_agent_id');
			$table->timestamps();
			$table->softDeletes();
			$table->bigInteger('svc_watch_id')->nullable()->index('ix_managed_services_svc_watch_id');
			$table->bigInteger('test_profile_id')->nullable()->index('ix_managed_services_test_profile_id');
			$table->text('svc_aux_names', 65535)->nullable();
			$table->string('svc_ca')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('managed_services');
	}

}
