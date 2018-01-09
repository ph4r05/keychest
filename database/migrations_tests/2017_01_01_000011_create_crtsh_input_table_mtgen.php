<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCrtshInputTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('crtsh_input', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('sld_id')->nullable()->index('ix_crtsh_input_sld_id');
			$table->string('iquery');
			$table->smallInteger('itype')->nullable();
			$table->dateTime('created_at')->nullable();
			$table->unique(['iquery','itype'], 'crtsh_input_key_unique');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('crtsh_input');
	}

}
