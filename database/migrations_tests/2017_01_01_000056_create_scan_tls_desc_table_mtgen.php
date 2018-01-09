<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScanTlsDescTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scan_tls_desc', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('ip_id')->index('ix_scan_tls_desc_ip_id');
			$table->bigInteger('sni_id')->index('ix_scan_tls_desc_sni_id');
			$table->integer('scan_port')->default(443);
			$table->unique(['ip_id','sni_id','scan_port'], 'uk_scan_tls_desc_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scan_tls_desc');
	}

}
