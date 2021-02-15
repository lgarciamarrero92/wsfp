<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SolarPanel extends Model
{
    protected $fillable = ['model','invest_cost','nominal_power','width','height','user_id','om_cost_per_year'];
    public function seedDemoData($user){
        $user->solar_panels()->createMany([            
          [
            "model"=> "JKM245P",
            "nominal_power"=> 245,
            "height"=> 1.65,
            "width"=> 0.992,
            "invest_cost"=> 259.7,
            "om_cost_per_year"=> 3.185
          ],
          [
            "model"=> "JKM265P",
            "nominal_power"=> 265,
            "height"=> 1.65,
            "width"=> 0.992,
            "invest_cost"=> 280.9,
            "om_cost_per_year"=> 3.445
          ],
          [
            "model"=> "JKM315P",
            "nominal_power"=> 315,
            "height"=> 1.956,
            "width"=> 0.992,
            "invest_cost"=> 333.9,
            "om_cost_per_year"=> 4.095
          ],
          [
            "model"=> "JAP72S09-325/SC",
            "nominal_power"=> 325,
            "height"=> 1.979,
            "width"=> 0.996,
            "invest_cost"=> 344.5,
            "om_cost_per_year"=> 4.225
          ],
          [
            "model"=> "JAP72S09-330/SC",
            "nominal_power"=> 330,
            "height"=> 1.979,
            "width"=> 0.996,
            "invest_cost"=> 349.8,
            "om_cost_per_year"=> 4.29
          ],
          [
            "model"=> "JAP72S09-345/SC",
            "nominal_power"=> 345,
            "height"=> 1.979,
            "width"=> 0.996,
            "invest_cost"=> 365.7,
            "om_cost_per_year"=> 4.485
          ],
          [
            "model"=> "TSM-355PE15H",
            "nominal_power"=> 355,
            "height"=> 2.024,
            "width"=> 1.004,
            "invest_cost"=> 376.3,
            "om_cost_per_year"=> 4.615
          ],
          [
            "model"=> "TSM-285 PE06H",
            "nominal_power"=> 285,
            "height"=> 1.698,
            "width"=> 1.004,
            "invest_cost"=> 302.1,
            "om_cost_per_year"=> 3.705
          ],
          [
            "model"=> "TSM-300 PE06H",
            "nominal_power"=> 300,
            "height"=> 1.698,
            "width"=> 1.004,
            "invest_cost"=> 318,
            "om_cost_per_year"=> 3.9
          ],
          [
            "model"=> "LR4-72HBD-435M",
            "nominal_power"=> 435,
            "height"=> 2.131,
            "width"=> 1.052,
            "invest_cost"=> 461.1,
            "om_cost_per_year"=> 5.655
          ],
          [
            "model"=> "LR4-72HBD-415M",
            "nominal_power"=> 415,
            "height"=> 2.131,
            "width"=> 1.052,
            "invest_cost"=> 439.9,
            "om_cost_per_year"=> 5.395
          ],
          [
            "model"=> "LR6-72HBD-385M",
            "nominal_power"=> 385,
            "height"=> 2.02,
            "width"=> 0.996,
            "invest_cost"=> 408.1,
            "om_cost_per_year"=> 5.005
          ]
        ]);
    }
}
