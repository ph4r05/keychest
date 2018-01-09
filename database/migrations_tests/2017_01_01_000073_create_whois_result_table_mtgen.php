<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateWhoisResultTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('whois_result', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('domain_id')->index('ix_whois_result_domain_id');
			$table->smallInteger('status')->nullable();
			$table->string('registrant_cc')->nullable();
			$table->string('registrar')->nullable();
			$table->dateTime('registered_at')->nullable();
			$table->dateTime('expires_at')->nullable();
			$table->smallInteger('dnssec')->nullable();
			$table->dateTime('rec_updated_at')->nullable();
			$table->text('dns', 65535)->nullable();
			$table->text('emails', 65535)->nullable();
			$table->text('aux', 65535)->nullable();
			$table->timestamps();
			$table->dateTime('last_scan_at')->nullable();
			$table->integer('num_scans')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('whois_result');
	}

}
