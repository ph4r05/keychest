<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOwnerIpScanRecordTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('owner_ip_scan_record', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('owner_id')->index('ix_owner_ip_scan_record_owner_id');
			$table->bigInteger('ip_scan_record_id')->index('ix_owner_ip_scan_record_ip_scan_record_id');
			$table->timestamps();
			$table->softDeletes();
			$table->dateTime('disabled_at')->nullable();
			$table->bigInteger('scan_periodicity')->nullable();
			$table->smallInteger('auto_fill_watches');
			$table->unique(['owner_id','ip_scan_record_id'], 'uk_owner_ip_scan_record_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('owner_ip_scan_record');
	}

}
