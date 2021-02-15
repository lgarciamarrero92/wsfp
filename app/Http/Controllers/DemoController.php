<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use App\Zone;
use App\WindTurbine;
use App\SolarPanel;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function index()
    {
        if(!Auth::check()){
            $user = new User();
            $user = $user->create(
                [
                    'name' => 'Demo',
                    'email' => 'demo-'.Str::random(10).'@'.Str::random(10).'.com',
                    'password' => bcrypt('@dfp.xzomjix4p'),
                ]
            );
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->details = [
                'demo' => true
            ];
            $user->update();
            $panel = new SolarPanel();
            $panel->seedDemoData($user);
            $turbine = new WindTurbine();
            $turbine->seedDemoData($user);								
            $zone =  new Zone();
            $zone->seedDemoData($user);
            Auth::login($user);
        }
        return redirect('home');
    }
}
