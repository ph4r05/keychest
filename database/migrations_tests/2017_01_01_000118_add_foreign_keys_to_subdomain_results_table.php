<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSubdomainResultsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('subdomain_results', function(Blueprint $table)
		{
			$table->foreign('watch_id', 'wa_sub_res_watch_target_id')->references('id')->on('subdomain_watch_target')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('subdomain_results', function(Blueprint $table)
		{
			$table->dropForeign('wa_sub_res_watch_target_id');
		});
	}

}
