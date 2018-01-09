<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToWatchTargetTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('watch_target', function(Blueprint $table)
		{
			$table->foreign('top_domain_id', 'wt_base_domain_id')->references('id')->on('base_domain')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('ip_scan_id', 'wt_ip_scan_record_id')->references('id')->on('ip_scan_record')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('agent_id', 'wt_keychest_agent_id')->references('id')->on('keychest_agent')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('last_dns_scan_id', 'wt_scan_dns_id')->references('id')->on('scan_dns')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('service_id', 'wt_watch_service_id')->references('id')->on('watch_service')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('watch_target', function(Blueprint $table)
		{
			$table->dropForeign('wt_base_domain_id');
			$table->dropForeign('wt_ip_scan_record_id');
			$table->dropForeign('wt_keychest_agent_id');
			$table->dropForeign('wt_scan_dns_id');
			$table->dropForeign('wt_watch_service_id');
		});
	}

}
