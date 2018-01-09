<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManagedSolutionToServiceTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('managed_solution_to_service', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('service_id')->index('ix_managed_solution_to_service_service_id');
			$table->bigInteger('solution_id')->index('ix_managed_solution_to_service_solution_id');
			$table->timestamps();
			$table->softDeletes();
			$table->unique(['service_id','solution_id'], 'uk_managed_solution_to_service_sol_svc');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('managed_solution_to_service');
	}

}
