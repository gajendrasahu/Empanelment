<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\DB;

class JWTAuthHelper
{
    private static $secretKey;

	public static function init()
	{
		self::$secretKey = env('JWT_SECRET', 'default_secret'); // Use a fallback value (optional)
	}

    public static function generateToken($customerId)
    {
        $payload = [
            'iss' => request()->getHost(),
            'iat' => time(),
            'sub' => $customerId,
        ];
		
		/*
        $payload = [
            'iss' => request()->getHost(), // Issuer
            'iat' => time(), // Issued at
            'exp' => time() + 60 * 60, // Expiration (1 hour)
            'sub' => $customerId, // Subject (customer ID)
        ];
		*/
		
        $token = JWT::encode($payload, self::$secretKey, 'HS256');
        DB::table('customer_tbl')->where('customerid',$customerId)->update(['access_token'=>$token]);
		
        return $token;
    }

    public static function validateToken($token)
    {
        try
		{
            $decoded = JWT::decode($token, new Key(self::$secretKey, 'HS256'));
            //return $decoded->sub;
			$customerId = $decoded->sub;
			if(self::isValidCustomer($customerId)) 
			{
				return $customerId;
			}
			else
			{
				return false;
			}			
        }
		catch(\Exception $e)
		{
            return false;
        }
    }
	private static function isValidCustomer($customerId)
	{
		return DB::table('customer_tbl')->where('customerid',$customerId)->exists();
	}	
}

JWTAuthHelper::init();
?>