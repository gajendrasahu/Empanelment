<?php

	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Route;
	use App\Http\Controllers\WebApiController;


	Route::controller(WebApiController::class)->group( function() {
	
	Route::get('/statelist','stateList')->name('statelist');
	Route::get('/citylist','cityList')->name('citylist');
	Route::get('/arealist','areaList')->name('arealist');
	
	Route::get('/categories','categoryList')->name('categories');
	Route::get('/category','masterCategoryList')->name('category');
	Route::get('/subcategory','subCategoryList')->name('subcategory');
	Route::get('/searchservice','searchService')->name('searchservice');
	Route::get('/getvendorcategory','getVendorCategory')->name('getvendorcategory');


	Route::post('/signup','signUp')->name('signup');
	Route::post('/signupverification','signUpVerification')->name('signupverification');
	
	Route::post('/signin','signInOtp')->name('signin');
	Route::post('/signinverification','signInVerification')->name('signinverification');

	Route::get('/subscribecategory','subscribeCategoryList')->name('subscribecategory');

	Route::post('/vendorsignup','generateVendorOtp')->name('vendorsignup');
	Route::post('/vendorotp','vendorOtpVerification')->name('vendorotp');

	Route::post('/booking','storeBooking')->name('booking');

	//Route::get('/myorder','myOrders')->name('myorder');
	//Route::post('/cancelorder','cancelOrder')->name('cancelorder');
	
	Route::post('/contact','contactForm')->name('contact');
	
	Route::post('/sendotp','sendOTPMessage')->name('sendotp');
	
	Route::post('/vendorsignin','vendorSignInOtp')->name('vendorsignin');
	Route::post('/vendorsigninverification','vendorSignInVerification')->name('vendorsigninverification');
	Route::get('/getemployeeform','getEmployeeForm')->name('getemployeeform');
	Route::post('/registeremployee','registerEmployee')->name('registeremployee');
	Route::get('/employeelist','employeeList')->name('employeelist');
	Route::post('/updateemployee','updateEmployee')->name('updateemployee');
	
	Route::post('/updateemployeeprofile','updateEmployeeProfile')->name('updateemployeeprofile');
	
	// VENDOR PROFILE DATA AND UPDATE
	Route::get('/vendorprofiledetail','getVendorProfile')->name('vendorprofiledetail');
	Route::get('/employeeprofiledetail','getEmployeeProfile')->name('employeeprofiledetail');
	
	Route::post('/updatevendorprofile','updateVendorProfile')->name('updatevendorprofile');
	
	Route::post('/setemployee','setEmployee')->name('setemployee');
	
	Route::put('/changeemployeestatus','setEmployeeStatus')->name('changeemployeestatus');
	
	Route::get('/getslottime','getSlotTime')->name('getslottime');


	Route::get('/getcustomerprofile','getCustomerProfile')->name('getcustomerprofile');
	Route::post('/updatecustomerprofile','updateCustomerProfile')->name('updatecustomerprofile');

	Route::post('/addservicelocation','addServiceLocation')->name('addservicelocation');
	Route::get('/getmyaddress','getMyAddress')->name('getmyaddress');
	
	Route::get('/myservices','myServiceList')->name('myservices');
	
	Route::get('/optionlist','getOptionList')->name('optionlist');
	
	//BOOKING SERVICES RELATED APIS
	Route::post('/addtocart','addToCart')->name('addtocart');
	Route::get('/getcart','getCart')->name('getcart');
	Route::delete('/removefromcart','removeFromCart')->name('removefromcart');
	Route::post('/applycoupon','applyCoupon')->name('applycoupon');
	Route::post('/removecoupon','removeCoupon')->name('removecoupon');

	Route::post('/generateorder','generateOrder')->name('generateorder');
	
	Route::post('/bookservice','bookService')->name('bookservice');
	Route::post('/bookingfailed','bookingFailed')->name('bookingfailed');
	Route::get('/myorders','myOrders')->name('myorders');
	Route::post('/reschedule','rescheduleOrder')->name('reschedule');
	Route::post('/cancelorder','cancelOrder')->name('cancelorder');
	

	Route::get('/myinvoices','myInvoices')->name('myinvoices');

	Route::get('/getreview','getReview')->name('getreview');
	Route::post('/storereview','storeReview')->name('storereview');
	Route::get('/servicephotos','servicePhotos')->name('servicephotos');
	Route::get('/getjobhistory','getJobHistory')->name('getjobhistory');
	
	
	Route::post('/career','storeCareerData')->name('career');
});

