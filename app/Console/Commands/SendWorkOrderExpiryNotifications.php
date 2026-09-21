<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\WorkOrderExpiryEmail;

class SendWorkOrderExpiryNotifications extends Command
{
    protected $signature = 'workorder:expiry-notifications';

    protected $description = 'Send work order expiry reminder notifications';

    public function handle()
    {
        $reminderDays = [60, 45, 30, 15, 7, 3, 1];

        $today = Carbon::today();

        $workOrders	=	DB::table('eoi_work_order as wo')
                        ->join('department_tbl as d','d.departmentid','=','wo.department_id')
                        ->leftJoin('eoi_request as eoi','eoi.requestid','=','wo.requestid')
                        ->whereNotNull('wo.workorderduedate')
                        ->whereDate('wo.workorderduedate','>=',$today)
                        ->where('wo.isActiveOrder',1)
						->where('wo.sendDueReminder',1)
                        ->select(
                            'wo.*',
                            'd.departmentname',
                            'd.officialemail',
                            'eoi.projecttitle',
                            'eoi.eoinumber',
                            'eoi.engagementname'
                        )
                        ->get();

        $sentCount = 0;

        foreach ($workOrders as $workOrder)
        {
            $dueDate	=	Carbon::parse($workOrder->workorderduedate);

            $remainingDays = $today->diffInDays($dueDate);

            if(!in_array($remainingDays, $reminderDays))
            {
                continue;
            }

            if(empty($workOrder->officialemail))
            {
                continue;
            }
			
			/*
            Mail::to($workOrder->officialemail)
                ->cc([
                    'ceo@cgchips.in',
                    'jceop@cgchips.in',
                    'jceo.finance@cgchips.in',
                    'singh.ranjeet@cgchips.in',
                    'divya_tiwari@cgchips.in',
                    'empl.chips@cgchips.in',
                    'empl_finance@cgchips.in',
                    'empl_hr@cgchips.in',
                ])
                ->send(
                    new WorkOrderExpiryEmail(
                        $workOrder,
                        $remainingDays
                    )
                );
			*/
			
			//Mail::to('gajendrasahu09@gmail.com')->send(new WorkOrderExpiryEmail($workOrder,$remainingDays));
			
			DB::table('reminder_notification')->insert([
				'ordernumber'	=>	$workOrder->ordernumber,
				'email'			=>	$workOrder->officialemail,
				'sent_on'		=>	now()
			]);
			
            $sentCount++;

            $this->info('Email sent to ' .$workOrder->officialemail .' - ' .$remainingDays .' days remaining');
        }

        $this->info($sentCount .' work order reminder(s) processed successfully.');

        return Command::SUCCESS;
    }
}
?>