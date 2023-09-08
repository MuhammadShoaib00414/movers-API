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
        Schema::create('delivery_item_pictures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_detail_id');
            $table->foreign('delivery_detail_id')->references('id')->on('delivery_details')->onDelete('cascade');
            $table->string('item_picture_path'); 
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
        //
    }
};
