<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\WindTurbine;
use Auth;
class WindTurbineController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'model' => 'required|unique:wind_turbines,model,NULL,id,user_id,'.Auth::user()->id,
            'invest_cost' => 'required|numeric',
            'nominal_power' => 'required|numeric',
            'rotor_diameter' => 'required|numeric'
        ]);
        $data['user_id'] = Auth::user()->id;
        $turbine = new WindTurbine($data);
        $turbine->save();

    }
    public function index(Request $request){
        $user_id = Auth::user()->id;
        $perPage = $request->has('perPage') ? (int) $request->perPage : 3;
        $items = WindTurbine::where('user_id',$user_id);
        $result = $items->paginate($perPage);
        return response()->json($result);
    }
    public function get($id){
        return WindTurbine::findOrFail($id);
    }
    public function edit($id, Request $request){
        $data = $this->validate($request, [
            'model' => 'required|unique:wind_turbines,model,'.$id.',id,user_id,'.Auth::user()->id,
            'invest_cost' => 'required|numeric',
            'nominal_power' => 'required|numeric',
            'rotor_diameter' => 'required|numeric'
        ]);
        $turbine = WindTurbine::findOrFail($id);
        $turbine->update($data);
    }
    public function delete($id){
        $turbine = WindTurbine::findOrFail($id);
        $turbine->delete();
    }
}
