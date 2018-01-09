<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCertificatesTableMtgen extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('certificates', function(Blueprint $table)
		{
			$table->bigInteger('id', true);
			$table->bigInteger('crt_sh_id')->nullable()->index('ix_certificates_crt_sh_id');
			$table->bigInteger('crt_sh_ca_id')->nullable();
			$table->string('fprint_sha1', 40)->index('ix_certificates_fprint_sha1');
			$table->string('fprint_sha256', 64)->nullable()->index('ix_certificates_fprint_sha256');
			$table->dateTime('valid_from')->nullable();
			$table->dateTime('valid_to')->nullable();
			$table->timestamps();
			$table->text('cname', 65535)->nullable();
			$table->text('subject', 65535)->nullable();
			$table->text('issuer', 65535)->nullable();
			$table->smallInteger('is_ca');
			$table->smallInteger('is_self_signed');
			$table->bigInteger('parent_id')->nullable();
			$table->text('alt_names', 65535)->nullable();
			$table->string('source')->nullable();
			$table->text('pem', 65535)->nullable();
			$table->integer('is_le')->default(0);
			$table->integer('is_cloudflare')->default(0);
			$table->smallInteger('is_precert')->default(0);
			$table->smallInteger('is_precert_ca')->default(0);
			$table->integer('key_bit_size')->nullable();
			$table->smallInteger('key_type')->nullable();
			$table->integer('sig_alg')->nullable();
			$table->string('authority_key_info', 64)->nullable()->index('ix_certificates_authority_key_info');
			$table->bigInteger('root_parent_id')->nullable();
			$table->string('subject_key_info', 64)->nullable()->index('ix_certificates_subject_key_info');
			$table->smallInteger('is_alt_wildcard');
			$table->smallInteger('is_cn_wildcard');
			$table->smallInteger('is_ev');
			$table->string('issuer_o', 64)->nullable();
			$table->integer('alt_names_cnt')->nullable();
			$table->smallInteger('is_ov');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('certificates');
	}

}
