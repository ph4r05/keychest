<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScanJobsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scan_jobs', function(Blueprint $table)
		{
			$table->foreign('crtsh_check_id', 'sjob_crtsh_query_id')->references('id')->on('crtsh_query')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('dns_check_id', 'sjob_scan_dns_id')->references('id')->on('scan_dns')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('whois_check_id', 'sjob_whois_result_id')->references('id')->on('whois_result')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scan_jobs', function(Blueprint $table)
		{
			$table->dropForeign('sjob_crtsh_query_id');
			$table->dropForeign('sjob_scan_dns_id');
			$table->dropForeign('sjob_whois_result_id');
		});
	}

}
