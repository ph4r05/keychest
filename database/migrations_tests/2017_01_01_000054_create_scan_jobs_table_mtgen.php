<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScanJobsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scan_jobs', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->char('uuid', 36)->unique();
			$table->string('scan_host', 191)->nullable();
			$table->string('scan_scheme', 191)->nullable();
			$table->string('scan_port', 191)->nullable();
			$table->timestamps();
			$table->string('state', 191)->nullable();
			$table->string('progress', 191)->nullable();
			$table->integer('user_id')->unsigned()->nullable();
			$table->string('user_ip', 191)->nullable();
			$table->string('user_sess', 191)->nullable();
			$table->bigInteger('crtsh_check_id')->nullable()->index('ix_scan_jobs_crtsh_check_id');
			$table->string('crtsh_checks')->nullable();
			$table->bigInteger('dns_check_id')->nullable()->index('ix_scan_jobs_dns_check_id');
			$table->bigInteger('whois_check_id')->nullable()->index('ix_scan_jobs_whois_check_id');
			$table->string('scan_ip')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scan_jobs');
	}

}
