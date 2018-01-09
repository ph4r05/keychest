<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateIpAddressTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ip_address', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('ip_addr')->unique('uk_ip_address_ip_addr');
			$table->smallInteger('ip_type')->default(2);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ip_address');
	}

}
