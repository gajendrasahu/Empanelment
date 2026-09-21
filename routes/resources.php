<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\MasterController;
use App\Http\Controllers\Resources\ResourcesController;

use App\Http\Controllers\Master\LanguageController;
use Illuminate\Support\Facades\Session;

Route::middleware(['isLoggedIn','check.method','role:RESOURCE'])->controller(ResourcesController::class)->group(function()
{
	Route::get('/master/monthly/attendance','monthlyAttendance')->name('monthly.attendance');
	Route::get('/master/monthly/mpr','mprMonths')->name('monthly.mpr');
	Route::post('/master/monthlyreport/html','monthlyProgressReport')->name('monthlyreport.html');
	
});
