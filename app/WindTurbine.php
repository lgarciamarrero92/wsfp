<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WindTurbine extends Model
{
    protected $fillable = ['iec_class','model','invest_cost','nominal_power','rotor_diameter','user_id','om_cost_per_year'];
}
