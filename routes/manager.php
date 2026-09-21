<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Master\MasterController;
use App\Http\Controllers\Manager\ProjectManagerController;
use App\Http\Controllers\Manager\PmInvoiceController;

use App\Http\Controllers\Master\LanguageController;
use Illuminate\Support\Facades\Session;

Route::middleware(['isLoggedIn','check.method','role:PROJECT MANAGER'])->controller(ProjectManagerController::class)->group(function()
{
	/* PROJECT MANAGER */

	Route::get('/master/pmguide/line','pmSelectionProcess')->name('pmguide.line');
	//Route::get('/master/pmguide/line','pmSelectionProcess')->name('pmguide.line');
	//Route::get('/master/pmselection/process','pmSelectionProcess')->name('pmselection.process');
	Route::get('/master/add/pmhiring','addPmHiring')->name('add.pmhiring');   
	Route::post('/load-pm-form','loadFormData')->name('load-pm-form');
	Route::post('/master/saveonly/pmeoi','saveOnlyEOI')->name('saveonly.pmeoi');
	Route::get('/master/list/pmexperiences','getPmExperienceList')->name('pmexperiences.list');
	Route::get('/master/get/pmremuneration','getPmRemunerationAmount')->name('get.pmremuneration');
	Route::post('/master/add/pmeoirecord','addPmEoiRecord')->name('add.pmeoirecord');		
	Route::post('/master/store/pmhiring/{recordid}','storePmHiring')->name('store.pmhiring');
	Route::post('/master/clear/pmeois','clearPmEois')->name('clear.pmeois');
	Route::get('/delete/deletepmeois/{recordid}','deletePmEois')->name('delete.deletepmeois');   
	Route::get('/delete/delete_pmeois/{recordid}','deletePmRequestEois')->name('delete.delete_pmeois');   
	
	Route::get('/master/pm/eoilist/{eoi_status?}','pmEoiList')->name('pm.eoilist');
	Route::get('/master/pmeoirequest/html','getPmEoiRequestData')->name('pmeoirequest.html');
	Route::get('/master/pmeoipreview/printable/{recordid}','eoiPreviewPrintable')->name('pmeoipreview.printable');
	Route::get('/master/pmview/draft/{recordid}','pmViewDraft')->name('pmview.draft');
	Route::post('/master/add/pmcommittee','addPmEoiCommitteeMember')->name('add.pmcommittee');
	Route::post('/generate_pmconfirmation_otp','generatePmConfirmationOtp')->name('generate_pmconfirmation_otp');
	Route::post('/verify_pmconfirmation_otp','verifyPmConfirmationOtp')->name('verify_pmconfirmation_otp');
	Route::get('/master/prebid/pmreply/{recordid}','preBidPmReplyData')->name('prebid.pmreply');
	Route::post('/master/store/pmreply','storePmReply')->name('store.pmreply'); 
	Route::get('/master/printpreview/pmeoi/{recordid}','printPreviewPmEOI')->name('printpreview.pmeoi');
	Route::get('/master/pmempanel/vendors','pmempanelVendors')->name('pmempanel.vendors');
	Route::get('/master/pmvendors/html','getPmVendorData')->name('pmvendors.html');
	Route::get('/master/update/pmconfirm/{recordid}','updatePmConfirmation')->name('update.pmconfirm'); 
	
	
	Route::get('/master/add/pmcommittee','addCommitteeMember')->name('add.pmcommittee');  
	Route::get('/master/pmcommittee/html','getCommitteeMemberData')->name('pmcommittee.html');
	Route::post('/master/store/pmcommittee/{recordid}','storeCommitteeMember')->name('store.pmcommittee');
	Route::get('/master/edit/pmcommittee/{recordid}','editCommitteeMember')->name('edit.pmcommittee');
	Route::post('/master/update/pmcommittee/{recordid}','storeCommitteeMember')->name('update.pmcommittee');
	Route::get('/delete/pmcommittee/{recordid}','deleteCommitteeMember')->name('delete.pmcommittee');
	
	
	Route::get('/master/pm/orderslist','pmWorkOrderList')->name('pm.orderslist');
	Route::get('/master/pmorderlist/html','getPmWorkOrderData')->name('pmorderlist.html');
	
	Route::get('/master/show/pmprebidanswer/{recordid}','showPreBidResponse')->name('show.pmprebidanswer');
	
	Route::get('/master/view/pmdeployment/{orderid}','viewPmDeployment')->name('view.pmdeployment');
	Route::post('/master/update/pmdeploymentverification/{orderid}','updatePmDeploymentVerification')->name('update.pmdeploymentverification');

	Route::get('/master/pm/mprlist/{search?}','managerMprList')->name('pm.mprlist');
	Route::get('/master/pmmpr/html','getPmMprData')->name('pmmpr.html');
	Route::get('/master/view/mprmanager/{mprid?}','viewMprManager')->name('view.mprmanager');
	Route::get('/master/view/attendancemprmanager','viewAttendanceMprManager')->name('view.attendancemprmanager');
	
	Route::post('/master/update/pmmprattendance','updateMprAttendance')->name('update.pmmprattendance');
	Route::post('/master/approve/pmresourcempr','approveResourceMpr')->name('approve.pmresourcempr');
	
	Route::get('/master/update/pmeoidetail/{recordid}','updatePmEoiDetail')->name('update.pmeoidetail'); 
	Route::post('/master/updateonly/pmeoi','updateOnlyPmEOI')->name('updateonly.pmeoi');
	Route::post('/master/update/pmeoifile','updatePmEoiFiles')->name('update.pmeoifile');
	Route::post('/master/update/pmattachmenttitle','updatePmAttachmentTitle')->name('update.pmattachmenttitle');  
	Route::post('/master/upload/pmattachments','uploadPmAttachments')->name('upload.pmattachments'); 
	Route::post('/master/remove/pmattachedfile','removeAttachment')->name('remove.pmattachedfile');
	
	
	Route::post('/load-pmupdateform','loadUpdateFormData')->name('load-pmupdateform');
	Route::post('/master/update/pmeoirecord','updateEoiRecord')->name('update.pmeoirecord');

   Route::post('/master/update/pmresourcedetail','updateResourceRecord')->name('update.pmresourcedetail');
   Route::post('/master/log/pmupdateresource','storeResourceUpdate')->name('log.pmupdateresource');

   Route::post('/department/update/pmresource_detail','updateEoiResourceRecord')->name('update.pmresource_detail');
   Route::post('/department/log/pmresourceupdate','storeEoiResourceUpdate')->name('log.pmresourceupdate');
	
   Route::get('/master/view/pmpptresumes/{recordid}','viewPptResumes')->name('view.pmpptresumes');
   Route::get('/master/view/pmparticipations/{recordid}','viewPmParticipations')->name('view.pmparticipations');

   Route::post('/master/updatepm/interviewdate','updatePmInterviewDate')->name('updatepm.interviewdate');
   
   Route::post('/master/pmview/committeemember/{requestid?}','viewPmCommitteeMember')->name('pmview.committeemember');
   
   
   Route::post('/master/updateprebid/pmresponse/{recordid?}','updatePreBidPmResponse')->name('updateprebid.pmresponse');
   Route::post('/master/submitprebid/pmresponse/{recordid?}','submitPreBidPmResponse')->name('submitprebid.pmresponse');
   
   Route::get('/master/add/eoiresource/{requestid?}','addEoIResource')->name('add.eoiresource');
   Route::post('/master/upload/additional/resource/letter','uploadAdditionalResourceLetter')->name('upload.additional.resource.letter');
   Route::post('/master/store/eoi/resource','storeAdditionalEoiResource')->name('store.eoi.resource');
});

Route::middleware(['isLoggedIn','check.method','role:PROJECT MANAGER'])->controller(PmInvoiceController::class)->group(function()
{
	Route::get('/master/manager/invoices','pmInvoiceList')->name('pm.invoices');
	Route::get('/master/manager/html','getPmInvoiceData')->name('pminvoicehistory.html');
	Route::get('/master/manager/mark/{recordid?}','markAsSeen')->name('mark.invoice');
});