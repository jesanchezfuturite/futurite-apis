<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('campaigns'); // Asegúrate de eliminar la tabla si ya existe

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('customer_id');
            $table->bigInteger('campaign_id');
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
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
}
