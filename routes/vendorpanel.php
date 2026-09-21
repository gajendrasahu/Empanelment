<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Vendor\VendorManagementController;
use App\Http\Controllers\CustomVendorAuthController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\Master\LanguageController;
use Illuminate\Support\Facades\Session;
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

Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'mr', 'hn'])) {  // Allowed languages
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.change');

/*
Route::get('/storage-link',function(){
    $targetFolder = storage_path('app/public');
    $linkFolder = $_SERVER['DOCUMENT_ROOT'].'/puran/storage';
    symlink($targetFolder,$linkFolder);
});
*/
Route::get('/vendorpanel', function () {
    return redirect('vendorlogin');
});

// LOGIN RELATED LINKS
Route::controller(CustomVendorAuthController::class)->group( function() 
{
    Route::get('/vendorpanel','vendorlogin')->name('vendorpanel')->middleware('vendorAlreadyLoggedIn');
    Route::post('/login-vendor','loginVendor')->name('login-vendor');
    Route::get('/vendorpanel/dashboard','dashboard')->middleware('isVendorLoggedIn');
    Route::get('/vendorpanel/dashboard','dashboard')->middleware('isVendorLoggedIn')->name('vendorpanel/dashboard');
    Route::get('/vendorlogout','vendorlogout')->name('vendorlogout');
	Route::get('set-locale/{locale}','setLocale')->name('set-locale');
});

Route::controller(VendorManagementController::class)->group( function() {
   
   Route::get('/edit/profile/{recordid}','editData')->middleware('isVendorLoggedIn')->name('edit.profile');
   Route::post('/update/profile/{recordid}','updateData')->middleware('isVendorLoggedIn')->name('update.profile');

   Route::get('/edit/vendorpassword/{recordid}','editPassword')->middleware('isVendorLoggedIn')->name('edit.vendorpassword');
   Route::post('/update/vendorpassword/{recordid}','updatePassword')->middleware('isVendorLoggedIn')->name('update.vendorpassword');


   Route::get('/vendor/profile/','vendorProfile')->middleware('isVendorLoggedIn')->name('vendor.profile');
   Route::post('/update/vendorprofile/{recordid}','storeVendor')->middleware('isVendorLoggedIn')->name('update.vendorprofile');
   
   Route::get('/vendor/addemployee','addEmployee')->middleware('isVendorLoggedIn')->name('vendor.addemployee');   
   Route::post('/vendor/storeemployee/{recordid}','storeEmployee')->middleware('isVendorLoggedIn')->name('vendor.storeemployee');
   Route::get('/vendoremployee/html','getEmployeeData')->middleware('isVendorLoggedIn')->name('vendoremployee.html');
   Route::get('/edit/vendoremployee/{recordid}','editEmployee')->middleware('isVendorLoggedIn')->name('edit.vendoremployee');
   Route::post('/update/vendoremployee/{recordid}','storeEmployee')->middleware('isVendorLoggedIn')->name('update.vendoremployee');
   Route::get('/delete/vendoremployee/{recordid}','deleteEmployee')->middleware('isVendorLoggedIn')->name('delete.vendoremployee'); 

   Route::get('/employee/profile/','employeeProfile')->middleware('isVendorLoggedIn')->name('employee.profile');   
   Route::get('/vendor/orderlist/','openOrderList')->middleware('isVendorLoggedIn')->name('openorder.list');
   Route::get('/vendor/openorderlist/','getOpenOrderData')->middleware('isVendorLoggedIn')->name('openorder.html');
   
   
});



