<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScanHandshakesTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scan_handshakes', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('job_id')->nullable();
			$table->string('ip_scanned')->nullable();
			$table->timestamps();
			$table->smallInteger('status')->nullable();
			$table->integer('time_elapsed')->nullable();
			$table->integer('results')->nullable();
			$table->integer('new_results')->nullable();
			$table->text('certs_ids', 65535)->nullable();
			$table->bigInteger('cert_id_leaf')->nullable();
			$table->smallInteger('valid_path')->nullable();
			$table->integer('err_code')->nullable();
			$table->string('tls_ver', 16)->nullable();
			$table->string('err_validity', 64)->nullable();
			$table->integer('err_many_leafs')->nullable()->default(0);
			$table->integer('valid_hostname')->nullable()->default(0);
			$table->string('req_https_result', 64)->nullable();
			$table->string('follow_http_result', 64)->nullable();
			$table->string('follow_https_result', 64)->nullable();
			$table->string('follow_http_url')->nullable();
			$table->string('follow_https_url')->nullable();
			$table->integer('hsts_present')->nullable()->default(0);
			$table->bigInteger('hsts_max_age')->nullable();
			$table->smallInteger('hsts_include_subdomains')->nullable();
			$table->smallInteger('hsts_preload')->nullable();
			$table->smallInteger('pinning_present')->nullable()->default(0);
			$table->smallInteger('pinning_report_only')->nullable();
			$table->text('pinning_pins', 65535)->nullable();
			$table->dateTime('last_scan_at')->nullable();
			$table->integer('num_scans')->nullable();
			$table->bigInteger('watch_id')->nullable()->index('ix_scan_handshakes_watch_id');
			$table->integer('tls_alert_code')->nullable();
			$table->integer('err_valid_ossl_code')->nullable();
			$table->integer('err_valid_ossl_depth')->nullable();
			$table->smallInteger('is_ipv6');
			$table->bigInteger('sub_ecc')->nullable()->index('ix_scan_handshakes_sub_ecc');
			$table->bigInteger('sub_rsa')->nullable()->index('ix_scan_handshakes_sub_rsa');
			$table->string('cdn_cname')->nullable();
			$table->string('cdn_headers')->nullable();
			$table->string('cdn_reverse')->nullable();
			$table->string('ip_scanned_reverse')->nullable();
			$table->bigInteger('test_id')->nullable()->index('ix_scan_handshakes_test_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scan_handshakes');
	}

}
