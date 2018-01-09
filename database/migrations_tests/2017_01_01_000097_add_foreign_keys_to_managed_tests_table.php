<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManagedTestsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('managed_tests', function(Blueprint $table)
		{
			$table->foreign('host_id', 'fk_managed_tests_managed_host_id')->references('id')->on('managed_hosts')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('service_id', 'fk_managed_tests_managed_service_id')->references('id')->on('managed_services')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('solution_id', 'fk_managed_tests_managed_solution_id')->references('id')->on('managed_solutions')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('managed_tests', function(Blueprint $table)
		{
			$table->dropForeign('fk_managed_tests_managed_host_id');
			$table->dropForeign('fk_managed_tests_managed_service_id');
			$table->dropForeign('fk_managed_tests_managed_solution_id');
		});
	}

}
