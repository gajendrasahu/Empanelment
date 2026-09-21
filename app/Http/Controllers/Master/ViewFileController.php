<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ViewFileService;
use Illuminate\Support\Facades\Crypt;
class ViewFileController extends Controller
{

    protected $fileService;

    public function __construct(ViewFileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function showFile(Request $request,$encrypted)
    {
		try
		{
			$filename = Crypt::decrypt($encrypted);
		}
		catch(\Exception $e)
		{
			abort(404, 'Invalid file.');
		}

		return $this->fileService->serve($filename, true);
	}	
}
