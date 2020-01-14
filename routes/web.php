<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes(['verify' => true]);
Route::get('/', function () {
    //return view('welcome');
    return redirect('/home');
});

Route::get('/home', 'HomeController@index')->name('home');
 //Api
 //Solar panels
 Route::get('/solar_panel','SolarPanelController@getAll');
 Route::get('/solar_panel/{id}','SolarPanelController@get');
 Route::post('/solar_panel','SolarPanelController@store');
 Route::put('/solar_panel/{id}','SolarPanelController@edit');
 Route::delete('/solar_panel/{id}','SolarPanelController@delete');
//Wind turbines
 Route::get('/wind_turbine','WindTurbineController@index');
 Route::get('/wind_turbine/{id}','WindTurbineController@get');
 Route::post('/wind_turbine','WindTurbineController@store');
 Route::put('/wind_turbine/{id}','WindTurbineController@edit');
 Route::delete('/wind_turbine/{id}','WindTurbineController@delete');