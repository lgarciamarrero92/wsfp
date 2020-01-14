<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSolarPanelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solar_panels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model');
            $table->double('height');
            $table->double('width');
            $table->double('invest_cost');
            $table->double('nominal_power');
            $table->integer('user_id');
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
        Schema::dropIfExists('solar_panels');
    }
}
