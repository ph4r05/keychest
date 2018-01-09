<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubdomainResultsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subdomain_results', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('watch_id')->index('ix_subdomain_results_watch_id');
			$table->smallInteger('scan_type')->nullable();
			$table->smallInteger('scan_status')->nullable();
			$table->timestamps();
			$table->dateTime('last_scan_at')->nullable();
			$table->bigInteger('last_scan_idx')->nullable();
			$table->integer('num_scans')->nullable();
			$table->text('result', 65535)->nullable();
			$table->integer('result_size');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subdomain_results');
	}

}
