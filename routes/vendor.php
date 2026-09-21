<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Vendors\VendorManagementController;
use App\Http\Controllers\Vendors\InvoiceController;
use App\Http\Controllers\Vendors\PreBidQueryController;
use App\Http\Controllers\Vendors\VendorUsersController;
use App\Http\Controllers\Vendors\VendorMprController;

use App\Http\Controllers\Master\LanguageController;
use Illuminate\Support\Facades\Session;

Route::middleware(['isLoggedIn','check.method', 'role:VENDOR'])->controller(VendorManagementController::class)->group(function()
{
   Route::get('/master/floated/eois','floatedEoiList')->name('floated.eois');	
   Route::get('/master/floatedeoi/html','floatedEoiData')->name('floatedeoi.html');
   Route::get('/master/floated/view/{recordid}','floatedView')->name('floated.view');
   Route::get('/master/show/interest/{recordid}','showInterest')->name('show.interest');
   Route::post('/master/participate/eoi/{recordid}','participateEoi')->name('participate.eoi');
   Route::get('/master/participated/eois','participatedEoiList')->name('participated.eois');
   Route::get('/master/participatedeoi/html','participatedEoiData')->name('participatedeoi.html');
   Route::get('/master/fillform/view/{recordid}','candidateForm')->name('fillform.view');

   Route::post('/master/replace/presentationfile','replacePresentationFile')->name('replace.presentationfile');
   Route::post('/master/replace/signedcopyofeoi','replaceSignedCopyOfEoi')->name('replace.signedcopyofeoi');

   /*
   Route::post('/master/upload/ppt/{recordid}','uploadPpt')->middleware('pdf.security')->name('upload.ppt');
   Route::post('/master/upload/resumes/{recordid}','uploadResumes')->middleware('pdf.security')->name('upload.resumes');
   */

   Route::post('/master/upload/ppt/{recordid}','uploadPpt')->name('upload.ppt');
   Route::post('/master/upload/resumes/{recordid}','uploadResumes')->name('upload.resumes');
   
   //Route::get('/master/prebid/enquiry/{recordid}/{questionid?}','prebidEnquiry')->name('prebid.enquiry');
   Route::match(['get', 'post'], '/master/prebid/enquiry/{recordid}/{questionid?}', 'prebidEnquiry')->name('prebid.enquiry'); 
   
   Route::post('/master/store/prebidenquiry/{recordid}','storePreBidEnquiry')->name('store.prebidenquiry');
   Route::post('/master/prebid/enquirystatus/{recordid}','prebidEnquiryStatus')->name('prebid.enquirystatus');
   
   Route::post('/master/prebid/broadcasted/{recordid}','prebidBroadCasted')->name('prebid.broadcasted');
   
   Route::get('/master/view/prebidresponse/{recordid}','viewPreBidResponse')->name('view.prebidresponse');
   Route::get('/master/download/prebidresponse/{requestid?}','downloadPreBidResponse')->name('download.prebidresponse');
   
   Route::get('/master/download/vendorprebid/{requestid?}','downloadVendorPreBid')->name('download.vendorprebid');
   
   Route::post('/master/remove/resume','removeResume')->name('remove.resume');
   
   Route::get('/master/vendor/orderslist','vendorWorkOrderList')->name('vendor.orderslist');	
   Route::get('/master/vendororderlist/html','getVendorWorkOrderData')->name('vendororderlist.html');   
   Route::get('/master/show/vendorworkorder/{orderid}','showVendorOrder')->name('show.vendorworkorder');
   Route::get('/master/view/deploymentdate/{orderid}','viewDeployment')->name('view.deploymentdate');
   Route::post('/master/update/vendordeployment/{orderid}','updateVendorDeployment')->name('update.vendordeployment');
   
   Route::get('/master/manage/vendor/{orderid}','manageVendorOrder')->name('manage.vendor');
   Route::get('/master/mpr/months','loadMprMonths')->name('mpr.months');
   Route::get('/master/mpr/resources','loadMprResources')->name('mpr.resources');
   Route::get('/master/load/resourcempr','loadResourceMpr')->name('load.resourcempr');
   Route::post('/master/store/mprattendance','storeMprAttendance')->name('store.mprattendance');
   Route::post('/master/submit/mpr','submitMpr')->name('submit.mpr');
   
   Route::get('/master/pending/invoices','pendingInvoices')->name('pending.invoices');

   Route::get('/master/uploadmpr/document','uploadMprDocumentForm')->name('uploadmpr.document');

   Route::get('/master/make/vendorinvoice','makeVendorInvoice')->name('make.vendorinvoice');
   //Route::post('/master/prepare/vendorinvoice','prepareVendorInvoice')->name('prepare.vendorinvoice');
   Route::match(['get', 'post'], '/master/prepare/vendorinvoice', 'prepareVendorInvoice')->name('prepare.vendorinvoice');
   Route::post('/master/store/invoicevoucher','storeInvoiceVoucher')->name('store.invoicevoucher');
   
   Route::get('/master/view/mprlog','viewMprLog')->name('view.mprlog');
   Route::get('/master/view/mprupdates/{summaryid?}','viewMprUpdates')->name('view.mprupdates');
   Route::post('/master/upload/signed/documents','uploadSignedDocuments')->name('upload.singed.documents');
   
   
   Route::post('/master/assignto/user','assignEoIToUser')->name('assignto.user');
   
   Route::post('/master/assignorderto/user','assignOrderToUser')->name('assignorderto.user');
   
   
   Route::get('/master/vendors/orderresources','orderWiseResource')->name('vendor.orderresources');
   Route::get('/master/vendororderwiseresource/html','getOrderWiseResource')->name('vendororderwiseresource.html');
   
   Route::get('/master/workordervendor/replaceresource/{deploymentid?}','replaceResourceInOrder')->name('workordervendor.replaceresource');
   Route::post('/master/store/replaceresourcevendor/{deploymentid?}','storeResourceReplacement')->name('store.replaceresourcevendor');

   Route::post('/master/view/resourcedetail/{deploymentid?}','viewResourceDetail')->name('view.resourcedetail');   
   Route::post('/master/vendorresource/update/{deploymentid?}','updateResourceDetail')->name('vendorresource.update');

   Route::post('/master/participation/otp/verification','generateParticipationOtp')->name('participation.otp.verification');
   Route::post('/master/verify/participation/otp','verifyParticipationOtp')->name('verify.participation.otp');
   
   Route::get('/vendor/pending/mprs','vendorPendingMpr')->name('vendor.pending.mprs');
   Route::get('/vendor/pending/mprs/html','vendorPendingMprData')->name('vendor.pending.mprs.html');
   
   Route::get('/vendor/approved/mprs','vendorApprovedMpr')->name('vendor.approved.mprs');
   Route::get('/vendor/approved/mprs/html','vendorApprovedMprData')->name('vendor.approved.mprs.html');
   
   Route::get('/vendor/invoiced/mprs','vendorInvoicedMpr')->name('vendor.invoiced.mprs');
   Route::get('/vendor/invoiced/html','vendorInvoicedData')->name('vendor.invoiced.html');
});

Route::middleware(['isLoggedIn','check.method', 'role:VENDOR'])->controller(InvoiceController::class)->group(function()
{
   Route::get('/master/submit/invoice','submitInvoice')->name('submit.invoice');
   Route::post('/master/store/invoice','storeInvoice')->name('store.invoice');
   Route::get('/master/invoice/history','invoiceHistory')->name('invoice.history');
   Route::get('/master/invoicehistory/html','getInvoiceData')->name('invoicehistory.html');
});

Route::middleware(['isLoggedIn','check.method', 'role:VENDOR'])->controller(PreBidQueryController::class)->group(function()
{
   Route::post('/master/store/query','storeQuery')->name('store.query');
   Route::post('/master/update/query','updatePreBidQuery')->name('update.query');
   Route::post('/master/upload/prebidfile','uploadPreBidFile')->name('upload.prebidfile');
   Route::post('/master/forward/prebidquery','forwardPreBidQuery')->name('forward.prebidquery');  
});


Route::middleware(['isLoggedIn','check.method', 'role:VENDOR'])->controller(VendorUsersController::class)->group(function()
{
   Route::get('/master/add/vendoruser','addVendorUser')->name('add.vendoruser');
   Route::post('/master/store/vendoruser/{recordid?}','storeVendorUser')->name('store.vendoruser');
   Route::get('/master/vendor/userlist','vendorUsersList')->name('vendor.userlist');
   
   Route::get('/master/vendorusers/html','getVendorUserData')->name('vendorusers.html');
   Route::get('/master/edit/vendoruser/{recordid?}','editVendorUser')->name('edit.vendoruser');
   
});
