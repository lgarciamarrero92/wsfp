<?php

namespace App\Http\Middleware;

use Closure;
use App;
use Auth;
use Cache;
class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $lang = ['es','en'];
        $locale = 'en';
        if(Auth::user()){
            $locale = Auth::user()->locale;
        }
        else if(session()->has('locale')){
            $locale = session()->get('locale');
        }else{
            if(in_array($request->getLocale(),$lang)){
                $locale = $request->getLocale();
            }
        }
        if($locale != config('app.locale')){
            Cache::forget('lang.js');
        }
        App::setLocale($locale);
        return $next($request);
    }
}
