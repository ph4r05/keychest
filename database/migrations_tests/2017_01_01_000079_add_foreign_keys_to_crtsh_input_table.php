<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCrtshInputTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('crtsh_input', function(Blueprint $table)
		{
			$table->foreign('sld_id', 'crtsh_input_base_domain_id')->references('id')->on('base_domain')->onUpdate('RESTRICT')->onDelete('SET NULL');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('crtsh_input', function(Blueprint $table)
		{
			$table->dropForeign('crtsh_input_base_domain_id');
		});
	}

}
