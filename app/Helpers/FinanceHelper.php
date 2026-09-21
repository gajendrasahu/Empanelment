<?php

namespace App\Helpers;

use DB;
use NumberFormatter;
use Carbon\Carbon;
class FinanceHelper
{
	public static function financialYearFromDate($date)
	{
		$date	=	Carbon::parse($date);
		
		if((int)$date->format('n')>=4)
		{
			$start	=	(int)$date->format('Y');
		}
		else
		{
			$start	=	(int)$date->format('Y')-1;
		}
		return $start.'-'.substr($start+1,2);
	}

    public static function financialYears($previousYears = 2)
    {
        $years = [];

        $currentYear  = date('Y');
        $currentMonth = date('n');

        $fyStart = ($currentMonth >= 4) ? $currentYear : $currentYear - 1;

        for($i=0;$i<=$previousYears; $i++)
        {
            $start		=	$fyStart - $i;
            $end   		= 	substr($start + 1,2);
            $years[] 	= 	$start.'-'.$end;
        }

        return $years;
    }

    public static function currentFinancialYear()
    {
        return self::financialYears(0)[0];
    }

	public static function generateDocumentNumber($documentName)
	{
		$document	=	DB::table('finance_document_master')->where('document_name',$documentName)->where('is_active',1)->first();

		if(!$document)
		{
			throw new \Exception("Document Master not configured.");
		}

		$financialYear	=	self::currentFinancialYear();

		$lastNumber		=	DB::table($document->table_name)
							->where('financial_year',$financialYear)
							->orderByDesc($document->document_column)
							->value($document->document_column);

		if($lastNumber)
		{
			$lastSequence=	(int)substr($lastNumber,strrpos($lastNumber,'/')+1);
		}
		else
		{
			$lastSequence=	0;
		}

		$sequence	=	str_pad($lastSequence + 1,$document->running_length,'0',STR_PAD_LEFT);

		return $document->document_prefix.'/'.$financialYear.'/'.$sequence;
	}

	public static function calculateGST($taxableAmount, $taxId, $gstType = 'INTRA_STATE')
	{
		$tax = DB::table('finance_tax_master')->where('tax_id', $taxId)->where('is_active', 1)->first();

		if(!$tax)
		{
			return [
				'rate'         => 0,
				'cgst_rate'    => 0,
				'sgst_rate'    => 0,
				'igst_rate'    => 0,
				'cgst_amount'  => 0,
				'sgst_amount'  => 0,
				'igst_amount'  => 0,
				'gst_amount'   => 0,
				'grand_total'  => round($taxableAmount, 2)
			];
		}

		if($gstType == 'INTER_STATE')
		{
			$cgstRate   = 0;
			$sgstRate   = 0;
			$igstRate   = $tax->igst_percent;
			$cgstAmount = 0;
			$sgstAmount = 0;
			$igstAmount = round(($taxableAmount * $igstRate) / 100, 2);
		}
		else
		{
			$cgstRate   = $tax->cgst_percent;
			$sgstRate   = $tax->sgst_percent;
			$igstRate   = 0;
			$cgstAmount = round(($taxableAmount * $cgstRate) / 100, 2);
			$sgstAmount = round(($taxableAmount * $sgstRate) / 100, 2);
			$igstAmount = 0;
		}

		$gstAmount = $cgstAmount + $sgstAmount + $igstAmount;

		return [
			'rate'         =>	$tax->rate,
			'cgst_rate'    => 	$cgstRate,
			'cgst_amount'  => 	$cgstAmount,
			'sgst_rate'    => 	$sgstRate,
			'sgst_amount'  => 	$sgstAmount,
			'igst_rate'    => 	$igstRate,
			'igst_amount'  => 	$igstAmount,
			'gst_amount'   => 	$gstAmount,
			'grand_total'  => 	round($taxableAmount + $gstAmount, 2)
		];
	}

    public static function amountInWords($amount)
    {
        $formatter	=	new NumberFormatter("en_IN", NumberFormatter::SPELLOUT);
        return ucwords($formatter->format($amount)).' Rupees Only';
    }



	public static function logChanges($module,$recordId,$oldData,$newData,$action='UPDATE',$description=null)
	{
		$oldData	=	(array)$oldData;

		$uuid 		= 	(string)\Illuminate\Support\Str::uuid();

		$changes = [];

		foreach($newData as $field => $newValue)
		{
			if(!array_key_exists($field,$oldData))
			{
				continue;
			}

			$oldValue = $oldData[$field];

			if((string)$oldValue === (string)$newValue)
			{
				continue;
			}

			$changes[] = [
				'transaction_uuid' 	=> 	$uuid,
				'module_name' 		=> 	$module,
				'record_id' 		=> 	$recordId,
				'action' 			=> 	$action,
				'action_description'=> 	$description,
				'field_name' 		=> 	$field,
				'old_value' 		=> 	is_null($oldValue) ? NULL : (string)$oldValue,
				'new_value' 		=> 	is_null($newValue) ? NULL : (string)$newValue,
				'remarks' 			=> 	NULL,
				'action_by' 		=> 	session('userId'),
				'action_date' 		=> 	now(),
				'ip_address' 		=> 	request()->ip(),
			];
		}

		$count = count($changes);

		foreach($changes as &$row)
		{
			$row['changed_fields'] = $count;
		}

		if($count>0)
		{
			DB::table('finance_activity_log')->insert($changes);
		}
	}


	public static function departmentReceiptObject()
	{
		$obj = new \stdClass();
		
		$obj->department_payment_id	=	'';
		$obj->receipt_no			=	'';
		$obj->receipt_date			=	date('Y-m-d');
		$obj->financial_year		=	'';
		$obj->voucher_no			= 	'';
		$obj->voucher_date			=	date('Y-m-d');
		$obj->payment_mode_id		= 	'';
		$obj->bank_account_id		=	'';
		$obj->transaction_no		= 	'';
		$obj->gross_receipt_amount 	= 	'';
		$obj->tds_amount 			= 	'';
		$obj->gst_tds_amount 		= 	'';
		$obj->other_deduction 		= 	'';
		$obj->net_received_amount 	= 	'';
		$obj->remarks 				= 	'';
		$obj->tds_tax_id			= 	'';
		$obj->gst_tds_tax_id		= 	'';

		return $obj;
	}


	public static function updateDemandNotePaymentStatus($demandNoteId)
	{
		$demandNote = DB::table('finance_demand_note')->where('demand_note_id',$demandNoteId)->first();

		if(!$demandNote)
		{
			return false;
		}

		$received	=	DB::table('finance_department_payment')
						->where('demand_note_id',$demandNoteId)
						->where('status','Active')
						->sum('net_received_amount');

		if($received<=0)
		{
			$status='Pending';
		}
		elseif($received < $demandNote->total_amount)
		{
			$status='Partially Paid';
		}
		else
		{
			$status='Paid';
		}

		DB::table('finance_demand_note')
		->where('demand_note_id',$demandNoteId)
		->update([
			'payment_status'=>$status
		]);

		return true;
	}


	public static function calculateNetReceipt($grossAmount, $tdsTaxId = NULL, $gstTdsTaxId = NULL, $otherDeduction = 0)
	{
		$grossAmount		=	(float)$grossAmount;
		$otherDeduction		=	(float)$otherDeduction;

		$tdsRate			=	0;
		$tdsAmount			=	0;

		$gstTdsRate		=	0;
		$gstTdsAmount		=	0;

		if(!empty($tdsTaxId))
		{
			$tds	=	DB::table('finance_tax_master')
						->where('tax_id',$tdsTaxId)
						->where('tax_type','TDS')
						->where('is_active',1)
						->first();

			if($tds)
			{
				$tdsRate	=	(float)$tds->rate;
				$tdsAmount	=	round(($grossAmount * $tdsRate)/100,2);
			}
		}

		if(!empty($gstTdsTaxId))
		{
			$gstTds	=	DB::table('finance_tax_master')
						->where('tax_id',$gstTdsTaxId)
						->where('tax_type','GST_TDS')
						->where('is_active',1)
						->first();

			if($gstTds)
			{
				$gstTdsRate		=	(float)$gstTds->rate;
				$gstTdsAmount	=	round(($grossAmount * $gstTdsRate)/100,2);
			}
		}

		$netAmount	=	$grossAmount - $tdsAmount - $gstTdsAmount - $otherDeduction;

		if($netAmount < 0)
		{
			$netAmount = 0;
		}

		return [
			'tds_rate'			=>	$tdsRate,
			'tds_amount'		=>	$tdsAmount,
			'gst_tds_rate'		=>	$gstTdsRate,
			'gst_tds_amount'	=>	$gstTdsAmount,
			'other_deduction'	=>	round($otherDeduction,2),
			'net_amount'		=>	round($netAmount,2)
		];
	}


	public static function calculateDepartmentReceipt($grossAmount,$gstRate,$tdsTaxId=NULL,$gstTdsTaxId=NULL,$otherDeduction=0)
	{
		$grossAmount		=	(float)$grossAmount;
		$gstRate			=	(float)$gstRate;
		$otherDeduction		=	(float)$otherDeduction;

		if($gstRate > 0)
		{
			$taxableAmount	=	round($grossAmount / (1 + ($gstRate / 100)),2);
			$gstAmount		=	round($grossAmount - $taxableAmount,2);
		}
		else
		{
			$taxableAmount	=	round($grossAmount,2);
			$gstAmount		=	0;
		}

		$tdsRate	=	0;
		$tdsAmount	=	0;

		if(!empty($tdsTaxId))
		{
			$tds	= 	DB::table('finance_tax_master')
						->where('tax_id',$tdsTaxId)
						->where('tax_type','TDS')
						->where('is_active',1)
						->first();

			if($tds)
			{
				$tdsRate	=	(float)$tds->rate;
				$tdsAmount	=	round(($taxableAmount * $tdsRate) / 100,2);
			}
		}

		$gstTdsRate		=	0;
		$gstTdsAmount	=	0;

		if(!empty($gstTdsTaxId))
		{
			$gstTds	=	DB::table('finance_tax_master')
						->where('tax_id',$gstTdsTaxId)
						->where('tax_type','GST_TDS')
						->where('is_active',1)
						->first();

			if($gstTds)
			{
				$gstTdsRate		=	(float)$gstTds->rate;
				$gstTdsAmount	=	round(($taxableAmount * $gstTdsRate) / 100,2);
			}
		}

		$netReceivedAmount = round($grossAmount - $tdsAmount - $gstTdsAmount - $otherDeduction,2);

		if($netReceivedAmount < 0)
		{
			$netReceivedAmount = 0;
		}

		return [
			'gross_received_amount'	=>	round($grossAmount,2),
			'gst_rate'				=>	$gstRate,
			'taxable_amount'		=>	$taxableAmount,
			'gst_amount'			=>	$gstAmount,
			'tds_tax_id'			=>	$tdsTaxId,
			'tds_rate'				=>	$tdsRate,
			'tds_amount'			=>	$tdsAmount,
			'gst_tds_tax_id'		=>	$gstTdsTaxId,
			'gst_tds_rate'			=>	$gstTdsRate,
			'gst_tds_amount'		=>	$gstTdsAmount,
			'other_deduction'		=>	round($otherDeduction,2),
			'net_received_amount'	=>	$netReceivedAmount
		];
	}


	public static function isLatestDepartmentPayment($paymentId)
	{
		$payment	=	DB::table('finance_department_payment')
						->where('department_payment_id',$paymentId)
						->where('status','Active')
						->first();

		if(!$payment)
		{
			return false;
		}

		$latestPayment = DB::table('finance_department_payment')
							->where('demand_note_id',$payment->demand_note_id)
							->where('status','Active')
							->orderBy('receipt_date','desc')
							->orderBy('department_payment_id','desc')
							->first();

		if(!$latestPayment)
		{
			return false;
		}

		return $latestPayment->department_payment_id == $paymentId;
	}	

	public static function hasDepartmentInvoice($paymentId)
	{
		return DB::table('finance_department_invoice')
					->where('department_payment_id',$paymentId)
					->exists();
	}	
	public static function hasDepartmentInvoiceGenerated($paymentId)
	{
		return DB::table('finance_department_invoice_payment')
					->where('department_payment_id',$paymentId)
					->exists();
	}	

}