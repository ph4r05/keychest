<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToScanTlsDescTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scan_tls_desc', function(Blueprint $table)
		{
			$table->foreign('ip_id', 'fk_scan_tls_desc_ip_address_id')->references('id')->on('ip_address')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('sni_id', 'fk_scan_tls_desc_watch_service_id')->references('id')->on('watch_service')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scan_tls_desc', function(Blueprint $table)
		{
			$table->dropForeign('fk_scan_tls_desc_ip_address_id');
			$table->dropForeign('fk_scan_tls_desc_watch_service_id');
		});
	}

}
