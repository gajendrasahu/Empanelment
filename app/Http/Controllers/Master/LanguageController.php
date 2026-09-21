<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    public function changeLanguage($lang)
    {
		if (in_array($lang, ['en','mr','hn']))
		{
			session(['locale' => $lang]);
			App::setLocale($lang);
		}    
		return redirect()->back();
	}
}