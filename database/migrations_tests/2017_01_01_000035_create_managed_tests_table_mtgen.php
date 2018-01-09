<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManagedTestsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('managed_tests', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('solution_id')->index('ix_managed_tests_solution_id');
			$table->bigInteger('service_id')->index('ix_managed_tests_service_id');
			$table->bigInteger('host_id')->nullable()->index('ix_managed_tests_host_id');
			$table->text('scan_data', 65535)->nullable();
			$table->dateTime('last_scan_at')->nullable();
			$table->smallInteger('last_scan_status')->nullable();
			$table->text('last_scan_data', 65535)->nullable();
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
		Schema::drop('managed_tests');
	}

}
