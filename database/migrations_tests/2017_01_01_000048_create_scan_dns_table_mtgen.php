<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScanDnsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scan_dns', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('job_id')->nullable();
			$table->bigInteger('watch_id')->nullable()->index('ix_scan_dns_watch_id');
			$table->timestamps();
			$table->dateTime('last_scan_at')->nullable();
			$table->integer('num_scans')->nullable();
			$table->smallInteger('status')->nullable();
			$table->text('dns', 65535)->nullable();
			$table->smallInteger('num_ipv4');
			$table->smallInteger('num_ipv6');
			$table->smallInteger('num_res');
			$table->smallInteger('is_synthetic');
			$table->string('cname')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scan_dns');
	}

}
