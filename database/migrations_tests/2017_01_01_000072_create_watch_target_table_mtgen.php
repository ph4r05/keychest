<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWatchTargetTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('watch_target', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('scan_host');
			$table->string('scan_scheme')->nullable();
			$table->string('scan_port')->nullable();
			$table->smallInteger('scan_connect')->default(0);
			$table->timestamps();
			$table->dateTime('last_scan_at')->nullable();
			$table->smallInteger('last_scan_state')->default(0);
			$table->bigInteger('top_domain_id')->nullable()->index('ix_watch_target_top_domain_id');
			$table->bigInteger('agent_id')->nullable()->index('ix_watch_target_agent_id');
			$table->bigInteger('service_id')->nullable()->index('ix_watch_target_service_id');
			$table->bigInteger('last_dns_scan_id')->nullable()->index('ix_watch_target_last_dns_scan_id');
			$table->smallInteger('is_ip_host')->default(0);
			$table->bigInteger('ip_scan_id')->nullable()->index('ix_watch_target_ip_scan_id');
			$table->smallInteger('manual_dns')->default(0);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('watch_target');
	}

}
