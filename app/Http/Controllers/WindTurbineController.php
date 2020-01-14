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
            'invest_cost' => 'required|numeric|min:1|max:1000000',
            'nominal_power' => 'required|numeric|min:1|max:100000000',
            'rotor_diameter' => 'required|numeric|min:1|max:500',
            'iec_class' => 'required|integer|min:1|max:3'
        ]);
        $data['user_id'] = Auth::user()->id;
        $turbine = new WindTurbine($data);
        $turbine->save();

    }
    public function getAll(Request $request){
        $user_id = Auth::user()->id;
        if($request->has('page')){
            $perPage = $request->has('perPage') ? (int) $request->perPage : 3;
            $items = WindTurbine::where('user_id',$user_id);
            $result = $items->paginate($perPage);
            return response()->json($result);
        }else{
            $items = WindTurbine::where('user_id',$user_id)->get();
            return $items;
        }
    }
    public function get($id){
        return WindTurbine::findOrFail($id);
    }
    public function edit($id, Request $request){
        $data = $this->validate($request, [
            'model' => 'required|unique:wind_turbines,model,'.$id.',id,user_id,'.Auth::user()->id,
            'invest_cost' => 'required|numeric|min:1|max:1000000',
            'nominal_power' => 'required|numeric|min:1|max:100000000',
            'rotor_diameter' => 'required|numeric|min:1|max:500',
            'iec_class' => 'required|integer|min:1|max:3'
        ]);
        $turbine = WindTurbine::findOrFail($id);
        $turbine->update($data);
    }
    public function delete($id){
        $turbine = WindTurbine::findOrFail($id);
        $turbine->delete();
    }
}
