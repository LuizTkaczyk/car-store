<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle', function (Blueprint $table) {
            $table->id();
            $table->string('model');

            $table->unsignedBigInteger('brand_id');
            $table->foreign('brand_id')->references('id')->on('brand')->onDelete('restrict');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('category')->onDelete('restrict');

            $table->string('year');
            $table->float('price');
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
        Schema::dropIfExists('vehicle');
    }
}
