<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VendorApiController;

// DATA CONTROLLER ALL ROUTE


Route::controller(VendorApiController::class)->group( function()
{
	Route::get('/statelist','stateList')->name('statelist');
	Route::get('/citylist','cityList')->name('citylist');
	Route::get('/arealist','areaList')->name('arealist');

	Route::post('/sendotp','sendOTPMessage')->name('sendotp');
	
	Route::post('/categories','categoryList')->name('categories');
	Route::get('/category','masterCategoryList')->name('category');
	Route::get('/subcategory','subCategoryList')->name('subcategory');
	Route::get('/getvendorcategory','getVendorCategory')->name('getvendorcategory');


	Route::post('/vendorsignup','generateVendorOtp')->name('vendorsignup');
	Route::post('/signupverification','vendorOtpVerification')->name('signupverification');
	Route::post('/basicdetail','updateBasicDetail')->name('basicdetail');
	Route::post('/gotodashboard','goToDashboard')->name('gotodashboard');
	Route::get('/getauthstatus','getAuthStatus')->name('getauthstatus');
	

	Route::post('/vendorsignin','vendorSignInOtp')->name('vendorsignin');
	Route::post('/vendorsigninverification','vendorSignInVerification')->name('vendorsigninverification');
	
	Route::get('/getprofile','getProfile')->name('getprofile');
	Route::post('/updatevendorprofile','updateVendorProfile')->name('updatevendorprofile');
	
	Route::post('/registeremployee','registerEmployee')->name('registeremployee');

	
	Route::get('/myemployees','getMyEmployeeList')->name('myemployees');
	Route::post('/setemployeestatus','setEmployeeStatus')->name('setemployeestatus');
	Route::post('/updatemyemployee','updateMyEmployee')->name('updatemyemployee');
	
	
	Route::post('/startwork','startWork')->name('startwork');
	Route::post('/addonprice','addOrderPrice')->name('addonprice');
	
	Route::post('/generateorderid','generateRecharge')->name('generateorderid');

	Route::post('/verifyrecharge','verifyRecharge')->name('verifyrecharge');

	Route::post('/failedrecharge','failedRecharge')->name('failedrecharge');

	Route::get('/rechargelist','rechargeList')->name('rechargelist');
	Route::get('/walletbalance','walletBalance')->name('walletbalance');
	
	
	Route::get('/setjobrequest','setJobRequest')->name('setjobrequest');
	
	Route::get('/availablejobs','availableVendorJob')->name('availablejobs');
	Route::get('/getemployee','getEmployee')->name('getemployee');
	Route::post('/acceptservice','acceptService')->name('acceptservice');
	Route::post('/rejectservice','rejectService')->name('rejectservice');
	Route::get('/bookinghistory','bookingHistory')->name('bookinghistory');
	Route::get('/getbookingdetail','getBookingDetail')->name('getbookingdetail');
	
	Route::get('/getjobhistory','getJobHistory')->name('getjobhistory');
	
	Route::get('/getbookingphotos','getBookingPicture')->name('getbookingphotos');
	Route::post('/startpin','startPin')->name('startpin');
	Route::post('/endpin','endPin')->name('endpin');
	Route::get('/getpicture','getPicture')->name('getpicture');
	Route::post('/takepicture','takePicture')->name('takepicture');
	Route::get('/getservices','getServices')->name('getservices');
	//Route::get('/getoptions','getOptionList')->name('getoptions');
	Route::post('/addonservice','addOnService')->name('addonservice');
	Route::post('/sendtohold','sendToHold')->name('sendtohold');
	Route::post('/backtowork','backToWork')->name('backtowork');
	Route::get('/interchangeemployee','interchangeEmployee')->name('interchangeemployee');
	Route::post('/confirminterchange','confirmInterchange')->name('confirminterchange');
	
	Route::get('/pendingpayments','getPendingPaymentServices')->name('pendingpayments');
	Route::post('/initiatepayment','initiatePayment')->name('initiatepayment');
	
	Route::post('/unpaidpayment/webhook','handleWebhook');
	
	Route::get('/payment/callback','handleCallback');
	
	Route::post('/createorderid','createOrder')->name('createorderid');
	Route::post('/makepayment','makePayment')->name('makepayment');
	Route::post('/makepaymentfailed','makePaymentFailed')->name('makepaymentfailed');
	Route::post('/sendpaymentlink','generatePaymentLink')->name('sendpaymentlink');
	Route::get('/paymentlinkcallback','handlePaymentLinkCallback')->name('paymentlinkcallback');
	
	Route::post('/testpdf','testPDF')->name('testpdf');
	
	Route::get('/getdashboard','getDashBoard')->name('getdashboard');
	Route::get('/dailyattendance','dailyAttendance')->name('dailyattendance');
	Route::get('/getloginoptions','getLoginOption')->name('getloginoptions');
	Route::post('/setavailability','setAvailability')->name('setavailability');
	Route::post('/updatelocation','updateLocation')->name('updatelocation');
	
	Route::post('/testnotification','testNotification')->name('testnotification');
	Route::get('/mynotifications','myNotifications')->name('mynotifications');
	Route::get('/acceptduration','acceptDuration')->name('acceptduration');
	
	Route::get('/earninghistory','earningHistory')->name('earninghistory');
	
	Route::post('/copydata','copyData')->name('copydata');
	Route::get('/contactdetail','contactDetail')->name('contactdetail');
	
});



