<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\OrderValueService;

class OrderValueController extends Controller
{
    protected $valueService;
    public function __construct(OrderValueService $valueService)
    {
        $this->valueService = $valueService;
    }	
    public function getCalculation(Request $request)
    {
		
		$requestid	=	Crypt::decrypt($request->input('requestid'));

		$eoi		=	DB::table('eoi_request')->where('requestid',$requestid)->first();

		$tierid		=	$request->input('tierid');
		$categoryid	=	$request->input('categoryid');
		
		if($categoryid==2)
		{
			$resources = DB::table('eoi_request_detail as a')
						->select('b.sectorname','c.consultantposition','d.remuneration as base_price','a.duration')
						->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
						->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
						->leftJoin('remuneration_tbl as d', function($join){
							$join->on('d.sectorid','=','a.sectorid')
								 ->on('d.positionid','=','a.positionid')
								 ->on('d.rateid','=','a.rateid');
						})
						->where('a.requestid', $requestid)
						->where('d.categoryid', $categoryid)
						->where('d.tierid', $tierid)
						->get();
			}

        $data = $this->valueService->calculateOrder(date('Y\-m\-d',strtotime($eoi->creationdate)),$eoi->projectduration,$resources);

		$html = view('admin.pricingpages.ordercalculation',['data'=>$data])->render();
		
		return response()->json(['status'=>200,'message'=>'Price Calculation.','formhtml' => $html]);
		
        //return view('eoi.calculation', compact('data'));
    }
	
}
