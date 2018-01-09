<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCertificatePriceListTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('certificate_price_list', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('issuer_org')->nullable();
			$table->float('price', 10, 0);
			$table->float('price_personal', 10, 0)->nullable();
			$table->float('price_ev', 10, 0)->nullable();
			$table->float('price_wildcard', 10, 0)->nullable();
			$table->float('price_ev_wildcard', 10, 0)->nullable();
			$table->timestamps();
			$table->float('price_ov', 10, 0)->nullable();
			$table->float('price_ov_wildcard', 10, 0)->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('certificate_price_list');
	}

}
