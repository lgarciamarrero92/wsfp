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
//Localization
Route::get('/js/lang.js', function () {
    $strings = Cache::rememberForever('lang.js', function(){
        $lang = config('app.locale');
        if($lang == 'en')return '';
        $path = resource_path('lang/'.$lang.'.json');
        $json = json_decode(file_get_contents($path),true);
        return $json;
    });
    header('Content-Type: text/javascript');
    echo('window.i18n = '.json_encode($strings).';');
    exit();
});
Route::get('/translate/{locale}','TranslateController@set')->name('translate');
//
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/demo', 'DemoController@index')->name('demo');
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
 //Zones
 Route::get('/zones','ZoneController@getAll');
 Route::get('/zones/{id}','ZoneController@get');
 Route::post('/zones','ZoneController@store');
 Route::put('/zones/{id}','ZoneController@edit');
 Route::delete('/zones/{id}','ZoneController@delete');