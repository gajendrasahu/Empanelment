<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\MasterController;
use App\Http\Controllers\Department\DepartmentManagementController;
use App\Http\Controllers\Department\ReCallController;
use App\Http\Controllers\Department\DepartmentInvoiceController;

use App\Http\Controllers\Master\LanguageController;
use Illuminate\Support\Facades\Session;

Route::middleware(['isLoggedIn','check.method','role:DEPARTMENT,PROJECT MANAGER'])->controller(ReCallController::class)->group(function()
{
	Route::get('/master/recall/eoi/{requestid?}','addReCall')->name('recall.eoi');   
	Route::post('/master/store/recall/{requestid?}','storeReCall')->name('store.recall');   
});
Route::middleware(['isLoggedIn','check.method','role:DEPARTMENT'])->controller(DepartmentManagementController::class)->group(function()
{
   Route::get('/master/add/cmtmember','addCommitteeMember')->name('add.cmtmember');   
   Route::post('/master/store/cmtmember/{recordid}','storeCommitteeMember')->name('store.cmtmember');
   Route::get('/master/cmtmember/html','getCommitteeMemberData')->name('cmtmember.html');
   Route::get('/master/edit/cmtmember/{recordid}','editCommitteeMember')->name('edit.cmtmember');
   Route::post('/master/update/cmtmember/{recordid}','storeCommitteeMember')->name('update.cmtmember');
   Route::get('/delete/cmtmember/{recordid}','deleteCommitteeMember')->name('delete.cmtmember');   
   
   Route::post('/master/add/deptcommittee','addDeptEoiCommitteeMember')->name('add.deptcommittee');   


   Route::get('/master/add/hiring','addHiring')->name('add.hiring');   
   
   Route::post('/master/add/eoirecord','addEoiRecord')->name('add.eoirecord');   
   Route::get('/delete/deleteeois/{recordid}','deleteEois')->name('delete.deleteeois');   
   Route::get('/delete/delete_eois/{recordid}','deleteRequestEois')->name('delete.delete_eois'); 
   Route::post('/master/clear/eois','clearEois')->name('clear.eois');   
   
   Route::post('/master/store/hiring/{recordid}','storeHiring')->name('store.hiring');
   Route::get('/master/hiring/html','getHiringData')->name('hiring.html');
   Route::get('/master/edit/hiring/{recordid}','editHiring')->name('edit.hiring');
   Route::post('/master/update/hiring/{recordid}','storeHiring')->name('update.hiring');
   Route::get('/delete/hiring/{recordid}','deleteHiring')->name('delete.hiring');   
   
   Route::post('/master/print/eoi','printEOI')->name('print.eoi');
   Route::get('/master/printpreview/eoi/{recordid}','printPreviewEOI')->name('printpreview.eoi');
   Route::get('/master/eoipreview/printable/{recordid}','eoiPreviewPrintable')->name('eoipreview.printable');
   Route::post('/master/saveonly/eoi','saveOnlyEOI')->name('saveonly.eoi');
   Route::get('/master/dept/viewresumes/{recordid}','deptViewResumes')->name('dept.viewresumes');
   Route::get('/master/dept/viewparticipations/{recordid}','deptViewParticipations')->name('dept.viewparticipations');
   
   Route::get('/master/dept/eoilist/{eoi_status?}','departmentEoiList')->name('dept.eoilist');
   
   Route::get('/master/depteoirequest/html','getDeptEoiRequestData')->name('depteoirequest.html');
   
   Route::get('/master/deptview/draft/{recordid}','departmentViewDraft')->name('deptview.draft');
   Route::post('/generate_confirmation_otp','generateConfirmationOtp')->name('generate_confirmation_otp');
   Route::post('/verify_confirmation_otp','verifyConfirmationOtp')->name('verify_confirmation_otp');
   
   Route::get('/master/dept/ratelist','deptRateList')->name('dept.ratelist');   
   Route::get('/master/deptratelist/html','getDeptRatelistData')->name('deptratelist.html');
   
   Route::get('/master/guide/line','selectionProcess')->name('guide.line'); 
   Route::get('/master/selection/process','selectionProcess')->name('selection.process');   
   Route::get('/master/empanel/vendors','empanelVendors')->name('empanel.vendors');   
   Route::get('/master/deptvendors/html','getDeptVendorData')->name('deptvendors.html');   
   
   Route::post('/load-form','loadFormData')->name('load-form');   
   
   
   Route::get('/master/prebidforward/list/{recordid}','preBidForwardList')->name('prebidforward.list'); 
   Route::get('/master/prebid/reply/{recordid}','preBidReplyData')->name('prebid.reply');
   Route::post('/master/store/reply','storeReply')->name('store.reply'); 
   
   Route::get('/master/show/prebidanswer/{recordid}','showPreBidResponse')->name('show.prebidanswer'); 
   
   Route::get('/master/update/eoidetail/{recordid}','updateEoiDetail')->name('update.eoidetail'); 
   Route::get('/master/update/confirm/{recordid}','updateEoiConfirmation')->name('update.confirm'); 
   
   Route::post('/load-updateform','loadUpdateFormData')->name('load-updateform');  
   Route::post('/master/update/eoirecord','updateEoiRecord')->name('update.eoirecord');    
   Route::post('/master/updateonly/eoi','updateOnlyEOI')->name('updateonly.eoi');
   Route::get('/delete/deleteeoidetail/{recordid}','deleteEoiDetail')->name('delete.deleteeoidetail');   
   
   Route::post('/master/update/attachmenttitle','updateAttachmentTitle')->name('update.attachmenttitle');  
   Route::post('/master/remove/attachedfile','removeAttachment')->name('remove.attachedfile');  
   Route::post('/master/upload/attachments','uploadAttachments')->middleware('pdf.security')->name('upload.attachments');  
   
   Route::post('/master/update/eoifile','updateEoiFiles')->middleware('pdf.security')->name('update.eoifile');  
   
   Route::get('/master/department/orderslist','departmentWorkOrderList')->name('department.orderslist');
   Route::get('/master/departmentworkorders/list/{search?}','departmentWorkOrdersList')->name('departmentworkorders.list');
   
   Route::get('/master/departmentorderlist/html','getDepartmentWorkOrderData')->name('departmentorderlist.html');
   
   Route::get('/master/view/deployment/{orderid}','viewDeployment')->name('view.deployment');
   Route::post('/master/update/deploymentverification/{orderid}','updateDeploymentVerification')->name('update.deploymentverification');
   
   
   Route::get('/master/department/mprlist/{search?}','departmentMprList')->name('department.mprlist');
   Route::get('/master/mpr/html','getMprData')->name('mpr.html');
   Route::get('/master/view/mprdepartment/{mprid?}','viewMprDepartment')->name('view.mprdepartment');
   Route::get('/master/view/attendancemprdepartment','viewAttendanceMprDepartment')->name('view.attendancemprdepartment');
   
   Route::post('/master/approve/resourcempr','approveResourceMpr')->name('approve.resourcempr');
   
   Route::post('/master/update/mprattendance','updateMprAttendance')->name('update.mprattendance');
      
   Route::get('/department/cancel/eoi/{recordid}','cancelEoi')->name('cancel.eoi');
   Route::post('/department/store/cancellation','storeEoiCancellation')->middleware('pdf.security')->name('store.cancellation');
   
   Route::post('/department/update/resourcedetail','updateResourceRecord')->name('update.resourcedetail');
   Route::post('/department/log/updateresource','storeResourceUpdate')->name('log.updateresource');
   
   Route::post('/department/update/resource_detail','updateEoiResourceRecord')->name('update.resource_detail');
   Route::post('/department/log/resourceupdate','storeEoiResourceUpdate')->name('log.resourceupdate');
   
   Route::get('/master/view/deptpptresumes/{recordid}','viewPptResumes')->name('view.deptpptresumes');
   Route::get('/master/view/deptparticipations/{recordid}','viewDeptParticipations')->name('view.deptparticipations');
   
   Route::post('/master/updateprebid/response/{recordid?}','updatePreBidResponse')->name('updateprebid.response');
   Route::post('/master/submitprebid/response/{recordid?}','submitPreBidResponse')->name('submitprebid.response');
   
   Route::post('/master/updatedept/interviewdate','updateDeptInterviewDate')->name('updatedept.interviewdate');
});

Route::middleware(['isLoggedIn','check.method','role:DEPARTMENT'])->controller(DepartmentInvoiceController::class)->group(function()
{
	Route::get('/master/department/invoices','deptInvoiceList')->name('dept.invoices');
	Route::get('/master/department/html','getDeptInvoiceData')->name('deptinvoicehistory.html');
	Route::get('/master/department/mark/{recordid?}','markAsSeen')->name('deptmark.invoice');
});