<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIpScanResultTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ip_scan_result', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('ip_scan_record_id')->index('ix_ip_scan_result_ip_scan_record_id');
			$table->timestamps();
			$table->dateTime('finished_at')->nullable();
			$table->bigInteger('duration')->nullable();
			$table->dateTime('last_scan_at')->nullable();
			$table->smallInteger('last_scan_state')->nullable();
			$table->integer('num_scans')->nullable()->default(1);
			$table->integer('num_ips_alive')->default(0);
			$table->integer('num_ips_found')->default(0);
			$table->text('ips_alive', 65535)->nullable();
			$table->text('ips_found', 65535)->nullable();
			$table->text('ips_alive_ids', 65535)->nullable();
			$table->text('ips_found_ids', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ip_scan_result');
	}

}
