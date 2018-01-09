<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToWhoisResultTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('whois_result', function(Blueprint $table)
		{
			$table->foreign('domain_id', 'who_base_domain_id')->references('id')->on('base_domain')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('whois_result', function(Blueprint $table)
		{
			$table->dropForeign('who_base_domain_id');
		});
	}

}
