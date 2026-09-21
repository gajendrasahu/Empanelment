<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Hash;
use Session;

class CustomVendorAuthController extends Controller
{
    public function vendorlogin(){

        return view('vendorauth.login');
    }

    public function loginVendor(Request $request)
	{

        $this->validate($request, [
            'loginid' =>  'required',
            'password' => 'required',
        ]);        

		$vendor = DB::table('vendor_tbl')
					->where(function($query) use ($request) {
						$query->where('loginid','=', $request->loginid)
							  ->orWhere('mobilenumber','=',$request->loginid);
					})
					->where('loginpassword','=',$request->password)
					->first();
		
		if($vendor)
        {
            if($request->password==$vendor->loginpassword)
            {
				if($vendor->isverified==0)
				{
					return back()->with('fail','YOUR ACCOUNT IS NOT VERIFIED. PLEASE CONTACT THE SCREW DRIVER SUPPORT TEAM OR WAIT TILL ACCOUNT VERIFICATION PROCESS IS COMPLETED.');
				}
				else if($vendor->isactive==0)
				{
					return back()->with('fail','YOUR ACCOUNT IS NOT ACTIVE. PLEASE CONTACT THE SCREW DRIVER SUPPORT TEAM.');
				}
				else
                {
                    $request->session()->put('vendorId',$vendor->vendorid);
                    $request->session()->put('vendorName',$vendor->name);
					$request->session()->put('isselfemployeed',$vendor->isselfemployeed);
					$request->session()->put('isemployee',$vendor->isemployee);
					$request->session()->put('verificationstatus',$vendor->verificationstatus);
					$request->session()->put('isverified',$vendor->isverified);
					
                    Session::put('vendormenu', 'dashboard');
					Session::put('vendorsubmenu','');
                    return redirect('vendorpanel/dashboard')->with('success','YOU ARE LOGGED IN SUCCESSFULLY');
                }
            }
            else
            {
                return back()->with('fail','THE LOGIN DETAILS PROVIDED ARE INVALID.');
            }
        }
        else
        {
            return back()->with('fail','THE LOGIN DETAILS PROVIDED ARE INVALID.');
        }
    }

    public function dashboard()
    {
        $data = array();
        if(Session::has('vendorId'))
        {
            $logindata = DB::table('vendor_tbl')
					->select('vendorid','name','mobilenumber','isactive','isselfemployeed','isemployee','isverified','verificationstatus','profilepic')
                    ->where('vendorid','=',Session::get('vendorId'))
                    ->first();
        }
        Session::put('vendormenu', 'dashboard');
		Session::put('vendorsubmenu', '');
        return view('vendorpanel/dashboard',compact('logindata'));
    }

    public function vendorlogout()
    {
        if(Session::has('vendorId'))
        {
            Session::pull('vendorId');
			Session::pull('vendorName');
			Session::pull('isselfemployeed');
			Session::pull('isemployee');
            return redirect('vendorpanel');
        }
    }
	public function setLocale($locale)
	{
		if (in_array($locale, ['en', 'mr', 'hn'])) {
			Session::put('locale', $locale);
		}

		return redirect()->back();
	}	
	
}
