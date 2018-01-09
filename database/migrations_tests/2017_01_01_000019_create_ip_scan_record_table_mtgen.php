<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIpScanRecordTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ip_scan_record', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('service_name');
			$table->bigInteger('service_id')->nullable()->index('ix_ip_scan_record_service_id');
			$table->string('ip_beg', 24);
			$table->string('ip_end', 24);
			$table->bigInteger('ip_beg_int')->nullable();
			$table->bigInteger('ip_end_int')->nullable();
			$table->timestamps();
			$table->dateTime('last_scan_at')->nullable();
			$table->smallInteger('last_scan_state')->nullable();
			$table->integer('num_scans')->nullable()->default(0);
			$table->bigInteger('last_result_id')->nullable()->index('ix_ip_scan_record_last_result_id');
			$table->integer('service_port')->default(443);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ip_scan_record');
	}

}
