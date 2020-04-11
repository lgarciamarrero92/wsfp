<?php

namespace App\Http\Controllers;
use Cache;
use Auth;
use User;
use Illuminate\Http\Request;

class TranslateController extends Controller
{
    public function set(Request $request, $locale)
    {
        $langs = ['de','en','es','fr'];
        if(Auth::user()){
            Auth::user()->locale = $locale;
            Auth::user()->update();
        }else{
            session()->put('locale', $locale);
        }
        return redirect()->back();
    }
}
