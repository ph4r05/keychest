<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToManagedSolutionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('managed_solutions', function(Blueprint $table)
		{
			$table->foreign('owner_id', 'managed_solutions_owner_id')->references('id')->on('owners')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('managed_solutions', function(Blueprint $table)
		{
			$table->dropForeign('managed_solutions_owner_id');
		});
	}

}
