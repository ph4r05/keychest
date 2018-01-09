<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCrtshQueryTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('crtsh_query', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('job_id')->nullable();
			$table->timestamps();
			$table->smallInteger('status')->nullable();
			$table->integer('results')->nullable();
			$table->integer('new_results')->nullable();
			$table->text('certs_ids', 65535)->nullable();
			$table->dateTime('last_scan_at')->nullable();
			$table->integer('num_scans')->nullable();
			$table->bigInteger('watch_id')->nullable()->index('ix_crtsh_query_watch_id');
			$table->text('certs_sh_ids', 65535)->nullable();
			$table->bigInteger('input_id')->nullable()->index('ix_crtsh_query_input_id');
			$table->bigInteger('newest_cert_id')->nullable();
			$table->bigInteger('newest_cert_sh_id')->nullable();
			$table->bigInteger('sub_watch_id')->nullable()->index('ix_crtsh_query_sub_watch_id');
			$table->bigInteger('service_id')->nullable()->index('ix_crtsh_query_service_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('crtsh_query');
	}

}
