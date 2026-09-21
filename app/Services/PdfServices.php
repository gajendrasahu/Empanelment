<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Exception;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\TierWiseDataService;
use Illuminate\Support\Facades\Log;
class PdfServices
{
	protected $appUrl;
	protected $priceService;
	
	public function __construct(TierWiseDataService $priceService)
	{
		$this->appUrl 		=	Config::get('app.url');
		ini_set('serialize_precision',-1);
		
		$this->priceService	=	$priceService;
	}
	
	function geterateEoiPdf($requestid)
	{
		try
		{
			$data	=	DB::table('eoi_request')->where('requestid',$requestid)->first();
			
			if($data->categoryid==2)
			{
				$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',1)->first();
				if(!$pricing)
				{
					throw new \Exception('Pricing not found');
				}				
				$detail		=	$this->priceService->getCsfPrice($data->requestid,$data->categoryid,1);
				if (empty($detail) || !is_iterable($detail))
				{
					throw new \Exception('Price details not found');
				}				
				$totalmanmonth		=	0;
				$adminchargetotal	=	0;
				$grandtotal			=	0;
				foreach($detail as $rec)
				{
					$rec->baseprice	=	$rec->budget;
					$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;
					$totalmanmonth	=	$totalmanmonth+$rec->budget;
				}

				$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
				$grandtotal			=	$totalmanmonth+$adminchargetotal;
				
				$data->totalmanmonth	=	$this->formatIndianCurrency($totalmanmonth);
				$data->adminchargetotal	=	$this->formatIndianCurrency($adminchargetotal);
				$data->grandtotal		=	$this->formatIndianCurrency($grandtotal);
				
				
				$eoi_csf_tier1	=	PDF::loadView('pdf.csf_tier1',compact('data','pricing','detail'))->setPaper('A4','portrait');
				
				$filename	=	uniqid()."_{$data->requestid}_csf_tier1.pdf";
				
				$eoi_csf_tier1->save(storage_path('app/public/vendor_eoifiles/'.$filename));
			}
		}
		catch(Exception $e)
		{
			Log::error('Error: ' . $e->getMessage());
			return [
				'success' 	=>	false,
				'message' 	=> 	$e->getMessage(),
				'status' 	=> 	400,
			];			
		}
	}
	function formatIndianCurrency($number) 
	{
		$decimal = '';
		if (strpos($number, '.') !== false) {
			$parts = explode('.', $number);
			$number = $parts[0];
			$decimal = '.' . substr($parts[1], 0, 2); // Keep 2 decimal places
		}

		$lastThree = substr($number, -3);
		$rest = substr($number, 0, -3);

		if ($rest != '') {
			$lastThree = ',' . $lastThree;
		}

		$rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);
		return $rest . $lastThree . $decimal;
	}	
}
