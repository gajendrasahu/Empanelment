<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DisplayOrderService
{
	public static function getLastDisplayOrder($categoryId, $tableName, $fieldName, $categoryIdField='categoryid')
	{		
		return DB::table($tableName)->where($categoryIdField,$categoryId)->max($fieldName);
	}

	public function getDisplayOrder(string $table)
    {
        // Fetch the last displayorder value ordered by displayorder in descending order
        $result = DB::table($table)
                    ->orderBy('displayorder', 'desc')
                    ->first(['displayorder']);
        
        return $result ? $result->displayorder : null; // Return displayorder or null if no record found
    }	
}
