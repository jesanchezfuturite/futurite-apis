<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('customers', function(Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('customer_id');
            $table->string('descriptive_name');
            $table->string('client_customer');
            $table->integer('level');
            $table->string('manager');
            $table->string('currency_code');
            $table->string('time_zone');
            $table->boolean('hidden');
            $table->string('resource_name');
            $table->string('test_account');
            $table->string('applied_labels');
            $table->integer('status');
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
		Schema::drop('customers');
	}
};

