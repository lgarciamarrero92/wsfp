<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SolarPanel extends Model
{
    protected $fillable = ['model','invest_cost','nominal_power','width','height','user_id','om_cost_per_year'];
}
