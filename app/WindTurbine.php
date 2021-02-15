<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WindTurbine extends Model
{
    protected $fillable = ['iec_class','model','invest_cost','nominal_power','rotor_diameter','user_id','om_cost_per_year'];
    public function seedDemoData($user){
        $this->create([
            'model' => 'GW82/1500',
            'nominal_power' => 1500,
            'iec_class' => 3,
            'rotor_diameter' => 82,
            'invest_cost' => 2205000,
            'om_cost_per_year' => 66000,
            'user_id' => $user->id
        ]);
        $this->create([
            'model' => 'GW109/2500',
            'nominal_power' => 2500,
            'iec_class' => 2,
            'rotor_diameter' => 109,
            'invest_cost' => 3675000,
            'om_cost_per_year' => 110000,
            'user_id' => $user->id
        ]);
        $this->create([
            'model' => 'GW140/3000',
            'nominal_power' => 3000,
            'iec_class' => 3,
            'rotor_diameter' => 140,
            'invest_cost' => 4410000,
            'om_cost_per_year' => 132000,
            'user_id' => $user->id
        ]);
        $this->create([
            'model' => 'V90/2000',
            'nominal_power' => 2000,
            'iec_class' => 2,
            'rotor_diameter' => 90,
            'invest_cost' => 2940000,
            'om_cost_per_year' => 88000,
            'user_id' => $user->id
        ]);
        $this->create([
            'model' => 'V110/2000',
            'nominal_power' => 2000,
            'iec_class' => 3,
            'rotor_diameter' => 110,
            'invest_cost' => 2940000,
            'om_cost_per_year' => 88000,
            'user_id' => $user->id
        ]);
        $this->create([
            'model' => 'V105/3450',
            'nominal_power' => 3450,
            'iec_class' => 1,
            'rotor_diameter' => 105,
            'invest_cost' => 5071500,
            'om_cost_per_year' => 151800,
            'user_id' => $user->id
        ]);		
        $this->create([
            'model' => 'SG114/2625',
            'nominal_power' => 2625,
            'iec_class' => 1,
            'rotor_diameter' => 114,
            'invest_cost' => 3858750,
            'om_cost_per_year' => 115500,
            'user_id' => $user->id
        ]);
        $this->create([
            'model' => 'SG132/5000',
            'nominal_power' => 5000,
            'iec_class' => 1,
            'rotor_diameter' => 132,
            'invest_cost' => 7350000,
            'om_cost_per_year' => 220000,
            'user_id' => $user->id
        ]);
        $this->create([
            'model' => 'GE100/1700',
            'nominal_power' => 1700,
            'iec_class' => 3,
            'rotor_diameter' => 100,
            'invest_cost' => 2499000,
            'om_cost_per_year' => 74800,
            'user_id' => $user->id
        ]);
        $this->create([
            'model' => 'GE130/3800',
            'nominal_power' => 3800,
            'iec_class' => 2,
            'rotor_diameter' => 130,
            'invest_cost' => 5586000,
            'om_cost_per_year' => 167200,
            'user_id' => $user->id
        ]);
        $this->create([
            'model' => 'E136/4650',
            'nominal_power' => 4650,
            'iec_class' => 1,
            'rotor_diameter' => 136,
            'invest_cost' => 6835500,
            'om_cost_per_year' => 204600,
            'user_id' => $user->id
        ]);
        $this->create([
            'model' => 'E48/800',
            'nominal_power' => 800,
            'iec_class' => 2,
            'rotor_diameter' => 48,
            'invest_cost' => 1176000,
            'om_cost_per_year' => 35200,
            'user_id' => $user->id
        ]);							
    }
}
