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
 Route::get('/solar_panels','SolarPanelController@getAll');
 Route::get('/solar_panels/{id}','SolarPanelController@get');
 Route::post('/solar_panels','SolarPanelController@store');
 Route::put('/solar_panels/{id}','SolarPanelController@edit');
 Route::delete('/solar_panels/{id}','SolarPanelController@delete');
//Wind turbines
 Route::get('/wind_turbines','WindTurbineController@getAll');
 Route::get('/wind_turbines/{id}','WindTurbineController@get');
 Route::post('/wind_turbines','WindTurbineController@store');
 Route::put('/wind_turbines/{id}','WindTurbineController@edit');
 Route::delete('/wind_turbines/{id}','WindTurbineController@delete');