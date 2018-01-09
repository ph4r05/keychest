<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDomainNameTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('domain_name', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('domain_name')->unique('uk_domain_name_domain_name');
			$table->bigInteger('top_domain_id')->nullable()->index('ix_domain_name_top_domain_id');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('domain_name');
	}

}
