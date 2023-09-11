<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_details', function (Blueprint $table) {
            $table->id();
            $table->json('pickup_address')->nullable();
            $table->json('dropoff_address')->nullable();
            $table->string('pickup_date')->nullable();
            $table->string('pickup_time')->nullable();
            $table->text('detailed_description')->nullable();
            $table->string('number_of_items')->nullable();
            $table->boolean('heavey_weight_items')->default(false);
            $table->enum('pickup_property_type', ['apartment', 'condominium']);
            $table->string('pickup_unit_number')->nullable();
            $table->string('pickup_flight_of_stairs')->nullable();
            $table->boolean('pickup_elevator')->default(false);
            $table->text('pickup_elevator_timing_from')->nullable();
            $table->text('pickup_elevator_timing_to')->nullable();
            $table->enum('dropoff_property_type', ['apartment', 'condominium']);
            $table->integer('dropoff_unit_number')->nullable();
            $table->boolean('dropoff_elevator')->default(false);
            $table->text('dropoff_elevator_timing_from')->nullable();
            $table->text('dropoff_elevator_timing_to')->nullable();
            $table->string('dropoff_flight_of_stairs')->nullable();
            // Add columns for pickup and dropoff latitude and longitude
            $table->json('pickup_latitude')->nullable();
            $table->json('pickup_longitude')->nullable();
            $table->json('dropoff_latitude')->nullable();
            $table->json('dropoff_longitude')->nullable();

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
        Schema::dropIfExists('delivery_details');
    }
};
