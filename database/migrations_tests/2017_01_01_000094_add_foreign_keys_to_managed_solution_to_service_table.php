<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManagedSolutionToServiceTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('managed_solution_to_service', function(Blueprint $table)
		{
			$table->foreign('solution_id', 'managed_solution_to_service_solution_id')->references('id')->on('managed_solutions')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('service_id', 'managed_solution_to_service_service_id')->references('id')->on('managed_services')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('managed_solution_to_service', function(Blueprint $table)
		{
			$table->dropForeign('managed_solution_to_service_solution_id');
			$table->dropForeign('managed_solution_to_service_service_id');
		});
	}

}
