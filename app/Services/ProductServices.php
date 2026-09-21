<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProductServices
{
	public static function getItemCode()
	{
		$itemcode	=	rand('1000000001','9999999999');
		$res		=	DB::insert('insert into item_code(itemcode,creationdate) values(?,?)',[$itemcode,date('Y\-m\-d H:i:s')]);
		if(!$res)
		{
			getItemCode();
		}
		return $itemcode;
	}
}
