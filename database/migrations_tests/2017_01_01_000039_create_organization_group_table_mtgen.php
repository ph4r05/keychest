<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrganizationGroupTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('organization_group', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->string('name', 191);
			$table->bigInteger('organization_id')->index('ix_organization_group_organization_id');
			$table->bigInteger('parent_group_id')->nullable()->index('ix_organization_group_parent_group_id');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('organization_group');
	}

}
