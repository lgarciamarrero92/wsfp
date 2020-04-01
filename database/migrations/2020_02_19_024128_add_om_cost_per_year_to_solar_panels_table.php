<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOmCostPerYearToSolarPanelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('solar_panels', function (Blueprint $table) {
            $table->double('om_cost_per_year');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('solar_panels', function (Blueprint $table) {
            $table->dropColumn(['om_cost_per_year']);
        });
    }
}
