<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderValueService
{
    public function calculateOrder($startDate, $months, $resources,$pricing)
    {
        $result 	= 	[];
        $grandTotal = 	0;

        foreach($resources as $resource)
		{
			$start 		= 	Carbon::parse($startDate);
			$end 		= 	$start->copy()->addMonths($resource->duration)->subDay();

            $basePrice 		= 	$resource->base_price;
            $currentStart 	= 	$start->copy();
            $resourceRows 	= 	[];

            while($currentStart<=$end)
			{
                $year		=	$currentStart->year;
				$mayFirst	= 	Carbon::create($currentStart->year,6,1);
				
				if($currentStart->lt($mayFirst))
				{
					$slabEnd = $mayFirst->copy()->subDay();
				}
				else
				{
					$slabEnd = Carbon::create($currentStart->year + 1,6,1)->subDay();
				}

				if($slabEnd->gt($end))
				{
					$slabEnd = $end;
				}

                $salary 	= 	$this->calculateSalary($currentStart,$slabEnd,$basePrice);
				$operating	=	$salary*($pricing->operatingmargin/100);
                $gst 		=	($salary+$operating) * ($pricing->tax/100);
                $withGst	= 	($salary+$operating) + $gst;
                $admin 		= 	$withGst * ($pricing->admincharge/100);
                $total 		= 	$withGst + $admin;

                $resourceRows[] = [
					
                    'slab' 		=> 	$currentStart->format('d M Y').' - '.$slabEnd->format('d M Y'),
					'basePrice'	=>	round($basePrice,2),
                    'salary' 	=> 	round($salary,2),
					'operating' => 	round($operating,2),
                    'gst' 		=> 	round($gst,2),
                    'admin' 	=> 	round($admin,2),
                    'total' 	=> 	round($total,2)
                ];

                $grandTotal += $total;

                //$currentStart = $slabEnd->copy()->addDay();
				$currentStart = $slabEnd->copy()->addDay()->startOfDay();
				
				if($pricing->categoryid==2)
                $basePrice	=	$basePrice * 1.05;
				else
				$basePrice	=	$basePrice * 1.08;
            }
			if($pricing->categoryid==2)
			{
				$result[] = [
					'sector' 	=> 	$resource->sectorname,
					'resource' 	=> 	$resource->consultantposition,
					'rows' 		=> 	$resourceRows
				];
			}
			if($pricing->categoryid==1)
			{
				$result[] = [
					'role' 				=> 	$resource->role,
					'experiencelevel' 	=> 	$resource->experiencelevel,
					'experience' 		=> 	$resource->experience,
					'rows' 				=> 	$resourceRows
				];				
			}
        }

        return [
            'resources' 	=> 	$result,
            'grand_total' 	=> 	round($grandTotal,2)
        ];
    }


	private function calculateSalary($start,$end,$basePrice)
	{
		$current 	= 	$start->copy();
		$salary 	=	0;
		while($current<=$end)
		{
			$daysInMonth	=	$current->daysInMonth;
			$perDaySalary 	= 	$basePrice / $daysInMonth;
			
			$salary += $perDaySalary;
			$current->addDay();
		}
		return $salary;
	}
	
	public function calculateOlderSummary($startDate, $endDate, $resources)
	{
		try
		{
			$start = Carbon::parse($startDate);
			$end   = Carbon::parse($endDate);

			$summary = [
				'salary' => 0,
				'gst'    => 0,
				'total'  => 0,
			];

			foreach ($resources as $resource)
			{
				$basePrice = $resource->base_price;
				$currentStart = $start->copy();

				while ($currentStart <= $end)
				{
					$mayFirst = Carbon::create($currentStart->year, 6, 1);

					if ($currentStart->lt($mayFirst))
					{
						$slabEnd = $mayFirst->copy()->subDay();
					}
					else
					{
						$slabEnd = Carbon::create($currentStart->year + 1, 6, 1)->subDay();
					}

					if ($slabEnd->gt($end))
					{
						$slabEnd = $end;
					}

					$salary = $this->calculateSalary($currentStart, $slabEnd, $basePrice);
					$gst    = $salary * 0.18;
					$total  = $salary + $gst;

					$summary['salary'] += $salary;
					$summary['gst']    += $gst;
					$summary['total']  += $total;

					$currentStart = $slabEnd->copy()->addDay()->startOfDay();
					$basePrice *= 1.05;
				}
				
			}
			
			
			
			return [
				'salary' => round($summary['salary'],2),
				'gst'    => round($summary['gst'],2),
				'total'  => round($summary['total'],2),
			];
		}
		catch(Exception $e)
		{
			Log::error('Error : '.$e->getMessage());
		}
	}	


	public function calculateSummary($startDate, $months, $resources)
	{
		try
		{
			$start = Carbon::parse($startDate);
			$end   = $start->copy()->addMonths($months)->subDay();

			$summary = [
				'salary' => 0,
				'gst'    => 0,
				'total'  => 0,
			];

			foreach ($resources as $resource)
			{
				$basePrice = $resource->base_price;
				$currentStart = $start->copy();

				while ($currentStart <= $end)
				{
					$mayFirst = Carbon::create($currentStart->year, 6, 1);

					if ($currentStart->lt($mayFirst))
					{
						$slabEnd = $mayFirst->copy()->subDay();
					}
					else
					{
						$slabEnd = Carbon::create($currentStart->year + 1, 6, 1)->subDay();
					}

					if ($slabEnd->gt($end))
					{
						$slabEnd = $end;
					}

					$salary = $this->calculateSalary($currentStart, $slabEnd, $basePrice);
					$gst    = $salary * 0.18;
					$total  = $salary + $gst;

					$summary['salary'] += $salary;
					$summary['gst']    += $gst;
					$summary['total']  += $total;

					$currentStart = $slabEnd->copy()->addDay()->startOfDay();
					$basePrice *= 1.05;
				}
				
			}
			
			
			
			return [
				'salary' => round($summary['salary'],2),
				'gst'    => round($summary['gst'],2),
				'total'  => round($summary['total'],2),
			];
		}
		catch(Exception $e)
		{
			Log::error('Error : '.$e->getMessage());
		}
	}	


	public function calculateOrderSummary($startDate, $endDate, $resources)
	{
		try
		{
			$start = Carbon::parse($startDate);
			$end   = Carbon::parse($endDate);

			$summary = [
				'salary' => 0,
				'gst'    => 0,
				'total'  => 0,
			];

			foreach($resources as $resource)
			{
				$basePrice = $resource->base_price;
				$currentStart = $start->copy();

				while ($currentStart <= $end)
				{
					$mayFirst = Carbon::create($currentStart->year, 6, 1);

					if ($currentStart->lt($mayFirst))
					{
						$slabEnd = $mayFirst->copy()->subDay();
					}
					else
					{
						$slabEnd = Carbon::create($currentStart->year + 1, 6, 1)->subDay();
					}

					if ($slabEnd->gt($end))
					{
						$slabEnd = $end;
					}

					$salary = $this->calculateOrderSalary($currentStart, $slabEnd, $basePrice);
					$gst    = $salary * 0.18;
					$total  = $salary + $gst;

					$summary['salary'] += $salary;
					$summary['gst']    += $gst;
					$summary['total']  += $total;

					$currentStart = $slabEnd->copy()->addDay()->startOfDay();
					$basePrice *= 1.05;
				}
				
			}
			
			
			
			return [
				'salary' => round($summary['salary'],2),
				'gst'    => round($summary['gst'],2),
				'total'  => round($summary['total'],2),
			];
		}
		catch(Exception $e)
		{
			Log::error('Error : '.$e->getMessage());
			
		}
	}	

	private function calculateOrderSalary($start,$end,$basePrice)
	{
		$current 	= 	$start->copy();
		$salary 	=	0;

		while($current<=$end)
		{
			$daysInMonth	=	$current->daysInMonth;
			$perDaySalary 	= 	$basePrice / $daysInMonth;
			
			$salary += $perDaySalary;

			$current->addDay();
		}

		return $salary;
	}


	public function calculateIndividualOrderSummary($startDate,$endDate, $resources,$vendor)
	{
		try
		{
			$summary = [
				'salary' => 0,
				'gst'    => 0,
				'total'  => 0,
			];
			
			$cutoff		=	Carbon::parse('2025-07-01');
			foreach($resources as $resource)
			{
				$start 	= 	Carbon::parse($resource->startDate);
				$end   	=	Carbon::parse($resource->endDate);
				
				$start 	= 	$start->max($cutoff);
				
				$rtId	=	DB::table('remuneration_rate_list')
							->where('startDate','<=',$start)
							->where('endDate', '>=',$start)
							->where('categoryid',2)
							->value('rateid');

				if($rtId)
				{
					$price	=	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price','a.startDate','a.endDate')
								->join('remuneration_tbl as b', function ($join) {
								$join->on('b.sectorid', '=', 'a.sectorid')
									 ->on('b.positionid', '=', 'a.positionid');
								})
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->whereIn('a.deployment_status',['Pending','Active','Extended','Released'])
								->where('b.rateid', $rtId)
								->where('a.deploymentid',$resource->deploymentid)
								->first();							
					

					$start	= 	Carbon::parse($price->startDate);
					$end	=	Carbon::parse($price->endDate);
					
					$start = $start->max($cutoff);
						
					$basePrice 		= 	$price->base_price;
					$currentStart	= 	$start->copy();
			
					while($currentStart<=$end)
					{
						$mayFirst = Carbon::create($currentStart->year, 6, 1);

						if ($currentStart->lt($mayFirst))
						{
							$slabEnd = $mayFirst->copy()->subDay();
						}
						else
						{
							$slabEnd = Carbon::create($currentStart->year + 1, 6, 1)->subDay();
						}

						if ($slabEnd->gt($end))
						{
							$slabEnd = $end;
						}


						$salary	= 	$this->calculateOrderSalary($currentStart,$slabEnd,$basePrice);
						$gst	= 	$salary * 0.18;
						$total	= 	$salary + $gst;


						$summary['salary'] += $salary;
						$summary['gst']    += $gst;
						$summary['total']  += $total;


						$currentStart = $slabEnd->copy()->addDay()->startOfDay();
						$basePrice *= 1.05;
					}
				}
			}
			
			
			
			return [
				'salary' => round($summary['salary'],2),
				'gst'    => round($summary['gst'],2),
				'total'  => round($summary['total'],2),
			];
		}
		catch(Exception $e)
		{
			Log::error('Error : '.$e->getMessage());
			
		}
	}	

	public function calculateIndividualAsOrderSummary($startDate,$endDate, $resources)
	{
		try
		{

			$summary = [
				'salary' => 0,
				'gst'    => 0,
				'total'  => 0,
			];

			foreach($resources as $resource)
			{
				$start = Carbon::parse($resource->startDate);
				$end   = Carbon::parse($endDate);

				$basePrice = $resource->base_price;
				$currentStart = $start->copy();

				while ($currentStart <= $end)
				{
					$mayFirst = Carbon::create($currentStart->year, 6, 1);

					if ($currentStart->lt($mayFirst))
					{
						$slabEnd = $mayFirst->copy()->subDay();
					}
					else
					{
						$slabEnd = Carbon::create($currentStart->year + 1, 6, 1)->subDay();
					}

					if ($slabEnd->gt($end))
					{
						$slabEnd = $end;
					}

					$salary = $this->calculateOrderSalary($currentStart, $slabEnd, $basePrice);
					$gst    = $salary * 0.18;
					$total  = $salary + $gst;

					$summary['salary'] += $salary;
					$summary['gst']    += $gst;
					$summary['total']  += $total;

					$currentStart = $slabEnd->copy()->addDay()->startOfDay();
					$basePrice *= 1.05;
				}
				
			}
			
			return [
				'salary' => round($summary['salary'],2),
				'gst'    => round($summary['gst'],2),
				'total'  => round($summary['total'],2),
			];
		}
		catch(Exception $e)
		{
			Log::error('Error : '.$e->getMessage());
			
		}
	}	
	
    public function getCalculatedOrderValue($vendor,$resources)
    {
        $result 	= 	[];
        $grandTotal = 	0;
        foreach($resources as $resource)
		{
			$start 		= 	Carbon::parse($resource->startDate);
			$end 		= 	Carbon::parse($resource->endDate);


			$rtId		=	DB::table('remuneration_rate_list')
							->where('startDate','<=',$start)
							->where('endDate', '>=',$start)
							->where('categoryid',$vendor->categoryid)
							->value('rateid');
			if($rtId)
			{
				$price	= 	DB::table('remuneration_tbl as a')
							->select('a.remuneration','b.sectorname','c.consultantposition')
							->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
							->where('a.categoryid', $vendor->categoryid)
							->where('a.tierid', $vendor->tierid)
							->where('a.sectorid', $resource->sectorid)
							->where('a.positionid', $resource->positionid)
							->where('a.experiencelevel', $resource->experiencelevel)
							->where('a.rateid',$rtId)
							->first();							
				
				$basePrice 		= 	$price->remuneration;
				$currentStart 	= 	$start->copy();
				$resourceRows 	= 	[];
				
				
				
				while($currentStart<=$end)
				{
					$year		=	$currentStart->year;
					$mayFirst	= 	Carbon::create($currentStart->year,6,1);
					
					if($currentStart->lt($mayFirst))
					{
						$slabEnd = $mayFirst->copy()->subDay();
					}
					else
					{
						$slabEnd = Carbon::create($currentStart->year + 1,6,1)->subDay();
					}

					if($slabEnd->gt($end))
					{
						$slabEnd = $end;
					}

					$salary 	= 	$this->calculateSalary($currentStart,$slabEnd,$basePrice);
					$operating	=	$salary*($pricing->operatingmargin/100);
					$gst 		=	($salary+$operating) * ($pricing->tax/100);
					$withGst	= 	($salary+$operating) + $gst;
					$admin 		= 	$withGst * ($pricing->admincharge/100);
					$total 		= 	$withGst + $admin;

					$resourceRows[] = [
						'slab' 		=> 	$currentStart->format('d M Y').' - '.$slabEnd->format('d M Y'),
						'basePrice'	=>	round($basePrice,2),
						'salary' 	=> 	round($salary,2),
						'operating' => 	round($operating,2),
						'gst' 		=> 	round($gst,2),
						'admin' 	=> 	round($admin,2),
						'total' 	=> 	round($total,2)
					];

					$grandTotal+= $total;

					$currentStart	=	$slabEnd->copy()->addDay()->startOfDay();

					$basePrice	=	$basePrice * 1.05;
				}
				if($vendor->categoryid==2)
				{
					$result[] = [
						'sector'	=> 	$price->sectorname,
						'position' 	=> 	$price->consultantposition,
						'rows' 		=> 	$resourceRows
					];
				}
				if($vendor->categoryid==1)
				{
					$result[] = [
						'role' 				=> 	$resource->role,
						'experiencelevel' 	=> 	$price->experiencelevel,
						'experience' 		=> 	$resource->experience,
						'rows' 				=> 	$resourceRows
					];				
				}
			}
        }
        return ['resources'	=>	$result,'grand_total'	=>	round($grandTotal,2)];
    }


    public function getCalculatedDemandNoteValue($resources)
    {
        $result 	= 	[];
        $grandTotal = 	0;
        foreach($resources as $resource)
		{
			$start 		= 	Carbon::parse($resource->startDate);
			$end 		= 	Carbon::parse($resource->endDate);


			$rtId		=	DB::table('remuneration_rate_list')
							->where('startDate','<=',$start)
							->where('endDate','>=',$start)
							->where('categoryid',$resource->categoryid)
							->value('rateid');

			if($rtId)
			{
				$price	= 	DB::table('remuneration_tbl as a')
							->select('a.remuneration','b.sectorname','c.consultantposition')
							->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
							->where('a.categoryid',$resource->categoryid)
							->where('a.tierid',$resource->tierid)
							->where('a.sectorid',$resource->sectorid)
							->where('a.positionid',$resource->positionid)
							->where('a.rateid',$rtId)
							->first();							
				
				$basePrice 		= 	$price->remuneration;
				$currentStart 	= 	$start->copy();
				$resourceRows 	= 	[];
				
				$pricing	=	DB::table('pricing_tbl')
								->where('categoryid',$resource->categoryid)
								->where('tierid',$resource->tierid)
								->where('isActive',1)
								->first();
				
				while($currentStart<=$end)
				{
					$year		=	$currentStart->year;
					$mayFirst	= 	Carbon::create($currentStart->year,6,1);
					
					if($currentStart->lt($mayFirst))
					{
						$slabEnd	=	$mayFirst->copy()->subDay();
					}
					else
					{
						$slabEnd	=	Carbon::create($currentStart->year + 1,6,1)->subDay();
					}

					if($slabEnd->gt($end))
					{
						$slabEnd = $end;
					}

					$salary 	= 	$this->calculateSalary($currentStart,$slabEnd,$basePrice);
					$total 		= 	$salary;

					$resourceRows[] = [
						'slab' 		=> 	$currentStart->format('d M Y').' - '.$slabEnd->format('d M Y'),
						'basePrice'	=>	round($basePrice,2),
						'salary' 	=> 	round($salary,2),
						'total' 	=> 	round($total,2)
					];

					$grandTotal+= $total;

					$currentStart	=	$slabEnd->copy()->addDay()->startOfDay();
					$basePrice		=	$basePrice * 1.05;
				}
				
				if($resource->categoryid==2)
				{
					$result[] = [
						'sector'	=> 	$price->sectorname,
						'position' 	=> 	$price->consultantposition,
						'rows' 		=> 	$resourceRows
					];
				}
			}
        }
        return ['resources'	=>	$result,'grand_total'	=>	round($grandTotal,2)];
    }
}
?>