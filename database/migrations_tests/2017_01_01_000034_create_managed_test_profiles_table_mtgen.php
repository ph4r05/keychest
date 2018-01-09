<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManagedTestProfilesTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('managed_test_profiles', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->text('cert_renew_check_data', 65535)->nullable();
			$table->string('cert_renew_check_strategy')->nullable();
			$table->string('scan_key')->nullable();
			$table->smallInteger('scan_passive')->default(0);
			$table->string('scan_scheme')->nullable();
			$table->string('scan_port')->nullable();
			$table->smallInteger('scan_connect');
			$table->text('scan_data', 65535)->nullable();
			$table->bigInteger('scan_service_id')->nullable()->index('ix_managed_test_profiles_scan_service_id');
			$table->bigInteger('top_domain_id')->nullable()->index('ix_managed_test_profiles_top_domain_id');
			$table->timestamps();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('managed_test_profiles');
	}

}
