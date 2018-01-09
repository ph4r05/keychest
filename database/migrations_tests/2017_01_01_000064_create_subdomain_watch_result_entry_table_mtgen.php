<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubdomainWatchResultEntryTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subdomain_watch_result_entry', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('watch_id')->index('ix_subdomain_watch_result_entry_watch_id');
			$table->smallInteger('is_wildcard');
			$table->smallInteger('is_internal');
			$table->smallInteger('res_order');
			$table->timestamps();
			$table->dateTime('last_scan_at')->nullable();
			$table->integer('num_scans');
			$table->bigInteger('last_scan_id')->nullable()->index('ix_subdomain_watch_result_entry_last_scan_id');
			$table->bigInteger('first_scan_id')->nullable()->index('ix_subdomain_watch_result_entry_first_scan_id');
			$table->bigInteger('service_id')->nullable()->index('ix_subdomain_watch_result_entry_service_id');
			$table->string('name', 191)->index('ix_subdomain_watch_result_entry_name');
			$table->smallInteger('is_long');
			$table->text('name_full', 65535)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subdomain_watch_result_entry');
	}

}
