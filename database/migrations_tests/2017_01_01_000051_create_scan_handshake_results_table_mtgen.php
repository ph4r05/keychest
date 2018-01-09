<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScanHandshakeResultsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scan_handshake_results', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('scan_id')->nullable()->index('ix_scan_handshake_results_scan_id');
			$table->bigInteger('job_id')->nullable();
			$table->bigInteger('crt_id')->nullable();
			$table->bigInteger('crt_sh_id')->nullable();
			$table->smallInteger('was_new')->nullable();
			$table->smallInteger('is_ca')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scan_handshake_results');
	}

}
