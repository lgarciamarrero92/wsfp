<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOmCostPerYearToWindTurbinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wind_turbines', function (Blueprint $table) {
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
        Schema::table('wind_turbines', function (Blueprint $table) {
            $table->dropColumn(['om_cost_per_year']);
        });
    }
}
