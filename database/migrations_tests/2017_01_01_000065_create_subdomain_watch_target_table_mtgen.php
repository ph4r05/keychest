<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubdomainWatchTargetTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subdomain_watch_target', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('scan_host');
			$table->text('scan_ports', 65535)->nullable();
			$table->bigInteger('top_domain_id')->nullable()->index('ix_subdomain_watch_target_top_domain_id');
			$table->timestamps();
			$table->dateTime('last_scan_at')->nullable();
			$table->smallInteger('last_scan_state')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subdomain_watch_target');
	}

}
