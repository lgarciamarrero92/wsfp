<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Zone;
use Auth;
class ZoneController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'feature' => 'required',
            'name' => 'required',
            'type' => 'required',
            'area' => 'tooBigForSolarFarm:'.$request->type
        ]);
        $data['user_id'] = Auth::user()->id;
        $zone = new Zone($data);
        $zone->save();
        return $zone->id;
    }
    public function getAll(Request $request){
        $user_id = Auth::user()->id;
        if($request->has('page')){
            $perPage = $request->has('perPage') ? (int) $request->perPage : 3;
            $items = Zone::where('user_id',$user_id);
            $result = $items->paginate($perPage);
            return response()->json($result);
        }else{
            return Zone::where('user_id',$user_id)->get();
        }
    }
    public function get($id){
        return Zone::findOrFail($id);
    }
    public function edit($id, Request $request){
        $zone = Zone::findOrFail($id);
        if(!$request['type']){
            $request['type'] = $zone->type;
        }
        $data = $this->validate($request, [
            'name' => 'sometimes|required',
            'type' => 'sometimes|required',
            'feature' => 'sometimes|required',
            'area' => 'sometimes|tooBigForSolarFarm:'.$request->type
        ]);
        $zone->update($data);
        return $zone->id;
    }
    public function delete($id){
        $zone = Zone::findOrFail($id);
        $zone->delete();
    }
}
