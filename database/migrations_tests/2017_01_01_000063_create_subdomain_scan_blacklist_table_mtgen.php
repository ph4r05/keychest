<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubdomainScanBlacklistTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('subdomain_scan_blacklist', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('rule');
			$table->smallInteger('rule_type')->nullable();
			$table->smallInteger('detection_code')->nullable();
			$table->integer('detection_value')->nullable();
			$table->dateTime('detection_first_at')->nullable();
			$table->dateTime('detection_last_at')->nullable();
			$table->integer('detection_num')->nullable();
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('subdomain_scan_blacklist');
	}

}
