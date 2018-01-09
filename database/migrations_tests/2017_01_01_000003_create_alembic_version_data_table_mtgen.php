<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAlembicVersionDataTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alembic_version_data', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('schema_ver')->nullable();
			$table->bigInteger('data_ver')->nullable();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('alembic_version_data');
	}

}
