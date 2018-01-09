<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScanWebAppTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scan_web_app', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('host_id')->nullable()->index('ix_scan_web_app_host_id');
			$table->timestamps();
			$table->dateTime('last_scan_at')->nullable();
			$table->integer('num_scans')->nullable()->default(1);
			$table->string('req_https_result', 64)->nullable();
			$table->string('follow_http_result', 64)->nullable();
			$table->string('follow_https_result', 64)->nullable();
			$table->string('follow_http_url')->nullable();
			$table->string('follow_https_url')->nullable();
			$table->smallInteger('hsts_present')->nullable()->default(0);
			$table->bigInteger('hsts_max_age')->nullable();
			$table->smallInteger('hsts_include_subdomains')->nullable();
			$table->smallInteger('hsts_preload')->nullable();
			$table->smallInteger('pinning_present')->nullable()->default(0);
			$table->smallInteger('pinning_report_only')->nullable();
			$table->text('pinning_pins', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scan_web_app');
	}

}
