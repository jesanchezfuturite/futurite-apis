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
		Schema::create('atc_leads', function(Blueprint $table) {
            $table->id();
			$table->string('contact_id')->unique();
			$table->string('name')->nullable();
			$table->string('email')->nullable();
			$table->string('phone')->nullable();
			$table->string('campaign')->nullable();
			$table->string('utmSource')->nullable();
			$table->string('utmMedium')->nullable();
			$table->string('utmContent')->nullable();
			$table->string('utmTerm')->nullable();
			$table->string('utmKeyword')->nullable();
			$table->string('utmMatchtype')->nullable();
			$table->dateTime('date_created')->nullable();
			$table->longText('fullData')->nullable();
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
		Schema::drop('atc_leads');
	}
};
