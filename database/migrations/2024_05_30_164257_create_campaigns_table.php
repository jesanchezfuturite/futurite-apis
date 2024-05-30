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
		Schema::create('campaigns', function(Blueprint $table) {
            $table->id();
            $table->string('campaign_id');
            $table->string('name');
            $table->string('status');
            $table->integer('clicks');
            $table->integer('impressions');
            $table->decimal('ctr', 5, 2);
            $table->decimal('average_cpc', 10, 2);
            $table->decimal('cost_micros', 15, 2);
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
		Schema::drop('campaigns');
	}
};
