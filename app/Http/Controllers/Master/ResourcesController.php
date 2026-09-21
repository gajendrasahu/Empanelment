<?php

namespace App\Http\Controllers;
use Illuminate\Pagination\LengthAwarePaginator;
namespace App\Http\Controllers\Master;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ResourcesController extends Controller
{
    private $str;

    public function __construct()
    {
        $this->str = 'PARAS';
    }

    public function GetFinancialYear($passeddate)
    {
        if($passeddate)
        {
            $year = date('Y',strtotime($passeddate));
            $year1 = date('y',strtotime($passeddate));
            $month= date('m',strtotime($passeddate));
            if ($month>=4) {
                $financialYear=$year.'-'.($year1 + 1);
            } else {
                $financialYear=($year - 1).'-'.$year1;
            }
            return $financialYear;
        }
        else
        {
            return "";
        }
    }

    public function GetVoucherNo($financial,$vouchertype)
    {
        if($vouchertype=='REC')
        {
            $no = 1;
            $lastRecord = DB::table('receipt_tbl')
                        ->select('voucherno')
                        ->where('financialyear','=',$financial)
                        ->orderBy('receiptid','desc')
                        ->first();
            if($lastRecord)
            {
                $no = $lastRecord->voucherno+1;
                return "REC/".$financial."/".$no;
            }
            else
            {
                return "REC/".$financial."/".$no;
            }
        }
        if($vouchertype=='PP')
        {
            $no = 1;
            $lastRecord = DB::table('partypayment_tbl')
                        ->select('voucherno')
                        ->where('financialyear','=',$financial)
						->where('transactiontype','=','PURCHASE')
                        ->orderBy('paymentid','desc')
                        ->first();
            if($lastRecord)
            {
                $no = $lastRecord->voucherno+1;
                return "PP-".str_pad($no, 10, '0', STR_PAD_LEFT);
            }
            else
            {
                return "PP-".str_pad($no, 10, '0', STR_PAD_LEFT);
            }
        }
        if($vouchertype=='SP')
        {
            $no = 1;
            $lastRecord = DB::table('partypayment_tbl')
                        ->select('voucherno')
                        ->where('financialyear','=',$financial)
						->where('transactiontype','=','SALES')
                        ->orderBy('paymentid','desc')
                        ->first();
            if($lastRecord)
            {
                $no = $lastRecord->voucherno+1;
                return "SP-".str_pad($no, 10, '0', STR_PAD_LEFT);
            }
            else
            {
                return "SP-".str_pad($no, 10, '0', STR_PAD_LEFT);
            }
        }
        if($vouchertype=='SL')
        {
            $no = 1;
            $lastRecord = DB::table('sales_tbl')
                        ->select('voucherno')
                        ->where('financialyear','=',$financial)
                        ->orderBy('salesid','desc')
                        ->first();
            if($lastRecord)
            {
                $no = $lastRecord->voucherno+1;
                return "SL-".str_pad($no, 10, '0', STR_PAD_LEFT);
            }
            else
            {
                return "SL-".str_pad($no, 10, '0', STR_PAD_LEFT);
            }
        }
    }

    public function NumberToWords($x)
    {
        $number = $x;
        $no = round($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = array();
        $words = array('0' => '', '1' => 'one', '2' => 'two',
        '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six',
        '7' => 'seven', '8' => 'eight', '9' => 'nine',
        '10' => 'ten', '11' => 'eleven', '12' => 'twelve',
        '13' => 'thirteen', '14' => 'fourteen',
        '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen',
        '18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty',
        '30' => 'thirty', '40' => 'forty', '50' => 'fifty',
        '60' => 'sixty', '70' => 'seventy',
        '80' => 'eighty', '90' => 'ninety');
        $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
        while ($i < $digits_1) 
        {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) 
            {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number] .
            " " . $digits[$counter] . $plural . " " . $hundred
            :
            $words[floor($number / 10) * 10]
            . " " . $words[$number % 10] . " "
            . $digits[$counter] . $plural . " " . $hundred;
            }
            else $str[] = null;
        }
        $str 	= array_reverse($str);
        $result = implode('',$str);
        return $result;
    }
}
