<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToCrtshQueryResultsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('crtsh_query_results', function(Blueprint $table)
		{
			$table->foreign('query_id', 'fk_crtsh_query_results_crtsh_query_id')->references('id')->on('crtsh_query')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('crtsh_query_results', function(Blueprint $table)
		{
			$table->dropForeign('fk_crtsh_query_results_crtsh_query_id');
		});
	}

}
