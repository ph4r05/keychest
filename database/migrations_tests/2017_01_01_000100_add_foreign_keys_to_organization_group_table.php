<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToOrganizationGroupTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('organization_group', function(Blueprint $table)
		{
			$table->foreign('parent_group_id', 'organization_group_organization_group_id')->references('id')->on('organization_group')->onUpdate('RESTRICT')->onDelete('SET NULL');
			$table->foreign('organization_id', 'organization_group_organization_id')->references('id')->on('organization')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('organization_group', function(Blueprint $table)
		{
			$table->dropForeign('organization_group_organization_group_id');
			$table->dropForeign('organization_group_organization_id');
		});
	}

}
