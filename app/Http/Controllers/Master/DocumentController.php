<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    public function readScopeDoc()
    {
        $path = storage_path("documents/scope.docx");

        if (!file_exists($path)) {
            return response("File not found", 404);
        }

        $phpWord = IOFactory::load($path);
        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

        ob_start();
        $htmlWriter->save('php://output');
        $html = ob_get_clean();

        return response($html);
    }

    public function readAboutDoc()
    {
        $path = storage_path("documents/aboutproject.docx");

        if (!file_exists($path)) {
            return response("File not found", 404);
        }

        $phpWord 	= IOFactory::load($path);
        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

        ob_start();
        $htmlWriter->save('php://output');
        $html = ob_get_clean();

        return response($html);
    }

    public function readOtherDoc()
    {
        $path = storage_path("documents/otherproject.docx");

        if (!file_exists($path)) {
            return response("File not found", 404);
        }

        $phpWord 	= IOFactory::load($path);
        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

        ob_start();
        $htmlWriter->save('php://output');
        $html = ob_get_clean();

        return response($html);
    }

    public function showVendorsList()
    {
		$data	=	DB::table('tiermaster_tbl')->get();
		
		foreach($data as $tier)
		{
			$tier->vendors	=	DB::table('users_tbl as a')
									->select('a.*','b.vendorid')
									->leftJoin('vendor_tbl as b','b.userid','=','a.userid')
									->where('b.tierid',$tier->tierid)
									->where('a.isvendor',1)
									->get();
			foreach($tier->vendors as $vendor)
			{
				$vendor->sectors	=	DB::table('vendor_sector as a')
											->select('b.sectorname')
											->leftjoin('sector_tbl as b','b.sectorid','=','a.sectorid')
											->where('a.vendorid',$vendor->vendorid)
											->get();
			}
		}
		
		$html	=	view('admin.ajaxpages.tierwisevendorTable',['data'=>$data])->render();
		//return response()->json(['status'=>200,'message'=>'RECORD SAVED SUCCESSFULLY!','formhtml' => $html]);
		
        return response($html);
    }
	
}