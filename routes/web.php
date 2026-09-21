<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\MasterController;
use App\Http\Controllers\Master\DepartmentController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\Master\EoiController;

use App\Http\Controllers\Master\ServicesController;
use App\Http\Controllers\Master\ManagementController;
use App\Http\Controllers\Master\VendorController;

use App\Http\Controllers\Master\ResourceController;
use App\Http\Controllers\Master\WorkOrderController;
use App\Http\Controllers\Master\DocumentController;
use App\Http\Controllers\Master\FinanceModule;
use App\Http\Controllers\Finance\FinanceController;
use App\Http\Controllers\Master\ViewFileController;
use App\Http\Controllers\Master\PasswordController;
use App\Http\Controllers\Master\UtilityController;
use App\Http\Controllers\Master\RolePermissionController;
use App\Http\Controllers\Master\CommunicationController;
use App\Http\Controllers\Master\OrderValueController;
use App\Http\Controllers\Master\AdminPreBidQuery;
use App\Http\Controllers\Master\CommitteeMember;
use App\Http\Controllers\Master\CalendarController;
use App\Http\Controllers\Master\RemunerationController;
use App\Http\Controllers\Master\NotificationController;
use App\Http\Controllers\Master\LoiBasedWorkOrderController;
use App\Http\Controllers\Master\ExtensionController;
use App\Http\Controllers\Master\ReportingController;

use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Finance\DemandNoteController;
use App\Http\Controllers\Finance\DepartmentPaymentController;
use App\Http\Controllers\Finance\DepartmentInvoiceController;
use App\Http\Controllers\Finance\FinanceVendorInvoiceController;
use App\Http\Controllers\Finance\FinanceVendorPaymentController;
use App\Http\Controllers\Finance\FinanceDepartmentFundController;

use App\Http\Controllers\Master\DashboardController;
use App\Http\Controllers\Master\PasswordManagementController;

use App\Http\Controllers\Master\UserController;
use App\Http\Controllers\Master\StateCityController;

use App\Http\Controllers\Master\MenuController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Master\TaxController;

use App\Http\Controllers\Master\PDFController;

use App\Http\Controllers\Master\ProfileController;

use App\Http\Controllers\CustomAuthController;
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
    if(in_array($locale, ['en', 'mr', 'hn'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.change');

Route::get('/loginpanel', function () {
    return redirect('loginpanel');
});

Route::get('/', function () {
    return redirect('loginpanel');
});


/*
Route::get('/storage-link',function(){
    $targetFolder = storage_path('app/public');
    $linkFolder = $_SERVER['DOCUMENT_ROOT'].'/puran/storage';
    symlink($targetFolder,$linkFolder);
});
*/

Route::get('/invalid-host', function () {
    return view('errors.invalid-host');
})->name('invalid-host');

Route::get('/invalid-referer', function () {
    return view('errors.invalid-referer');
})->name('invalid-referer');


Route::controller(RegistrationController::class)->group( function()
{
	Route::get('/registration','userRegistration')->name('registration');
	Route::post('/generate_otp','generateOtp')->name('generate_otp');
	Route::post('/verify_department_otp','verifyDepartmentOtp')->name('verify_department_otp');
		
	
});
Route::get('/register/department/{token}', function ($token) {
    return view('auth.department_registration', compact('token'));
})->name('department.registration.view');

Route::get('/register/impellent/{token}', function ($token) {
    return view('auth.impellent_registration', compact('token'));
})->name('impellent.registration.view');

Route::get('/register/vendor/{token}', function ($token) {
    return view('auth.vendor_registration', compact('token'));
})->name('vendor.registration.view');

Route::get('/registered/view', function () {
    return view('auth.registered');
})->name('registered.view');


// LOGIN RELATED LINKS
Route::controller(CustomAuthController::class)->group( function()
{
    Route::get('/loginpanel','login')->middleware('alreadyLoggedIn')->name('loginpanel');
    Route::post('/login-user','loginUser')->middleware('throttle:login_data')->name('login-user');
	//Route::post('/login-user','loginUser')->middleware('throttle:login_data')->name('login-user');
    Route::get('/dashboard','dashboard')->middleware('isLoggedIn');
    Route::get('/dashboard','dashboard')->middleware('isLoggedIn')->name('dashboard');
    Route::get('/logout','logout')->middleware('isLoggedIn')->name('logout');
	Route::get('set-locale/{locale}','setLocale')->name('set-locale');
	
	Route::get('/forgot','forgotPassword')->name('forgotpassword');
	Route::post('/forgot-password','sendPasswordLink')->name('forgot-password');
	Route::get('/password/reset/{token}', 'showResetForm')->name('userpassword.reset');
	Route::post('/update-password','updatePassword')->name('update-password');
});


// DATA CONTROLLER ALL ROUTE
Route::controller(DataController::class)->group( function() {
    Route::get('/load/data','loadData')->middleware('isLoggedIn')->name('load.data');
});

Route::middleware(['isLoggedIn', 'role:ADMIN,DEPARTMENT,VENDOR,PROJECT MANAGER'])->controller(DashboardController::class)->group(function()
{
   Route::get('/master/admindashboard/html','getAdminDashboard')->name('admindashboard.html');
   Route::get('/master/admin/noticeboard','getAdminNoticeBoard')->middleware('role:ADMIN')->name('admin.noticeboard');
   Route::get('/master/adminnotice/html','getAdminNoticeBoardData')->middleware('role:ADMIN')->name('adminnotice.html');
   
   Route::get('/master/pmdashboard/html','getPmDashboard')->middleware('role:PROJECT MANAGER')->name('pmdashboard.html');
   Route::get('/master/departmentdashboard/html','getDeptDashboard')->middleware('role:DEPARTMENT')->name('departmentdashboard.html');
});

Route::middleware(['isLoggedIn','check.method','role:ADMIN,DEPARTMENT,VENDOR,PROJECT MANAGER'])->controller(PasswordManagementController::class)->group(function()
{
	
});


Route::middleware(['isLoggedIn','check.method', 'role:ADMIN,DEPARTMENT,PROJECT MANAGER,VENDOR'])->controller(CommunicationController::class)->group(function()
{
	Route::get('/master/communication/message/{requestid?}/{rectype?}/{vendor_id?}','startMessage')->name('communication.message');
	Route::get('/master/communication/html','getCommunicationData')->name('communication.html');
	Route::post('/master/sendmessage/conversation','sendConversation')->name('sendmessage.conversation');
	Route::post('/master/close/conversation','closeConversation')->name('close.conversation');
	
	Route::post('/master/markas/read','markAsRead')->name('markas.read');
});

Route::middleware(['isLoggedIn','check.method', 'role:ADMIN'])->controller(CommunicationController::class)->group(function()
{
	Route::post('/master/sendmessageadmin/conversation','sendConversationAdmin')->name('sendmessageadmin.conversation');
});


Route::middleware(['isLoggedIn', 'role:ADMIN'])->controller(RolePermissionController::class)->group(function()
{
   Route::get('/master/add/role','addRole')->name('add.role');
   Route::post('/master/store/role/{recordid}','storeRole')->middleware('throttle:master_data')->name('store.role');
   Route::get('/master/role/html','getRoleData')->name('role.html');
   Route::get('/master/edit/role/{recordid}','editRole')->name('edit.role');
   Route::post('/master/update/role/{recordid}','storeRole')->name('update.role');
   Route::get('/delete/role/{recordid}','deleteRole')->name('delete.role');   

   Route::get('/master/role/permission','rolePermission')->name('role.permission');
   Route::get('/master/rolepermission/html','getRolePermissionData')->middleware('isLoggedIn')->name('rolepermission.html');
   Route::post('/master/store/rolepermission/{recordid}','storeRolePermission')->middleware('isLoggedIn')->name('store.rolepermission');
   Route::get('/master/role/html','getRoleData')->middleware('isLoggedIn')->name('role.html');
   Route::get('/master/edit/role/{recordid}','editRole')->middleware('isLoggedIn')->name('edit.role');
   Route::post('/master/update/role/{recordid}','storeRole')->middleware('isLoggedIn')->name('update.role');
   Route::get('/delete/role/{recordid}','deleteRole')->middleware('isLoggedIn')->name('delete.role');   
	
});


Route::middleware(['isLoggedIn', 'role:ADMIN'])->controller(FinanceModule::class)->group(function()
{
   Route::get('/master/payment/request/{search?}','getWorkOrderList')->name('payment.request');
   Route::get('/master/orders/html','getOrdersData')->name('orders.html');
   Route::get('/master/admininvoice/list/{search?}','getInvoiceList')->name('admininvoice.list');
   Route::get('/master/admininvoice/html','getPendingInvoiceData')->name('admininvoice.html');
   Route::get('/master/firmwiseproject/html','getFirmWiseProjectData')->name('firmwiseproject.html');
   
   Route::get('/master/adminvendor/invoices','invoiceHistory')->name('adminvendor.invoices');
   Route::get('/master/admininvoicehistory/html','getInvoiceData')->name('admininvoicehistory.html');
   
   //Route::get('/master/add/invoicepayment','addInvoicePayment')->name('add.invoicepayment');
   Route::match(['get', 'post'], '/master/add/invoicepayment', 'addInvoicePayment')->name('add.invoicepayment');
   Route::post('/master/save/payment','savePaymentData')->name('save.payment');
   
   Route::get('/master/adminvendor/payments','invoicePaymentHistory')->name('adminvendor.payments');
   Route::get('/master/adminpaymenthistory/html','getInvoicePaymentData')->name('adminpaymenthistory.html');

   Route::get('/master/adminpayment/summary','paymentSummary')->name('adminpayment.summary');
   Route::get('/master/adminpaymentsummary/html','getPaymentSummaryData')->name('adminpaymentsummary.html');
   
   Route::post('/master/view/invoices','viewVendorInvoices')->name('view.invoices');

});


Route::middleware(['isLoggedIn','check.method','role:ADMIN'])->controller(EoiController::class)->group(function()
{
   Route::get('/master/eoi/requestlist/{firm_type?}/{record_type?}','eoiRequestList')->name('eoi.requestlist'); 
   Route::get('/master/eoirequest/html','getEoiRequestData')->name('eoirequest.html');
   Route::get('/master/export/eoidata','exportEoiData')->name('export.eoidata'); 
   Route::get('/master/view/eoirequest','viewEoiRequest')->name('view.eoirequest');
   Route::get('/master/prepare/draft/{recordid}','prepareDraft')->name('prepare.draft');
   Route::get('/master/view/departmentupdate/{recordid}','viewDepartmentUpdate')->name('view.departmentupdate');
   Route::get('/master/eoipreview/{recordid}','eoiPreviewPrintable')->name('eoipreview');
   Route::post('/master/save/draft','saveDraft')->name('save.draft');
   Route::post('/master/updateremark/eoi','updateRemarkEoi')->name('updateremark.eoi');
   Route::post('/master/update/draft/{recordid}','updateDraft')->name('update.draft');
   Route::get('/master/prepare/float/{recordid}','prepareToFloat')->name('prepare.float');
   
   Route::get('/master/floatingvendor/html','getFloatVendorData')->name('floatingvendor.html');   
   Route::post('/master/float/eoi','floatEoi')->name('float.eoi');
   Route::get('/master/prepare/committee/{recordid}','prepareCommittee')->name('prepare.committee');
   Route::post('/master/upload/committee/{recordid}','uploadCommittee')->name('upload.committee');
   Route::get('/master/view/pptresumes/{recordid}','viewPptResumes')->name('view.pptresumes');
   Route::get('/master/view/participations/{recordid}','viewParticipations')->name('view.participations');
   Route::post('/master/update/resumeremark','updateResumeRemark')->name('update.resumeremark');
   Route::post('/master/update/interviewlink','updateInterview')->name('update.interviewlink');
   Route::post('/master/update/interviewdate','updateInterviewDate')->name('update.interviewdate');
   Route::post('/master/update/participationremark','updateParticipationRemark')->name('update.participationremark');
   
   Route::post('/master/create/workorder','createWorkOrder')->middleware('pdf.security')->name('create.workorder');
   Route::get('/master/showworkorder/{orderid}','showOrder')->name('show.workorder');
   Route::get('/master/showworkorder/{orderid}','confirmOrder')->name('confirmed.workorder');
   Route::get('/master/printing/printworkorder/{orderid}','printWorkOrder')->name('print.workorder');
   
   Route::get('/master/prebidenquiry/list/{recordid}','preBidEnquiryList')->name('prebidenquiry.list'); 
   Route::get('/master/prebidenquiry/html','preBidEnquiryData')->name('prebidenquiry.html'); 
   Route::get('/master/download/queries/{recordid}','downloadAllFiles')->name('download.queries'); 
   Route::get('/master/prebid/forward/{recordid}','preBidForward')->name('prebid.forward'); 
   
   Route::post('/master/store/forward','storeForward')->name('store.forward'); 
   
   Route::post('/master/publish/tovendors','publishToVendors')->name('publish.tovendors'); 
   
   Route::post('/master/prepare/workorder','prepareWorkOrder')->name('prepare.workorder'); 
   
   Route::post('/master/setresume/status','setResumeStatus')->name('setresume.status'); 
   //Route::post('/master/generate/workorder/{recordid}','generateWorkOrder')->name('generate.workorder'); 
   Route::match(['get','post'],'/master/generate/workorder/{recordid}','generateWorkOrder')->name('generate.workorder'); 
   
   Route::get('/master/generate/wonumber','generateWoNumber')->name('generate.wonumber'); 
   
   Route::post('/master/mark/asread','markAsRead')->name('mark.asread');
   
   Route::get('/master/show/eoicontent/{recordid?}','showEoiContent')->name('show.eoicontent');
   Route::get('/master/show/factsheet/{recordid?}','showFactSheet')->name('show.factsheet');
   Route::match(['get','post'],'/master/show/previeweoi/{recordid?}','showPreviewEoiContent')->name('show.previeweoi');
   Route::get('/master/eoi/eoicancel/{recordid?}','eoiCancelRequest')->name('eoi.eoicancel');
   Route::get('/master/eoicancel/html','getCancelledEoiData')->name('eoicancel.html');
   
   Route::get('/master/show/draftpreview/{requestid?}','showDraftPreview')->name('show.draftpreview');
   
   Route::post('/master/published/firms/{requestid?}','publishedFirms')->name('published.firms');
   
   
   Route::get('/master/send/approvalform/{requestid?}','showApprovalForm')->name('send.approvalform');
   Route::post('/master/sendto/approval','sendToApproval')->name('sendto.approval');
   
   Route::get('/master/eoi/approval/{requestid?}','eoiApproval')->name('eoi.approval');
   
   Route::post('/master/sendto/approve','sendToApprove')->name('sendto.approve');
   
   Route::post('/master/send/approvalotp','sendApprovalOTP')->name('send.approvalotp');
   
   Route::get('/master/extend/eoidate/{recordid}','extendEoiDate')->name('extend.eoidate');
   Route::post('/master/store/eoiextension/{recordid}','storeEoiExtension')->name('store.eoiextension');
   
   Route::post('/master/updateinterview/daterequest','updateInterviewDateRequest')->name('updateinterview.daterequest');
   
   Route::get('/master/update/eoidates/{requestid?}','getEoIDates')->name('update.eoidates');
   Route::post('/master/eoi/updatedates/{requestid?}','updateEoiDates')->name('eoi.updatedates');
   
   /* NEW WORK ORDER RELATED ROUTES */
   Route::post('/master/create/finalworkorder','createFinalWorkOrder')->middleware('pdf.security')->name('create.finalworkorder');
   Route::get('/master/orderconfirmation/{orderid}','confirmFinalOrder')->name('confirmed.finalworkorder');
   
   Route::get('/master/more/eoidata','viewMoreEoIData')->name('more.eoidata');
   
   Route::get('/master/close/project/{requestid?}','closeProject')->name('close.project');
   Route::post('/master/close/confirmation/{requestid?}','closeConfirmation')->name('close.confirmation');
   Route::post('/master/update/projectname','updateProjectName')->name('update.projectname');
   
   Route::get('/master/allow/addition/{requestid?}/{allow_type?}','allowAddition')->name('allow.addition');
});


Route::controller(EoiController::class)->group( function()
{
	Route::get('/master/prebid/querieshtml','preBidQueryData')->name('prebid.querieshtml');
});


Route::middleware(['isLoggedIn','check.method', 'role:ADMIN'])->controller(WorkOrderController::class)->group(function()
{
   Route::get('/master/workorder/list/{firm_type?}','workOrderList')->name('workorder.list');
   Route::get('/master/workorders/list/{search?}','workOrdersList')->where('search', '.*')->name('workorders.list');
   Route::get('/master/orderlist/html','getWorkOrderData')->name('orderlist.html');
   Route::get('/master/export/orderdata','exportOrderData')->name('export.orderdata');   
   
   Route::get('/master/set/orderdate/{orderid}','setOrderDate')->middleware('pdf.security')->name('set.orderdate');
   Route::post('/master/update/workorderdate/{orderid}','updateWorkOrderDate')->middleware('pdf.security')->name('update.workorderdate');
   
   Route::get('/master/set/deploymentdate/{orderid}','setDeploymentDate')->name('set.deploymentdate');
   Route::post('/master/update/deploymentdate/{orderid}','updateDeploymentDate')->name('update.deploymentdate');
   
   Route::get('/master/viewmore/order/{orderid}','viewMoreOrderDetail')->name('viewmore.order');
   Route::get('/master/pay/demandnote/{paymentid}','payDemandNote')->name('pay.demandnote');
   Route::post('/master/receive/demandnotepayment/{paymentid}','receiveDemandNotePayment')->middleware('pdf.security')->name('receive.demandnotepayment');
   
   Route::get('/master/demandnote/paymenthistory','demandNotePaymentHistory')->name('demandnote.paymenthistory');
   Route::get('/master/add/demandnote/{orderid}','addDemandNote')->name('add.demandnote');
   Route::post('/master/store/demandnote/{orderid}','storeDemandNote')->middleware('pdf.security')->name('store.demandnote');
   Route::get('/master/filter/demandnotepayment','demandNotePaymentHistory')->name('filter.demandnotepayment');
   
   Route::get('/master/add/workorder','addWorkOrder')->name('add.workorder');
   
   Route::post('/load-order-form','loadWorkOrderForm')->name('load-order-form');
   Route::post('/master/add/workorderrecord','addWorkOrderRecord')->name('add.workorderrecord');   
   Route::get('/master/get/vendorlist','getVendorsList')->name('get.vendorlist');   
   Route::post('/master/store/workorder','storeDirectWorkOrder')->middleware('pdf.security')->name('store.workorder');

   // New Work Order Related
   Route::get('/master/add/neworder/{requestid?}','addNewOrder')->name('add.neworder');
   Route::get('/master/view/orderresource','viewOrderResource')->name('view.orderresource');
   Route::post('/master/store/neworder/{requestid}','storeNewOrder')->middleware('pdf.security')->name('store.neworder');
   
   Route::get('/master/extend/order/{orderid}','extendOrder')->name('extend.order');
   Route::post('/master/store/extend/{orderid}','storeExtendOrder')->name('store.extend');
   
   
   Route::post('/master/workorder/confirmation/{requestid?}','workOrderConfirmation')->name('workorder.confirmation');
   // New Resource Related
   // LOI BASED orders
   
   Route::get('/master/workorder/resource','orderWiseResource')->name('order.resource');
   Route::get('/master/orderwiseresource/html','getOrderWiseResource')->name('orderwiseresource.html');
   Route::post('/master/resource/update','updateResourceDetail')->name('resource.update');
   Route::get('/master/export/orderwiseresourcedata','exportOrderWiseResourceData')->name('export.orderwiseresourcedata');   
   
   
   Route::get('/master/workorder/replaceresource/{deploymentid?}','replaceResourceInOrder')->name('workorder.replaceresource');
   Route::post('/master/store/replaceresource/{deploymentid?}','storeResourceReplacement')->name('store.replaceresource');
   
   Route::get('/master/workorder/cancellation/form/{orderid?}','cancelWorkOrder')->name('workorder.cancellation.form');
   Route::post('/master/cancel/confirmation/{orderid?}','cancelConfirmation')->name('cancel.confirmation');
   
   Route::post('/master/workorder/addresource','addResourceWorkorder')->name('workorder.addresource');
   
   Route::get('/master/update/duedate','updateDueDate')->name('update.duedate');
   Route::get('/master/workorderforduedate/html','getOrderList')->name('workorderforduedate.html');
   Route::post('/master/workorder/updateDueDate','updateWorkOrderDueDate')->name('workorder.updateDueDate');
   
});

Route::middleware(['isLoggedIn','check.method','role:ADMIN'])->controller(ResourceController::class)->group(function()
{
	// Resource Related
	Route::get('/master/add/newresource/{requestid?}','addNewResource')->name('add.newresource');

	
	Route::get('/master/resources/list','resourceList')->name('resources.list');
	Route::get('/master/resources/html','getResourcesData')->name('resources.html'); 
	Route::get('/master/export/resources','exportResourceData')->name('export.resources'); 

	
	Route::get('/master/release/resource/{userid?}','releaseResourceForm')->name('release.resource'); 
	Route::post('/master/store/release','storeRelease')->name('store.release'); 
	Route::get('/master/release/request','releasingResource')->name('release.request'); 
	Route::get('/master/releasing/html','getReleasingData')->name('releasing.html'); 
	Route::get('/master/process/release/{recordid?}','processRelease')->name('process.release'); 
	//Route::get('/master/send/approvalform/{requestid?}','showApprovalForm')->name('send.approvalform');
	
   Route::post('/master/viewresource/detail/{deploymentid?}','viewResourceDetail')->name('viewresource.detail');   
   Route::post('/master/adminresource/update/{deploymentid?}','updateResourceDetail')->name('adminresource.update');
   
   Route::get('/master/resource/resumes','resourceResumes')->name('resource.resumes');
   Route::get('/master/resumes/html','getEoIResumes')->name('resumes.html');
   
   Route::get('/master/buildnxt/resources','buildnxtResources')->name('buildnxt.resources');
	
});


Route::middleware(['isLoggedIn','check.method'])->controller(DocumentController::class)->group(function()
{
   Route::get('/master/read/scopeofwork','readScopeDoc')->name('read.scopeofwork');
   Route::get('/master/read/aboutproject','readAboutDoc')->name('read.aboutproject');
   Route::get('/master/read/otherproject','readOtherDoc')->name('read.otherproject');
   Route::get('/master/show/vendorslist','showVendorsList')->name('show.vendorslist');
});



Route::middleware(['isLoggedIn','check.method', 'role:ADMIN'])->controller(DepartmentController::class)->group(function()
{
   Route::get('/master/department/request','addDeptRequest')->name('department.request');   
   Route::post('/master/store/departmentrequest/{recordid}','storeDeptRequest')->middleware('throttle:master_data')->name('store.departmentrequest');
   Route::get('/master/departmentrequest/html','getDeptRequestData')->name('departmentrequest.html');
   Route::get('/master/edit/departmentrequest/{recordid}','editDeptRequest')->name('edit.departmentrequest');
   Route::get('/master/detail/departmentrequest/{recordid}','detailDeptRequest')->name('detail.departmentrequest');
   Route::post('/master/update/departmentrequest/{recordid}','storeDeptRequest')->name('update.departmentrequest');
   Route::post('/master/approval/departmentrequest/{recordid}','approvalDeptRequest')->name('approval.departmentrequest');
   Route::get('/delete/departmentrequest/{recordid}','deleteDeptRequest')->name('delete.departmentrequest');   

   Route::get('/master/add/requirement/{recordid}','addRequirement')->name('add.requirement');   
   Route::post('/master/store/requirement/{recordid}','storeRequirement')->name('store.requirement');
   Route::get('/master/requirement/html','getRequirementData')->name('requirement.html');
   Route::get('/master/edit/requirement/{recordid}','editRequirement')->name('edit.requirement');
   Route::post('/master/update/requirement/{recordid}','storeRequirement')->name('update.requirement');
   Route::get('/delete/requirement/{recordid}','deleteRequirement')->name('delete.requirement');   
   
   Route::get('/master/add/projectmanager','addProjectManager')->name('add.projectmanager');   
   Route::get('/master/projectmanager/html','getProjectManagerData')->name('projectmanager.html');

   Route::post('/master/store/projectmanager/{recordid}','storeProjectManager')->middleware('throttle:master_data')->name('store.projectmanager');
   Route::get('/master/edit/projectmanager/{recordid}','editProjectManager')->name('edit.projectmanager');
   Route::post('/master/update/projectmanager/{recordid}','storeProjectManager')->name('update.projectmanager');

   

});



// VENDOR RELATED LINKS
Route::middleware(['isLoggedIn','check.method', 'role:ADMIN'])->controller(VendorController::class)->group(function()
{
   Route::get('/master/vendor/list','vendorList')->name('vendor.list');
   Route::get('/master/vendors/html','getVendorData')->name('vendors.html');
   Route::post('/master/store/email','storeEmail')->name('store.email');
   Route::get('/delete/vendor/{recordid}','deleteVendor')->name('delete.vendor');   
   Route::get('/master/exportvendor/html','exportVendorData')->name('exportvendor.html');
   
   Route::post('/master/store/firm/{recordid}','storeFirm')->middleware('throttle:master_data')->name('store.firm');
   Route::get('/master/edit/vendor/{recordid}','editVendor')->name('edit.vendor');
   Route::post('/master/update/firm/{recordid}','storeFirm')->name('update.firm');
});

Route::middleware(['isLoggedIn','check.method', 'role:ADMIN,VENDOR'])->controller(VendorController::class)->group(function()
{
   Route::get('/master/sendmessage/vendor/{vendorid?}','sendMessageVendor')->name('sendmessage.vendor');
   Route::post('/master/storevendor/message','storeMessageVendor')->name('storevendor.message');
});



// SERVICES RELATED LINKS
Route::middleware(['isLoggedIn','check.method', 'role:ADMIN'])->controller(ServicesController::class)->group(function()
{

   Route::get('/master/add/commission','addCommission')->name('add.commission');   
   Route::get('/master/commission/html','getCommissionData')->name('commission.html');
   Route::get('/master/update/commission','updateCommission')->name('update.commission');


   Route::get('/master/add/startendphoto','addStartEndPhoto')->name('add.startendphoto');   
   Route::post('/master/store/startendphoto/{recordid}','storeStartEndPhoto')->name('store.startendphoto');
   Route::get('/master/startendphoto/html','getStartEndPhotoData')->name('startendphoto.html');
   Route::get('/master/edit/startendphoto/{recordid}','editStartEndPhoto')->name('edit.startendphoto');
   Route::post('/master/update/startendphoto/{recordid}','storeStartEndPhoto')->name('update.startendphoto');
   Route::get('/delete/startendphoto/{recordid}','deleteStartEndPhoto')->name('delete.startendphoto');


   Route::get('/master/add/services','addService')->name('add.services');
   Route::post('/master/store/services/{recordid}','storeService')->name('store.services');   
   Route::get('/master/services/html','getServiceData')->name('services.html');
   Route::get('/master/edit/services/{recordid}','editService')->name('edit.services');
   Route::post('/master/update/services/{recordid}','storeService')->name('update.services');
   Route::get('/delete/services/{recordid}','deleteService')->name('delete.services');
   Route::get('/master/setactive/service','setActiveInactiveService')->name('setactive.service');
   Route::get('/master/service/viewphotos','getServicePhotos')->name('service.viewphotos');   
   Route::post('/master/service/photos/{recordid}','storeServicePhotos')->name('service.photos');
   
   Route::get('/master/servicelists/html','getServiceListData')->name('servicelists.html');
   Route::get('/master/update/serviceorder','updateServiceOrder')->name('update.serviceorder');
   
   Route::get('/master/service/copy','copyServiceData')->name('service.copy');
   Route::post('/master/service/copydata/{recordid}','copyServicesData')->name('service.copydata');
   

   Route::get('/delete/servicephotos/{recordid}','deleteServicePhotos')->name('delete.servicephotos');
   Route::get('/master/service/options','addServiceOption')->name('service.options');
   Route::post('/master/store/options/{recordid}','storeServiceOption')->name('store.options');
   Route::get('/master/viewservice/options','viewServiceOption')->name('viewservice.options');
   Route::get('/master/edit/serviceoption/{recordid}','editServiceOption')->name('edit.serviceoption');
   Route::post('/master/update/options/{recordid}','updateServiceOption')->name('update.options');
   Route::get('/delete/options/{recordid}','deleteServiceOption')->name('delete.options');

   Route::get('/master/add/serviceinclude','addInclude')->name('add.serviceinclude');   
   Route::post('/master/store/serviceinclude/{recordid}','storeInclude')->name('store.serviceinclude');
   Route::get('/master/serviceinclude/html','getIncludeData')->name('serviceinclude.html');
   Route::get('/master/edit/serviceinclude/{recordid}','editInclude')->name('edit.serviceinclude');
   Route::post('/master/update/serviceinclude/{recordid}','storeInclude')->name('update.serviceinclude');
   Route::get('/delete/serviceinclude/{recordid}','deleteInclude')->name('delete.serviceinclude');
   Route::get('/master/up/include','updateInclude')->name('up.include');

   Route::get('/master/add/serviceexclude','addExclude')->name('add.serviceexclude');   
   Route::post('/master/store/serviceexclude/{recordid}','storeExclude')->name('store.serviceexclude');
   Route::get('/master/serviceexclude/html','getExcludeData')->name('serviceexclude.html');
   Route::get('/master/edit/serviceexclude/{recordid}','editExclude')->name('edit.serviceexclude');
   Route::post('/master/update/serviceexclude/{recordid}','storeExclude')->name('update.serviceexclude');
   Route::get('/delete/serviceexclude/{recordid}','deleteExclude')->name('delete.serviceexclude');
   Route::get('/master/up/exclude','updateExclude')->name('up.exclude');


   Route::get('/master/add/need','addNeed')->name('add.need');   
   Route::post('/master/store/need/{recordid}','storeNeed')->name('store.need');
   Route::get('/master/need/html','getNeedData')->name('need.html');
   Route::get('/master/edit/need/{recordid}','editNeed')->name('edit.need');
   Route::post('/master/update/need/{recordid}','storeNeed')->name('update.need');
   Route::get('/delete/need/{recordid}','deleteNeed')->name('delete.need');
   Route::get('/master/up/need','updateNeed')->name('up.need');

   Route::get('/master/add/category','addCategory')->name('add.category');
   Route::post('/master/store/category/{recordid}','storeCategory')->name('store.category');   
   Route::get('/master/category/html','getCategoryData')->name('category.html');
   Route::get('/master/edit/category/{recordid}','editCategory')->name('edit.category');
   Route::post('/master/update/category/{recordid}','storeCategory')->name('update.category');
   Route::get('/delete/category/{recordid}','deleteCategory')->name('delete.category');
   Route::get('/master/list/getsubcategory','getSubCategoryList')->name('getsubcategory.list');
   Route::get('/master/options/getsubcategory','getSubCategoryOption')->name('getsubcategory.options');

});


// MASTER RELATED LINKS
Route::middleware(['isLoggedIn','check.method','role:ADMIN'])->controller(MasterController::class)->group(function()
{
   Route::get('/master/prebid/format','preBidFormat')->name('prebid.format');
   //Route::post('/master/store/format/{recordid}','storePreBidFormat')->middleware('pdf.security')->name('store.format');
   Route::post('/master/store/format/{recordid}','storePreBidFormat')->name('store.format');

   Route::get('/master/add/committeemember','addCommitteeMember')->name('add.committeemember');   
   Route::post('/master/store/committeemember/{recordid}','storeCommitteeMember')->middleware('throttle:master_data')->name('store.committeemember');
   Route::get('/master/committeemember/html','getCommitteeMemberData')->name('committeemember.html');
   Route::get('/master/edit/committeemember/{recordid}','editCommitteeMember')->name('edit.committeemember');
   Route::post('/master/update/committeemember/{recordid}','storeCommitteeMember')->name('update.committeemember');
   Route::get('/delete/committeemember/{recordid}','deleteCommitteeMember')->name('delete.committeemember');   


   Route::get('/master/add/project','addProject')->name('add.project');   
   Route::post('/master/store/project/{recordid}','storeProject')->middleware('throttle:master_data')->name('store.project');
   Route::get('/master/project/html','getProjectData')->name('project.html');
   Route::get('/master/edit/project/{recordid}','editProject')->name('edit.project');
   Route::post('/master/update/project/{recordid}','storeProject')->name('update.project');
   Route::get('/delete/project/{recordid}','deleteProject')->name('delete.project');   


   Route::get('/master/add/state','addState')->name('add.state');   
   Route::post('/master/store/state/{recordid}','storeState')->middleware('throttle:master_data')->name('store.state');
   Route::get('/master/state/html','getStateData')->name('state.html');
   Route::get('/master/edit/state/{recordid}','editState')->name('edit.state');
   Route::post('/master/up/state/{recordid}','storeState')->name('up.state');
   Route::get('/delete/state/{recordid}','deleteState')->name('delete.state');   

   Route::get('/master/add/position','addPosition')->name('add.position');   
   Route::post('/master/store/position/{recordid}','storePosition')->middleware('throttle:master_data')->name('store.position');
   Route::get('/master/position/html','getPositionData')->name('position.html');
   Route::get('/master/edit/position/{recordid}','editPosition')->name('edit.position');
   Route::post('/master/update/position/{recordid}','storePosition')->name('update.position');
   Route::get('/delete/position/{recordid}','deletePosition')->name('delete.position');   

   Route::get('/master/add/sector','addSector')->name('add.sector');   
   Route::post('/master/store/sector/{recordid}','storeSector')->middleware('throttle:master_data')->name('store.sector');
   Route::get('/master/sector/html','getSectorData')->name('sector.html');
   Route::get('/master/edit/sector/{recordid}','editSector')->name('edit.sector');
   Route::post('/master/update/sector/{recordid}','storeSector')->name('update.sector');
   Route::get('/delete/sector/{recordid}','deleteSector')->name('delete.sector');   

   Route::get('/master/add/qualification','addQualification')->name('add.qualification');   
   Route::post('/master/store/qualification/{recordid}','storeQualification')->name('store.qualification');
   Route::get('/master/qualification/html','getQualificationData')->name('qualification.html');
   Route::get('/master/edit/qualification/{recordid}','editQualification')->name('edit.qualification');
   Route::post('/master/update/qualification/{recordid}','storeQualification')->name('update.qualification');
   Route::get('/delete/qualification/{recordid}','deleteQualification')->name('delete.qualification');   


   Route::get('/master/add/tiermaster','addTierMaster')->name('add.tiermaster');   
   Route::post('/master/store/tiermaster/{recordid}','storeTierMaster')->middleware('throttle:master_data')->name('store.tiermaster');
   Route::get('/master/tiermaster/html','getTierMasterData')->name('tiermaster.html');
   Route::get('/master/edit/tiermaster/{recordid}','editTierMaster')->name('edit.tiermaster');
   Route::post('/master/update/tiermaster/{recordid}','storeTierMaster')->name('update.tiermaster');
   Route::get('/delete/tiermaster/{recordid}','deleteTierMaster')->name('delete.tiermaster');   


   Route::get('/master/add/jobcategory','addJobCategory')->name('add.jobcategory');   
   Route::post('/master/store/jobcategory/{recordid}','storeJobCategory')->middleware('throttle:master_data')->name('store.jobcategory');
   Route::get('/master/jobcategory/html','getJobCategoryData')->name('jobcategory.html');
   Route::get('/master/edit/jobcategory/{recordid}','editJobCategory')->name('edit.jobcategory');
   Route::post('/master/update/jobcategory/{recordid}','storeJobCategory')->name('update.jobcategory');
   Route::get('/delete/jobcategory/{recordid}','deleteJobCategory')->name('delete.jobcategory');   


   Route::get('/master/add/remuneration','addRemuneration')->name('add.remuneration');   
   Route::post('/master/store/remuneration/{recordid}','storeRemuneration')->middleware('throttle:master_data')->name('store.remuneration');
   Route::get('/master/remuneration/html','getRemunerationData')->name('remuneration.html');
   Route::get('/master/edit/remuneration/{recordid}','editRemuneration')->name('edit.remuneration');
   Route::post('/master/update/remuneration/{recordid}','storeRemuneration')->name('update.remuneration');
   Route::get('/delete/remuneration/{recordid}','deleteRemuneration')->name('delete.remuneration');
   Route::get('/master/remuneration/form','getRemunerationForm')->name('remuneration.form');

   Route::get('/master/add/charges','addCharges')->name('add.charges');   
   Route::post('/master/store/charges/{recordid}','storeCharges')->middleware('throttle:master_data')->name('store.charges');
   Route::get('/master/charges/html','getChargesData')->name('charges.html');
   Route::get('/master/edit/charges/{recordid}','editCharges')->name('edit.charges');
   Route::post('/master/update/charges/{recordid}','storeCharges')->name('update.charges');
   Route::get('/delete/charges/{recordid}','deleteCharges')->name('delete.charges');   


   Route::get('/master/add/workexperience','addWorkExp')->name('add.workexperience');   
   Route::post('/master/store/workexperience/{recordid}','storeWorkExp')->middleware('throttle:master_data')->name('store.workexperience');
   Route::get('/master/workexperience/html','getWorkExpData')->name('workexperience.html');
   Route::get('/master/edit/workexperience/{recordid}','editWorkExp')->name('edit.workexperience');
   Route::post('/master/update/workexperience/{recordid}','storeWorkExp')->name('update.workexperience');
   Route::get('/delete/workexperience/{recordid}','deleteWorkExp')->name('delete.workexperience');   


   Route::get('/master/add/district','addDistrict')->name('add.district');   
   Route::post('/master/store/district/{recordid}','storeDistrict')->middleware('throttle:master_data')->name('store.district');
   Route::get('/master/district/html','getDistrictData')->name('district.html');
   Route::get('/master/edit/district/{recordid}','editDistrict')->name('edit.district');
   Route::post('/master/update/district/{recordid}','storeDistrict')->name('update.district');
   Route::get('/delete/district/{recordid}','deleteDistrict')->name('delete.district');   

   Route::get('/master/add/tehsil','addTehsil')->name('add.tehsil');   
   Route::post('/master/store/tehsil/{recordid}','storeTehsil')->middleware('throttle:master_data')->name('store.tehsil');
   Route::get('/master/tehsil/html','getTehsilData')->name('tehsil.html');
   Route::get('/master/edit/tehsil/{recordid}','editTehsil')->name('edit.tehsil');
   Route::post('/master/update/tehsil/{recordid}','storeTehsil')->name('update.tehsil');
   Route::get('/delete/tehsil/{recordid}','deleteTehsil')->name('delete.tehsil');   

   Route::get('/master/add/block','addBlock')->name('add.block');   
   Route::post('/master/store/block/{recordid}','storeBlock')->middleware('throttle:master_data')->name('store.block');
   Route::get('/master/block/html','getBlockData')->name('block.html');
   Route::get('/master/edit/block/{recordid}','editBlock')->name('edit.block');
   Route::post('/master/update/block/{recordid}','storeBlock')->name('update.block');
   Route::get('/delete/block/{recordid}','deleteBlock')->name('delete.block');   

   Route::get('/master/add/tier','addTier')->name('add.tier');   
   Route::post('/master/store/tier/{recordid}','storeTier')->name('store.tier');
   Route::get('/master/tier/html','getTierData')->name('tier.html');
   Route::get('/master/edit/tier/{recordid}','editTier')->name('edit.tier');
   Route::post('/master/update/tier/{recordid}','storeTier')->name('update.tier');
   Route::get('/delete/tier/{recordid}','deleteTier')->name('delete.tier');

   Route::get('/master/add/payouttype','addPayoutType')->name('add.payouttype');   
   Route::post('/master/store/payouttype/{recordid}','storePayoutType')->name('store.payouttype');
   Route::get('/master/payouttype/html','getPayoutTypeData')->name('payouttype.html');
   Route::get('/master/edit/payouttype/{recordid}','editPayoutType')->name('edit.payouttype');
   Route::post('/master/update/payouttype/{recordid}','storePayoutType')->name('update.payouttype');
   Route::get('/delete/payouttype/{recordid}','deletePayoutType')->name('delete.payouttype');

   Route::get('/master/add/objective','addObjective')->name('add.objective');   
   Route::post('/master/store/objective/{recordid}','storeObjective')->name('store.objective');
   Route::get('/master/objective/html','getObjectiveData')->name('objective.html');
   Route::get('/master/edit/objective/{recordid}','editObjective')->name('edit.objective');
   Route::post('/master/update/objective/{recordid}','storeObjective')->name('update.objective');
   Route::get('/delete/objective/{recordid}','deleteObjective')->name('delete.objective');
   Route::get('/master/list/objectives','getObjectiveList')->name('objectives.list');   

   Route::get('/master/add/payoutstructure','addPayoutStructure')->name('add.payoutstructure');   
   Route::get('/master/store/payoutstructure','storePayoutStructure')->name('store.payoutstructure');
   Route::get('/master/remove/payoutstructure','removePayoutStructure')->name('remove.payoutstructure');
   Route::get('/master/payoutstructure/html','getPayoutStructureData')->name('payoutstructure.html');
   Route::get('/master/edit/payoutstructure/{recordid}','editPayoutStructure')->name('edit.payoutstructure');
   Route::get('/delete/payoutstructure/{recordid}','deletePayoutStructure')->name('delete.payoutstructure');
   Route::get('/master/payoutstructure/list','getPayoutStructureList')->name('payoutstructure.list');
   
   Route::get('/master/add/city','addCity')->name('add.city');   
   Route::post('/master/store/city/{recordid}','storeCity')->middleware('throttle:master_data')->name('store.city');
   Route::get('/master/city/html','getCityData')->name('city.html');
   Route::get('/master/edit/city/{recordid}','editCity')->name('edit.city');
   Route::post('/master/update/city/{recordid}','storeCity')->name('update.city');
   Route::get('/delete/city/{recordid}','deleteCity')->name('delete.city');   
   Route::get('/master/setdefault/city','setDefaultCity')->name('setdefault.city');   
   Route::get('/master/setoperatingstatus/city','setOperatingCityStatus')->name('setoperatingstatus.city');
   
   Route::get('/master/add/cityslider','addCitySlider')->name('add.cityslider');   
   Route::post('/master/store/cityslider/{recordid}','storeCitySlider')->name('store.cityslider');
   Route::get('/master/cityslider/html','getCitySliderData')->name('cityslider.html');
   Route::get('/master/edit/cityslider/{recordid}','editCitySlider')->name('edit.cityslider');
   Route::post('/master/update/cityslider/{recordid}','storeCitySlider')->name('update.cityslider');
   Route::get('/delete/cityslider/{recordid}','deleteCitySlider')->name('delete.cityslider');   
   Route::get('/master/setactive/cityslider','setActiveInactiveCitySlider')->name('setactive.cityslider');
   Route::get('/master/setdisplay/cityslider','setDisplayCitySlider')->name('setdisplay.cityslider');

   Route::get('/master/add/tax','addTax')->name('add.tax');
   Route::post('/master/store/tax/{recordid}','storeTax')->name('store.tax');
   Route::get('/master/tax/html','getTaxData')->name('tax.html');
   Route::get('/master/edit/tax/{recordid}','editTax')->name('edit.tax');
   Route::post('/master/update/tax/{recordid}','storeTax')->name('update.tax');
   Route::get('/delete/tax/{recordid}','deleteTax')->name('delete.tax');   


   Route::get('/master/add/area','addArea')->name('add.area');   
   Route::post('/master/store/area/{recordid}','storeArea')->name('store.area');
   Route::get('/master/area/html','getAreaData')->name('area.html');
   Route::get('/master/edit/area/{recordid}','editArea')->name('edit.area');
   Route::post('/master/update/area/{recordid}','storeArea')->name('update.area');
   Route::get('/delete/area/{recordid}','deleteArea')->name('delete.area');   
   
   Route::get('/master/add/bank','addBank')->name('add.bank');
   Route::post('/master/store/bank/{recordid}','storeBank')->name('store.bank');
   Route::get('/master/bank/html','getBankData')->name('bank.html');
   Route::get('/master/edit/bank/{recordid}','editBank')->name('edit.bank');
   Route::post('/master/update/bank/{recordid}','storeBank')->name('update.bank');
   Route::get('/delete/bank/{recordid}','deleteBank')->name('delete.bank');   


   Route::get('/master/add/facing','addFacing')->name('add.facing');   
   Route::post('/master/store/facing/{recordid}','storeFacing')->name('store.facing');
   Route::get('/master/facing/html','getFacingData')->name('facing.html');
   Route::get('/master/edit/facing/{recordid}','editFacing')->name('edit.facing');
   Route::post('/master/update/facing/{recordid}','storeFacing')->name('update.facing');
   Route::get('/delete/facing/{recordid}','deleteFacing')->name('delete.facing');   

   Route::get('/master/add/inquirystatus','addInquiryStatus')->name('add.inquirystatus');   
   Route::post('/master/store/inquirystatus/{recordid}','storeInquiryStatus')->name('store.inquirystatus');
   Route::get('/master/inquirystatus/html','getInquiryStatusData')->name('inquirystatus.html');
   Route::get('/master/edit/inquirystatus/{recordid}','editInquiryStatus')->name('edit.inquirystatus');
   Route::post('/master/update/inquirystatus/{recordid}','storeInquiryStatus')->name('update.inquirystatus');
   Route::get('/delete/inquirystatus/{recordid}','deleteInquiryStatus')->name('delete.inquirystatus');   

   Route::get('/master/add/documenttype','addDocumentType')->name('add.documenttype');   
   Route::post('/master/store/documenttype/{recordid}','storeDocumentType')->name('store.documenttype');
   Route::get('/master/documenttype/html','getDocumentTypeData')->name('documenttype.html');
   Route::get('/master/edit/documenttype/{recordid}','editDocumentType')->name('edit.documenttype');
   Route::post('/master/update/documenttype/{recordid}','storeDocumentType')->name('update.documenttype');
   Route::get('/delete/documenttype/{recordid}','deleteDocumentType')->name('delete.documenttype');   

   Route::get('/master/add/flattype','addFlatType')->name('add.flattype');   
   Route::post('/master/store/flattype/{recordid}','storeFlatType')->name('store.flattype');
   Route::get('/master/flattype/html','getFlatTypeData')->name('flattype.html');
   Route::get('/master/edit/flattype/{recordid}','editFlatType')->name('edit.flattype');
   Route::post('/master/update/flattype/{recordid}','storeFlatType')->name('update.flattype');
   Route::get('/delete/flattype/{recordid}','deleteFlatType')->name('delete.flattype');   

   Route::get('/master/add/furnishedtype','addFurnishedType')->name('add.furnishedtype');   
   Route::post('/master/store/furnishedtype/{recordid}','storeFurnishedType')->name('store.furnishedtype');
   Route::get('/master/furnishedtype/html','getFurnishedTypeData')->name('furnishedtype.html');
   Route::get('/master/edit/furnishedtype/{recordid}','editFurnishedType')->name('edit.furnishedtype');
   Route::post('/master/update/furnishedtype/{recordid}','storeFurnishedType')->name('update.furnishedtype');
   Route::get('/delete/furnishedtype/{recordid}','deleteFurnishedType')->name('delete.furnishedtype');   

   Route::get('/master/add/rowhousetype','addRowhouseType')->name('add.rowhousetype');   
   Route::post('/master/store/rowhousetype/{recordid}','storeRowhouseType')->name('store.rowhousetype');
   Route::get('/master/rowhousetype/html','getRowhouseTypeData')->name('rowhousetype.html');
   Route::get('/master/edit/rowhousetype/{recordid}','editRowhouseType')->name('edit.rowhousetype');
   Route::post('/master/update/rowhousetype/{recordid}','storeRowhouseType')->name('update.rowhousetype');
   Route::get('/delete/rowhousetype/{recordid}','deleteRowhouseType')->name('delete.rowhousetype');   

   Route::get('/master/add/leadsource','addLeadSource')->name('add.leadsource');   
   Route::post('/master/store/leadsource/{recordid}','storeLeadSource')->name('store.leadsource');
   Route::get('/master/leadsource/html','getLeadSourceData')->name('leadsource.html');
   Route::get('/master/edit/leadsource/{recordid}','editLeadSource')->name('edit.leadsource');
   Route::post('/master/update/leadsource/{recordid}','storeLeadSource')->name('update.leadsource');
   Route::get('/delete/leadsource/{recordid}','deleteLeadSource')->name('delete.leadsource');   

   Route::get('/master/add/projectstatus','addProjectStatus')->name('add.projectstatus');   
   Route::post('/master/store/projectstatus/{recordid}','storeProjectStatus')->name('store.projectstatus');
   Route::get('/master/projectstatus/html','getProjectStatusData')->name('projectstatus.html');
   Route::get('/master/edit/projectstatus/{recordid}','editProjectStatus')->name('edit.projectstatus');
   Route::post('/master/update/projectstatus/{recordid}','storeProjectStatus')->name('update.projectstatus');
   Route::get('/delete/projectstatus/{recordid}','deleteProjectStatus')->name('delete.projectstatus');   

   Route::get('/master/add/amenities','addAmenity')->name('add.amenities');   
   Route::post('/master/store/amenities/{recordid}','storeAmenity')->name('store.amenities');
   Route::get('/master/amenities/html','getAmenityData')->name('amenities.html');
   Route::get('/master/edit/amenities/{recordid}','editAmenity')->name('edit.amenities');
   Route::post('/master/update/amenities/{recordid}','storeAmenity')->name('update.amenities');
   Route::get('/delete/amenities/{recordid}','deleteAmenity')->name('delete.amenities');   

   Route::get('/master/add/assettype','addAssetType')->name('add.assettype');   
   Route::post('/master/store/assettype/{recordid}','storeAssetType')->name('store.assettype');
   Route::get('/master/assettype/html','getAssetTypeData')->name('assettype.html');
   Route::get('/master/edit/assettype/{recordid}','editAssetType')->name('edit.assettype');
   Route::post('/master/update/assettype/{recordid}','storeAssetType')->name('update.assettype');
   Route::get('/delete/assettype/{recordid}','deleteAssetType')->name('delete.assettype');   

   Route::get('/master/add/deductiontype','addDeductionType')->name('add.deductiontype');   
   Route::post('/master/store/deductiontype/{recordid}','storeDeductionType')->name('store.deductiontype');
   Route::get('/master/deductiontype/html','getDeductionTypeData')->name('deductiontype.html');
   Route::get('/master/edit/deductiontype/{recordid}','editDeductionType')->name('edit.deductiontype');
   Route::post('/master/update/deductiontype/{recordid}','storeDeductionType')->name('update.deductiontype');
   Route::get('/delete/deductiontype/{recordid}','deleteDeductionType')->name('delete.deductiontype');   

   Route::get('/master/add/unit','addUnit')->name('add.unit');
   Route::post('/master/store/unit/{recordid}','storeUnit')->name('store.unit');
   Route::get('/master/unit/html','getUnitData')->name('unit.html');
   Route::get('/master/edit/unit/{recordid}','editUnit')->name('edit.unit');
   Route::post('/master/update/unit/{recordid}','storeUnit')->name('update.unit');
   Route::get('/delete/unit/{recordid}','deleteUnit')->name('delete.unit');   


   Route::get('/master/add/statuscolor','addStatusColor')->name('add.statuscolor');
   Route::post('/master/store/statuscolor/{recordid}','storeStatusColor')->name('store.statuscolor');
   Route::get('/master/statuscolor/html','getStatusColorData')->name('statuscolor.html');
   Route::get('/master/edit/statuscolor/{recordid}','editStatusColor')->name('edit.statuscolor');
   Route::post('/master/update/statuscolor/{recordid}','storeStatusColor')->name('update.statuscolor');
   Route::get('/delete/statuscolor/{recordid}','deleteStatusColor')->name('delete.statuscolor');   

   Route::get('/master/add/quota','addQuota')->name('add.quota');
   Route::post('/master/store/quota/{recordid}','storeQuota')->name('store.quota');
   Route::get('/master/quota/html','getQuotaData')->name('quota.html');
   Route::get('/master/edit/quota/{recordid}','editQuota')->name('edit.quota');
   Route::post('/master/update/quota/{recordid}','storeQuota')->name('update.quota');
   Route::get('/delete/quota/{recordid}','deleteQuota')->name('delete.quota');   

   Route::get('/master/add/priority','addPriority')->name('add.priority');
   Route::post('/master/store/priority/{recordid}','storePriority')->name('store.priority');
   Route::get('/master/priority/html','getPriorityData')->name('priority.html');
   Route::get('/master/edit/priority/{recordid}','editPriority')->name('edit.priority');
   Route::post('/master/update/priority/{recordid}','storePriority')->name('update.priority');
   Route::get('/delete/priority/{recordid}','deletePriority')->name('delete.priority');   

   Route::get('/master/add/leadstatus','addLeadStatus')->name('add.leadstatus');
   Route::post('/master/store/leadstatus/{recordid}','storeLeadStatus')->name('store.leadstatus');
   Route::get('/master/leadstatus/html','getLeadStatusData')->name('leadstatus.html');
   Route::get('/master/edit/leadstatus/{recordid}','editLeadStatus')->name('edit.leadstatus');
   Route::post('/master/update/leadstatus/{recordid}','storeLeadStatus')->name('update.leadstatus');
   Route::get('/delete/leadstatus/{recordid}','deleteLeadStatus')->name('delete.leadstatus');   

   Route::get('/master/add/callstatus','addCallStatus')->name('add.callstatus');
   Route::post('/master/store/callstatus/{recordid}','storeCallStatus')->name('store.callstatus');
   Route::get('/master/callstatus/html','getCallStatusData')->name('callstatus.html');
   Route::get('/master/edit/callstatus/{recordid}','editCallStatus')->name('edit.callstatus');
   Route::post('/master/update/callstatus/{recordid}','storeCallStatus')->name('update.callstatus');
   Route::get('/delete/callstatus/{recordid}','deleteCallStatus')->name('delete.callstatus');   

   Route::get('/master/add/ownerstatus','addOwnerStatus')->name('add.ownerstatus');
   Route::post('/master/store/ownerstatus/{recordid}','storeOwnerStatus')->name('store.ownerstatus');
   Route::get('/master/ownerstatus/html','getOwnerStatusData')->name('ownerstatus.html');
   Route::get('/master/edit/ownerstatus/{recordid}','editOwnerStatus')->name('edit.ownerstatus');
   Route::post('/master/update/ownerstatus/{recordid}','storeOwnerStatus')->name('update.ownerstatus');
   Route::get('/delete/ownerstatus/{recordid}','deleteOwnerStatus')->name('delete.ownerstatus');   

   Route::get('/master/add/paymentmode','addPaymentMode')->name('add.paymentmode');
   Route::post('/master/store/paymentmode/{recordid}','storePaymentMode')->name('store.paymentmode');
   Route::get('/master/paymentmode/html','getPaymentModeData')->name('paymentmode.html');
   Route::get('/master/edit/paymentmode/{recordid}','editPaymentMode')->name('edit.paymentmode');
   Route::post('/master/update/paymentmode/{recordid}','storePaymentMode')->name('update.paymentmode');
   Route::get('/delete/paymentmode/{recordid}','deletePaymentMode')->name('delete.paymentmode');   

   Route::get('/master/add/slider','addSlider')->name('add.slider');
   Route::post('/master/store/slider/{recordid}','storeSlider')->name('store.slider');
   Route::get('/master/slider/html','getSliderData')->name('slider.html');
   Route::get('/master/edit/slider/{recordid}','editSlider')->name('edit.slider');   
   Route::post('/master/update/slider/{recordid}','storeSlider')->name('update.slider');
   Route::get('/delete/slider/{recordid}','deleteSlider')->name('delete.slider');   
   
   Route::get('/master/add/subcategory','addSubCategory')->name('add.subcategory');
   Route::post('/master/store/subcategory/{recordid}','storeSubCategory')->name('store.subcategory');   
   Route::get('/master/subcategory/html','getSubCategoryData')->name('subcategory.html');
   Route::get('/master/edit/subcategory/{recordid}','editSubCategory')->name('edit.subcategory');
   Route::post('/master/update/subcategory/{recordid}','storeSubCategory')->name('update.subcategory');
   Route::get('/delete/subcategory/{recordid}','deleteSubCategory')->name('delete.subcategory');

   Route::get('/master/add/slot','addSlot')->name('add.slot');
   Route::post('/master/store/slot/{recordid}','storeSlot')->name('store.slot');   
   Route::get('/master/slot/html','getSlotData')->name('slot.html');
   Route::get('/master/edit/slot/{recordid}','editSlot')->name('edit.slot');
   Route::post('/master/update/slot/{recordid}','storeSlot')->name('update.slot');
   Route::get('/delete/slot/{recordid}','deleteSlot')->name('delete.slot');

   Route::get('/master/add/item','addItem')->name('add.item');
   Route::post('/master/store/item/{recordid}','storeItem')->name('store.item');   
   Route::get('/master/item/html','getItemData')->name('item.html');
   Route::get('/master/edit/item/{recordid}','editItem')->name('edit.item');
   Route::post('/master/update/item/{recordid}','storeItem')->name('update.item');
   Route::get('/delete/item/{recordid}','deleteItem')->name('delete.item');
   


});



Route::controller(MenuController::class)->group( function() {
   Route::get('/master/add/menu','addMenu')->middleware('isLoggedIn')->name('add.menu');
   Route::post('/master/store/menu/{recordid}','storeMenu')->middleware('isLoggedIn')->name('store.menu');
   Route::get('/master/menu/html','getMenuData')->middleware('isLoggedIn')->name('menu.html');
   Route::get('/master/edit/menu/{recordid}','editMenu')->middleware('isLoggedIn')->name('edit.menu');
   Route::post('/master/update/menu/{recordid}','storeMenu')->middleware('isLoggedIn')->name('update.menu');
   Route::get('/delete/menu/{recordid}','deleteMenu')->middleware('isLoggedIn')->name('delete.menu');   
   
   Route::get('/master/add/action','addAction')->middleware('isLoggedIn')->name('add.action');   
   Route::post('/master/store/action/{recordid}','storeAction')->middleware('isLoggedIn')->name('store.action');
   Route::get('/master/action/html','getActionData')->middleware('isLoggedIn')->name('action.html');
   Route::get('/master/edit/action/{recordid}','editAction')->middleware('isLoggedIn')->name('edit.action');
   Route::post('/master/update/action/{recordid}','storeAction')->middleware('isLoggedIn')->name('update.action');
   Route::get('/delete/action/{recordid}','deleteAction')->middleware('isLoggedIn')->name('delete.action');   
   
   Route::get('/master/sync/menupermission','syncMenuPermission')->middleware('isLoggedIn')->name('sync.menupermission');
   Route::get('/master/add/actionpermission','addActionPermission')->middleware('isLoggedIn')->name('add.actionpermission');
   Route::post('/master/store/actionpermission/{recordid}','storeActionPermission')->middleware('isLoggedIn')->name('store.actionpermission');
   Route::get('/master/actionpermission/html','getActionPermissionData')->middleware('isLoggedIn')->name('actionpermission.html');
   Route::get('/master/edit/actionpermission/{recordid}','editActionPermission')->middleware('isLoggedIn')->name('edit.actionpermission');
   Route::post('/master/update/actionpermission/{recordid}','storeActionPermission')->middleware('isLoggedIn')->name('update.actionpermission');
   Route::get('/delete/actionpermission/{recordid}','deleteActionPermission')->middleware('isLoggedIn')->name('delete.actionpermission');   
   
   Route::get('/master/add/rolepermission','addRolePermission')->middleware('isLoggedIn')->name('add.rolepermission');
   Route::get('/master/rolepermission/html','getRolePermissionData')->middleware('isLoggedIn')->name('rolepermission.html');
   Route::post('/master/saveRolePermission','saveRolePermission')->middleware('isLoggedIn')->name('save.role.permission');
   
});

// MANAGEMENT RELATED LINKS
Route::middleware(['isLoggedIn','check.method', 'role:ADMIN'])->controller(ManagementController::class)->group(function()
{
   Route::get('/master/add/permission','addPermission')->name('add.permission');   
   Route::get('/master/permission/html','getPermissionData')->name('permission.html');
   Route::post('/master/store/permission/{recordid}','storePermission')->name('store.permission');

   Route::get('/master/add/user','addUser')->name('add.user');   
   Route::post('/master/store/user/{recordid}','storeUser')->name('store.user');
   Route::get('/master/users/html','getUserData')->name('users.html');
   Route::get('/master/edit/user/{recordid}','editUser')->name('edit.user');
   Route::post('/master/update/user/{recordid}','storeUser')->name('update.user');
   Route::get('/delete/user/{recordid}','deleteUser')->name('delete.user');   
   Route::get('/master/departments/list','getDepartmentsList')->name('departments.list');


   Route::get('/master/add/branch','addBranch')->name('add.branch');   
   Route::post('/master/store/branch/{recordid}','storeBranch')->name('store.branch');
   Route::get('/master/branch/html','getBranchData')->name('branch.html');
   Route::get('/master/edit/branch/{recordid}','editBranch')->name('edit.branch');
   Route::post('/master/update/branch/{recordid}','storeBranch')->name('update.branch');
   Route::get('/delete/branch/{recordid}','deleteBranch')->name('delete.branch');   

   Route::get('/master/add/designation','addDesignation')->name('add.designation');   
   Route::post('/master/store/designation/{recordid}','storeDesignation')->name('store.designation');
   Route::get('/master/designation/html','getDesignationData')->name('designation.html');
   Route::get('/master/edit/designation/{recordid}','editDesignation')->name('edit.designation');
   Route::post('/master/update/designation/{recordid}','storeDesignation')->name('update.designation');
   Route::get('/delete/designation/{recordid}','deleteDesignation')->name('delete.designation');   

   Route::get('/master/add/department','addDepartment')->name('add.department');   
   Route::post('/master/store/department/{recordid}','storeDepartment')->name('store.department');
   Route::get('/master/department/html','getDepartmentData')->name('department.html');
   Route::get('/master/edit/department/{recordid}','editDepartment')->name('edit.department');
   Route::post('/master/update/department/{recordid}','storeDepartment')->name('update.department');
   Route::get('/delete/department/{recordid}','deleteDepartment')->name('delete.department');   
   
   Route::get('/master/set/userpermission/{recordid}','setUserPermission')->name('set.userpermission');   
   Route::get('/master/userpermission/html','getUserPermissionData')->middleware('isLoggedIn')->name('userpermission.html');
   Route::post('/master/saveUserPermission','saveUserPermission')->middleware('isLoggedIn');
   Route::post('/master/saveUserRolePermission','saveUserRolePermission')->middleware('isLoggedIn');

});



// STATE-CITY-LOCATION MASTER RELATED LINKS
Route::middleware(['isLoggedIn','check.method','role:ADMIN'])->controller(StateCityController::class)->group(function()
{
   Route::get('/master/store/state','storeState')->name('store.statename');   
   Route::get('/master/store/city','storeCity')->name('store.cityname');
   Route::get('/master/store/area','storeArea')->name('store.areaname');
   
   Route::get('/master/store/needvalue','storeNeedValue')->name('store.needvalue');   
   Route::get('/master/store/includevalue','storeIncludeValue')->name('store.includevalue');   
   
   
   Route::get('/master/list/prod','getProdList')->name('prod.list');
});


Route::middleware(['isLoggedIn','role:ADMIN,DEPARTMENT,VENDOR,PROJECT MANAGER'])->controller(ViewFileController::class)->group(function()
{
   Route::get('/view/uploadedfile/{encrypted}','showFile')->name('view.uploadedfile');   
});

   
Route::middleware(['isLoggedIn','role:ADMIN,DEPARTMENT,VENDOR,PROJECT MANAGER,RESOURCE'])->controller(PasswordController::class)->group(function()
{
   Route::get('/master/generate/password','generatePassword')->name('generate.password');
   Route::get('/master/reset/deployment','resetDeployment')->name('reset.deployment');
   Route::get('/master/approve/deployment','approveDeployment')->name('approve.deployment');
   Route::get('/master/set/issuancedate','setOrderIssuanceDate')->name('set.issuancedate');
   
   Route::get('/master/set/newordervalue','setNewOrderValue')->name('set.newordervalue');
   
   Route::get('/master/change/password','changePassword')->name('change.password');
   Route::post('/master/store/password','storePassword')->name('store.password');
   Route::get('/master/create/resources','addResources')->name('create.resources');
   
   Route::get('/master/update/remunerationprice','UpdateRemunerationPrice')->name('update.remunerationprice');
   Route::get('/master/update/resourceprice','UpdateResourcePrice')->name('update.resourceprice');
});


Route::middleware(['isLoggedIn','role:ADMIN,DEPARTMENT,VENDOR,PROJECT MANAGER'])->controller(UtilityController::class)->group(function()
{
	Route::get('/master/get/updates/{fieldname?}/{requestid?}','getUpdates')->name('get.updates');
	Route::get('/master/create/resources','addResources')->name('create.resources');
	
	Route::get('/master/update/resourcetracking','updateResourceTracking')->name('update.resourcetracking');
	
	Route::get('/master/price/comparison/{isTemp?}/{requestid?}','tierWisePriceComparison')->name('price.comparison');
	
	Route::post('/master/price/comparison/{isTemp?}/{requestid?}', 'showTierWisePriceComparison')
    ->name('showprice.comparison');
	
	Route::get('/master/calculate/eoiordervalue','calculateEoIValue')->name('calculate.eoiordervalue');
	
	Route::get('/master/calculate/ordervalue','calculateOrderValue')->name('calculate.ordervalue');
	
	Route::get('/master/calculate/oldereoiordervalue','calculateOldEoIValue')->name('calculate.oldereoiordervalue');


	Route::post('/master/show/workordervalue/{orderid?}','showWorkOrderValue')->name('show.workordervalue');   	
	
	Route::get('/master/show/logs','showLogs')->name('show.logs');
	Route::get('/master/delete/logs','deleteLogs')->name('delete.logs');

});


Route::controller(StateCityController::class)->group( function() {

   Route::get('/master/list/includes','getIncludeList')->name('includes.list');

   Route::get('/master/list/are','getAreaList')->name('area.list');
   Route::get('/master/list/areas','getAreaList')->name('areas.list');

   Route::get('/master/list/state','getStateList')->name('state.list');
   Route::get('/master/list/city','getCityList')->name('cities.list');
   Route::get('/master/list/area','getAreaNameList')->name('areaname.list');
   Route::get('/master/list/area','getAreaNamesList')->name('areanames.list');
   Route::get('/master/list/subhead','getSubHeadList')->name('subhead.list');
	
});

Route::controller(MasterController::class)->group( function()
{
   Route::get('/master/list/tier','getTierList')->name('tiers.list');
   Route::get('/master/list/tierole','getTiersList')->name('tierole.list');
   Route::get('/master/list/experience','getExperienceList')->name('experiences.list');
   Route::get('/master/list/positions','getPositionList')->name('positions.list');
   Route::get('/master/get/remuneration','getRemunerationAmount')->name('get.remuneration');
   Route::get('/master/get/consultantremuneration','getConsultantRemunerationAmount')->name('get.consultantremuneration');
   Route::get('/master/load/eoidata','loadEoiData')->name('load.eoidata');
	
});

// STATE-CITY-LOCATION MASTER RELATED LINKS
Route::middleware(['isLoggedIn','check.method','role:ADMIN'])->controller(UserController::class)->group(function()
{
   Route::get('/master/add/appuser','addAppUser')->name('add.appuser');   
   Route::post('/master/store/appuser/{recordid}','storeAppUser')->name('store.appuser');
   Route::get('/master/appuser/html','getAppUserData')->name('appuser.html');
   Route::get('/master/edit/appuser/{recordid}','editAppUser')->name('edit.appuser');
   Route::post('/master/update/state/{recordid}','storeAppUser')->name('update.appuser');
   Route::get('/delete/appuser/{recordid}','deleteAppUser')->name('delete.appuser');   
});



// AREA LOCATION MASTER RELATED LINKS
Route::middleware(['isLoggedIn','check.method','role:ADMIN'])->controller(ProfileController::class)->group(function()
{
   
   Route::get('/edit/profile/{recordid}','editData')->name('edit.profile');
   Route::post('/update/profile/{recordid}','updateData')->name('update.profile');

   Route::get('/edit/password/{recordid}','editPassword')->name('edit.password');
   Route::post('/update/password/{recordid}','updatePassword')->name('update.password');

   Route::get('/edit/aboutus/','editAbout')->name('edit.aboutus');
   Route::post('/update/aboutus/{recordid}','updateAbout')->name('update.aboutus');
   Route::get('/edit/terms/','editTerm')->name('edit.terms');
   Route::post('/update/terms/{recordid}','updateTerm')->name('update.terms');

   Route::get('/edit/privacy/','editPrivacy')->name('edit.privacy');
   Route::post('/update/privacy/{recordid}','updatePrivacy')->name('update.privacy');

   Route::get('/add/form','addData')->name('add.form');   
   
   Route::post('/master/send/otpsms','sendOtpMessage')->name('send.otpsms');
   
   Route::get('/master/application/settings','applicationSettings')->name('application.settings');
   Route::post('/master/update/settings','updateApplicationSettings')->name('update.settings');
   Route::post('/master/send/expiry/notification','sendWorkOrderExpiryNotification')->name('send.work.order.expiry.notification');
});


Route::middleware(['isLoggedIn','check.method','role:ADMIN,DEPARTMENT'])->controller(OrderValueController::class)->group(function()
{
	Route::post('/master/get/calculation','getCalculation')->name('get.calculation');
});


Route::middleware(['isLoggedIn','check.method','role:ADMIN'])->controller(AdminPreBidQuery::class)->group(function()
{
	Route::post('/master/view/allprebidquery','viewAllPreBidQuery')->name('view.allprebidquery');
	Route::post('/master/forwardto/department','forwardToDepartment')->name('forwardto.department');
	Route::post('/master/publishto/firms','publishToFirms')->name('publishto.firms');
	Route::post('/master/view/brodcasted','viewBrodcastedPreBidQuery')->name('view.brodcasted');
	Route::get('/master/download/prebidqueries/{requestid?}','downloadPreBidQuery')->name('download.prebidqueries');
});

Route::middleware(['isLoggedIn','check.method','role:DEPARTMENT,PROJECT MANAGER'])->controller(AdminPreBidQuery::class)->group(function()
{
   Route::get('/master/download/deptpmprebid/{requestid?}','downloadDeptPmPreBid')->name('download.deptpmprebid');
});


Route::middleware(['isLoggedIn','check.method','role:ADMIN,DEPARTMENT,PROJECT MANAGER'])->controller(CommitteeMember::class)->group(function()
{
	Route::post('/master/eoi/committeemember','viewEoiCommitteeMember')->name('eoi.committeemember');
	Route::post('/master/eoicommitteemember/update','updateEoICommitteeMember')->name('eoicommitteemember.update');
	Route::post('/master/eoicommitteemember/remove','removeEoICommitteeMember')->name('eoicommitteemember.remove');
	Route::post('/master/eoicommitteemember/add','addEoICommitteeMember')->name('eoicommitteemember.add');
});


Route::middleware(['isLoggedIn','check.method', 'role:ADMIN'])->controller(CalendarController::class)->group(function()
{
	Route::get('/master/create/holiday','createHoliday')->name('create.holiday');
	Route::post('/master/store/holiday/{recordid?}','storeHoliday')->name('store.holiday');
	Route::get('/master/holiday/html','holidayData')->name('holiday.html');
	Route::get('/master/edit/holiday/{recordid?}','editHoliday')->name('edit.holiday');
	Route::get('/master/delete/holiday/{recordid?}','storeHoliday')->name('delete.holiday');
	
	Route::get('/master/calendar/view','calendarView')->name('calendar.view');	
});

Route::middleware(['isLoggedIn','check.method', 'role:ADMIN'])->controller(RemunerationController::class)->group(function()
{
	Route::get('/master/add/ratelist','manageRemuneration')->name('add.ratelist');
	Route::get('/master/create/ratelist/{rateid?}','createRateList')->name('create.ratelist');
	Route::post('/master/add/store','storeRateList')->name('store.ratelist');
});


Route::middleware(['isLoggedIn','check.method','role:ADMIN'])->controller(NotificationController::class)->group(function()
{
	Route::get('/master/send/notification','sendNotification')->name('send.notification');
	Route::post('/master/store/notification','storeNotification')->name('store.notification');
	Route::get('/master/notification/html','getNotificationData')->name('notification.html');	
});


Route::middleware(['isLoggedIn','check.method','role:PROJECT MANAGER,DEPARTMENT,VENDOR'])->controller(NotificationController::class)->group(function()
{
	Route::get('/master/view/notifications/{notificationid?}','viewNotifications')->name('view.notifications');
	Route::get('/master/viewnotification/html','getNotificationsData')->name('viewnotification.html');	
});

Route::middleware(['isLoggedIn','check.method', 'role:ADMIN'])->controller(LoiBasedWorkOrderController::class)->group(function()
{
	Route::get('/master/loibased/order/{categoryid?}','addLoIWorkOrder')->name('loibased.order');
	Route::post('/master/store/directworkorder','storeDirectWorkOrder')->name('store.directworkorder');
	Route::get('/master/directorderconfirmation/{orderid}','confirmDirectOrder')->name('confirmed.finaldirectworkorder');
});


Route::middleware(['isLoggedIn','check.method', 'role:ADMIN'])->controller(ExtensionController::class)->group(function()
{
	Route::get('/workorder/extensionform/{orderid?}','showExtendForm')->name('workorder.extension.form');
	Route::post('/workorder/extend/{orderid?}','extendWorkOrder')->name('extend.workorder');
});

Route::middleware(['isLoggedIn','check.method', 'role:ADMIN'])->controller(ReportingController::class)->group(function()
{
	Route::get('/reporting/eoisummary','summaryDetail')->name('eoi.summary');

	Route::get('/reporting/summary/html','getSummaryData')->name('summary.html');
	
	Route::get('/reporting/deptwise/resources','departmentResources')->name('deptwise.resources');
	Route::get('/reporting/departmentwiseresource/html','getDepartmentWiseResourceData')->name('departmentwiseresource.html');
	
	Route::post('/reporting/resource/report','getResourceList')->name('resource.report');
	
	Route::post('/reporting/awdresource/report','getAwdResourceList')->name('awdresource.report');
	
	Route::post('/reporting/vendors/by/category','getVendorsByCategory')->name('vendors.by.category');
	
	
	Route::get('/reporting/undeployed/resources','undeployedResources')->name('undeployed.resources');
	Route::get('/reporting/undeployedresource/html','getUndeployedResourceData')->name('undeployedresource.html');

	Route::post('/reporting/undeployed/report','getUndeployedResourceList')->name('undeployed.report');
	Route::post('/reporting/awdundeployed/report','getAwdUndeployedResourceList')->name('awdundeployed.report');

	Route::post('/reporting/show/summarydetail','getEoISummaryDetail')->name('show.summarydetail');	
	
	Route::get('/reporting/eoiorder/value','eoiOrderValues')->name('eoiorder.value');
	Route::post('/reporting/eoiordervalue/html','getEoiOrderValueData')->name('eoiordervalue.html');
	Route::post('/reporting/firmwise/eoiordervalue','getFirmWiseEoiOrderValueData')->name('firmwise.eoiordervalue');
	Route::post('/reporting/firmwise/testdata','testFirmWiseEoiOrderValueData')->name('firmwise.testdata');
	Route::post('/reporting/firmwise/testdata/export','testFirmWiseEoiOrderValueExport')->name('firmwise.testdata.export');
	
	Route::post('/reporting/firmwise/eoiorderresource','getFirmWiseEoiOrderResourceData')->name('firmwise.eoiorderresource');
});

Route::middleware(['isLoggedIn','role:ADMIN'])->controller(FinanceController::class)->group(function()
{
   Route::get('/master/project/summary','getProjectSummary')->name('project.summary');
   Route::get('/master/finance/taxmaster','getProjectSummary')->name('finance.taxmaster');
   Route::get('/master/payment/modemaster','getProjectSummary')->name('payment.modemaster');
});

Route::middleware(['isLoggedIn','role:ADMIN'])->controller(FinanceDashboardController::class)->group(function()
{
   Route::get('/master/financedashboard','index')->name('finance.dashboard');
});

Route::middleware(['isLoggedIn','role:ADMIN'])->controller(DemandNoteController::class)->group(function()
{
   Route::get('/finance/demandnote/index','index')->name('finance.demandnote.index');
   Route::get('/finance/demandnote/eoilist','getEoIList')->name('finance.demandnote.eoilist');
   Route::get('/finance/demandnote/demandnotelist','getDemandNoteList')->name('finance.demandnote.demandnotelist');


   /* Working */
   Route::get('/finance/createdemandnote/{requestid?}','create')->name('finance.demandnote.create');
   Route::post('/finance/storedemandnote','store')->name('finance.demandnote.store');
   Route::get('/finance/editdemandnote/{id?}','edit')->name('finance.demandnote.edit');
   Route::post('/finance/updatedemandnote/{id}','update')->name('finance.demandnote.update');
   Route::post('/finance/canceldemandnote/{id}','cancel')->name('finance.demandnote.cancel');
   Route::get('/finance/viewdemandnote/view/{id}','view')->name('finance.demandnote.view');
   
   Route::post('/finance/demandnote/showresources','getDemandNoteResources')->name('finance.demandnote.showresources');
   Route::post('/finance/demandnote/submitresources','getDemandNoteValue')->name('finance.demandnote.submitresources');
   Route::post('/finance/demandnote/calculation','getCalculatedFile')->name('finance.demandnote.calculation');
   Route::post('/finance/demandnote/downloadcalculation','downloadCalculatedFile')->name('finance.demandnote.downloadcalculation');
});

Route::middleware(['isLoggedIn','role:ADMIN'])->controller(DepartmentPaymentController::class)->group(function()
{
   Route::get('/finance/departmentreceipt/index','index')->name('finance.departmentreceipt.index');
   Route::get('/finance/departmentreceipt/demandnotelist','getDemandNoteList')->name('finance.departmentreceipt.demandnotelist');
   Route::get('/finance/departmentreceipt/create/{id}','create')->name('finance.departmentreceipt.create');
   Route::post('/finance/departmentreceipt/store','store')->name('finance.departmentreceipt.store');
   Route::get('/finance/departmentreceipt/receiptlist','getDepartmentReceiptList')->name('finance.departmentreceipt.receiptlist');
   Route::get('/finance/departmentreceipt/edit/{id}','edit')->name('finance.departmentreceipt.edit');
   Route::post('/finance/departmentreceipt/update/{id}','update')->name('finance.departmentreceipt.update');
   Route::post('/finance/departmentreceipt/cancel/{id}','cancel')->name('finance.departmentreceipt.cancel');
   Route::get('/finance/departmentreceipt/view/{id}','view')->name('finance.departmentreceipt.view');
   Route::get('/finance/departmentreceipt/receipts','departmentReceipts')->name('finance.departmentreceipt.receipts');
   
});


Route::middleware(['isLoggedIn','role:ADMIN'])->controller(DepartmentInvoiceController::class)->group(function()
{
   Route::get('/finance/departmentinvoice/create/{id}','create')->name('finance.departmentinvoice.create');
   Route::post('/finance/departmentinvoice/prepare','prepareDepartmentInvoice')->name('finance.departmentinvoice.prepare');
   Route::get('/finance/departmentinvoice/form/{ids}','prepareDepartmentInvoiceForm')->name('finance.departmentinvoice.form');
   Route::post('/finance/departmentinvoice/store','storeDepartmentInvoice')->name('finance.departmentinvoice.store');
   Route::get('/finance/departmentinvoice/index','index')->name('finance.departmentinvoice.index');
   Route::get('/finance/departmentinvoice/invoicelist','getDepartmentInvoiceList')->name('finance.departmentinvoice.invoicelist');
   Route::get('/finance/departmentinvoice/view/{id}','view')->name('finance.departmentinvoice.view');
   Route::get('/finance/departmentinvoice/edit/{id}','edit')->name('finance.departmentinvoice.edit');
   Route::post('/finance/departmentinvoice/update','updateDepartmentInvoice')->name('finance.departmentinvoice.update');
   Route::post('/finance/departmentinvoice/post','post')->name('finance.departmentinvoice.post');
   Route::post('/finance/departmentinvoice/cancel','cancelDepartmentInvoice')->name('finance.departmentinvoice.cancel');
});


Route::middleware(['isLoggedIn','role:ADMIN'])->controller(FinanceVendorInvoiceController::class)->group(function()
{
   Route::get('/finance/vendorinvoice/index','index')->name('finance.vendorinvoice.index');
   Route::get('/finance/vendorinvoice/list','getVendorInvoiceList')->name('finance.vendorinvoice.list');
   Route::get('/finance/vendorinvoice/view/{id}','view')->name('finance.vendorinvoice.view');
   Route::post('/finance/vendorinvoice/mpr/verify','verifyMpr')->name('finance.vendorinvoice.mpr.verify');
   Route::post('/finance/vendorinvoice/invoice/verify','verifyVendorInvoice')->name('finance.vendorinvoice.invoice.verify');
});

Route::middleware(['isLoggedIn','role:ADMIN'])->controller(FinanceVendorPaymentController::class)->group(function()
{
   Route::get('/finance/vendorpayment/index','index')->name('finance.vendorpayment.index');
   Route::get('/finance/vendorpayment/verifiedinvoicelist','getVerifiedInvoiceList')->name('finance.vendorpayment.verifiedinvoicelist');
   Route::get('/finance/vendorpayment/make/{id}','makeVendorPayment')->name('finance.vendorpayment.make');
   Route::post('/finance/vendorpayment/store','storeVendorPayment')->name('finance.vendorpayment.store');
   Route::get('/finance/vendorpayment/vendorpaymentlist','financeVendorPaymentList')->name('finance.vendorpayment.vendorpaymentlist');
   Route::get('/finance/vendorpayment/view/{id}','view')->name('finance.vendorpayment.view');
});

Route::middleware(['isLoggedIn','role:ADMIN'])->controller(FinanceDepartmentFundController::class)->group(function()
{
   Route::get('/finance/department/balance','index')->name('finance.department.balance');
   Route::get('/finance/department/availablefund','getDepartmentAvailableFund')->name('finance.department.availablefund');
});

/*
Route::fallback(function () {
	return response()->view('admin.errors.custom_error', [], 404);
});
*/