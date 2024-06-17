<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::create('campaigns', function(Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('customer_id');
            $table->bigInteger('campaign_id')->unique();
            $table->string('name');
            $table->string('status');
            $table->string('serving_status');
            $table->string('advertising_channel_type');
            $table->string('advertising_channel_sub_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('bidding_strategy_type');
            $table->string('campaign_budget');
            $table->text('labels')->nullable();
            $table->text('tracking_url_template')->nullable();
            $table->text('final_url_suffix')->nullable();
            $table->text('frequency_caps')->nullable();
            $table->string('video_brand_safety_suitability')->nullable();
            $table->string('experiment_type')->nullable();
            $table->float('optimization_score')->nullable();
            $table->timestamps();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
