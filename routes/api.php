<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::any('{any}', function () {
    return response()->json(['error' => 'API access disabled'], 403);
})->where('any', '.*');