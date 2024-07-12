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
		Schema::create('ads_indicators_clients', function(Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('customer_id');
            $table->bigInteger('campaign_id');
            $table->bigInteger('client_id');
            $table->string('impressions');
            $table->string('impressions_month');
            $table->string('impressions_last_month');
            $table->string('clics');
            $table->string('clics_month');
            $table->string('clics_last_month');
            $table->string('conversion');
            $table->string('conversion_month');
            $table->string('conversion_last_month');
            $table->string('paid');
            $table->string('paid_month');
            $table->string('paid_last_month');
            $table->string('budget');
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
		Schema::drop('indicatorsadsclients');
	}
};
