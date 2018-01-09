<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateScanSubTlsTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scan_sub_tls', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('job_id')->nullable();
			$table->bigInteger('watch_id')->nullable()->index('ix_scan_sub_tls_watch_id');
			$table->bigInteger('parent_scan_id')->nullable()->index('ix_scan_sub_tls_parent_scan_id');
			$table->string('ip_scanned')->nullable();
			$table->smallInteger('is_ipv6');
			$table->string('tls_ver', 16)->nullable();
			$table->smallInteger('key_type')->nullable();
			$table->bigInteger('cipersuite_set')->nullable();
			$table->timestamps();
			$table->dateTime('last_scan_at')->nullable();
			$table->integer('num_scans')->nullable();
			$table->smallInteger('status')->nullable();
			$table->smallInteger('err_code')->nullable();
			$table->integer('tls_alert_code')->nullable();
			$table->integer('time_elapsed')->nullable();
			$table->integer('results')->nullable();
			$table->integer('new_results')->nullable();
			$table->text('certs_ids', 65535)->nullable();
			$table->bigInteger('cert_id_leaf')->nullable();
			$table->smallInteger('valid_path')->nullable();
			$table->smallInteger('valid_hostname')->nullable();
			$table->string('err_validity', 64)->nullable();
			$table->smallInteger('err_many_leafs')->nullable();
			$table->integer('err_valid_ossl_code')->nullable();
			$table->integer('err_valid_ossl_depth')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('scan_sub_tls');
	}

}
