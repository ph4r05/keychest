<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScanDnsEntryTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scan_dns_entry', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('scan_id')->index('ix_scan_dns_entry_scan_id');
			$table->smallInteger('is_ipv6');
			$table->smallInteger('is_internal');
			$table->string('ip', 191)->index('ix_scan_dns_entry_ip');
			$table->smallInteger('res_order');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scan_dns_entry');
	}

}
