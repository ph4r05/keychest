<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSubdomainWatchResultEntryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('subdomain_watch_result_entry', function(Blueprint $table)
		{
			$table->foreign('service_id', 'subdom_watch_entry_service_id')->references('id')->on('watch_service')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('last_scan_id', 'subdom_watch_entry_subdomain_results_id_first')->references('id')->on('subdomain_results')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('first_scan_id', 'subdom_watch_entry_subdomain_results_id_last')->references('id')->on('subdomain_results')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('watch_id', 'subdom_watch_entry_watch_id')->references('id')->on('subdomain_watch_target')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('subdomain_watch_result_entry', function(Blueprint $table)
		{
			$table->dropForeign('subdom_watch_entry_service_id');
			$table->dropForeign('subdom_watch_entry_subdomain_results_id_first');
			$table->dropForeign('subdom_watch_entry_subdomain_results_id_last');
			$table->dropForeign('subdom_watch_entry_watch_id');
		});
	}

}
