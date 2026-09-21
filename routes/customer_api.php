<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Helpers\JWTAuthHelper;
use App\Http\Controllers\CustomerApiController;

// DATA CONTROLLER ALL ROUTE

Route::controller(CustomerApiController::class)->group( function() 
{
	
	Route::get('/statelist','stateList')->name('statelist');
	Route::get('/citylist','cityList')->name('citylist');
	Route::get('/arealist','areaList')->name('arealist');
	
	Route::get('/categories','categoryList')->name('categories');
	Route::get('/category','masterCategoryList')->name('category');
	Route::get('/subcategory','subCategoryList')->name('subcategory');


	Route::post('/master/add/customer','addCustomer')->middleware('jwt.auth')->name('add.customer');   

	Route::post('/customersignup','generateCustomerOtp')->name('customersignup');
	Route::post('/signupverification','customerOtpVerification')->name('signupverification');
	//Route::post('/basicdetail','updateBasicDetail')->name('basicdetail');
	
	Route::post('/basicdetail','updateBasicDetail')->middleware('jwt.validate')->name('basicdetail');
	

	Route::post('/customersignin','customerSignInOtp')->name('customersignin');
	Route::post('/signinverification','customerSignInVerification')->name('signinverification');
	
	Route::get('/getcustomerprofile','getCustomerProfile')->middleware('jwt.validate')->name('getcustomerprofile');
	Route::post('/updatecustomerprofile','updateCustomerProfile')->middleware('jwt.validate')->name('updatecustomerprofile');
	
	Route::get('/getcategory','getCategoryList')->name('getcategory');
	Route::get('/getsubcategory','getSubCategoryList')->name('getsubcategory');
	Route::get('/getservices','getServiceList')->name('getservices');
	
	//Route::get('/getservices','getServiceList')->name('getservices');
	
	Route::get('/getmyaddress','getMyAddress')->middleware('jwt.validate')->name('getmyaddress');
	Route::post('/addservicelocation','addServiceLocation')->middleware('jwt.validate')->name('addservicelocation');
	
	Route::get('/getslottime','getSlotTime')->middleware('jwt.validate')->name('getslottime');

	//Route::post('/bookservice','bookService')->name('bookservice');

	Route::get('/myservices','myServiceList')->name('myservices');
	
	Route::get('/getvendors','getVendors')->name('getvendors');
	
	Route::get('/optionlist','getOptionList')->middleware('jwt.validate')->name('optionlist');
	
	Route::post('/addtocart','addToCart')->middleware('jwt.validate')->name('addtocart');
	Route::get('/getcart','getCart')->middleware('jwt.validate')->name('getcart');
	Route::delete('/removefromcart','removeFromCart')->middleware('jwt.validate')->name('removefromcart');
	Route::post('/applycoupon','applyCoupon')->middleware('jwt.validate')->name('applycoupon');
	
	Route::post('/generateorder','generateOrder')->middleware('jwt.validate')->name('generateorder');
	Route::post('/bookservice','bookService')->middleware('jwt.validate')->name('bookservice');
	Route::post('/bookingfailed','bookingFailed')->middleware('jwt.validate')->name('bookingfailed');
});

Route::fallback(function () {
    return response()->json([
        'message' => 'THE API END POINT YOU ARE LOOKING FOR DOES NOT EXIST.',
        'status' => 404
    ], 404);
});
