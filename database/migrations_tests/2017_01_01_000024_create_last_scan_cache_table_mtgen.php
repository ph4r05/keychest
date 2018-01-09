<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLastScanCacheTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('last_scan_cache', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->smallInteger('cache_type');
			$table->bigInteger('obj_id')->nullable()->index('ix_last_scan_cache_obj_id');
			$table->integer('scan_type')->index('ix_last_scan_cache_scan_type');
			$table->integer('scan_sub_type');
			$table->string('aux_key', 191);
			$table->bigInteger('scan_id');
			$table->text('scan_aux', 65535)->nullable();
			$table->timestamps();
			$table->unique(['cache_type','obj_id','scan_type','scan_sub_type','aux_key'], 'uq_last_scan_cache_key');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('last_scan_cache');
	}

}
