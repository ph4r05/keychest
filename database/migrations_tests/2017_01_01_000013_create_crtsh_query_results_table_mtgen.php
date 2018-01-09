<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCrtshQueryResultsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('crtsh_query_results', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('query_id')->nullable()->index('ix_crtsh_query_results_query_id');
			$table->bigInteger('job_id')->nullable();
			$table->bigInteger('crt_id')->nullable();
			$table->bigInteger('crt_sh_id')->nullable();
			$table->smallInteger('was_new')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('crtsh_query_results');
	}

}
