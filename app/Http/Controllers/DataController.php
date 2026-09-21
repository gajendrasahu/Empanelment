<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\City;
Use Carbon\Carbon;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;


class DataController extends Controller
{
    public function loadData()
    {
        $city = City::orderBy('City')->get();
        return response()->json($city);
    }

}
