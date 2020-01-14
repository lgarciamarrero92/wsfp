<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWindTurbinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wind_turbines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model');
            $table->double('rotor_diameter');
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
        Schema::dropIfExists('wind_turbines');
    }
}
