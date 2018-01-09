<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateManagedSolutionsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('managed_solutions', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('sol_display')->nullable();
			$table->string('sol_name')->nullable();
			$table->string('sol_type', 64)->nullable();
			$table->string('sol_assurance_level', 64)->nullable();
			$table->integer('sol_criticality')->nullable();
			$table->bigInteger('owner_id')->index('ix_managed_solutions_owner_id');
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
		Schema::drop('managed_solutions');
	}

}
