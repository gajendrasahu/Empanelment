<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class SmsService
{
	protected $apiKey;
	protected $senderid;
	protected $sender;
	protected $auth_template_id;
	protected $username;
	protected $password;
		
	public function __construct()
	{
		$this->username				= 	env('SMS_USER_NAME');
		$this->password 			= 	env('SMS_PASSWORD');
		$this->senderId 			= 	env('SMS_SENDER_ID');
		$this->apiKey 				=	env('SMS_API_KEY');
		$this->sender 				= 	env('SMS_USER_NAME');
		$this->auth_template_id 	= 	env('SMS_OTP_TEMPLATE_ID');
		$this->sms_type				= 	env('SMS_TYPE');
	}
	public function pushTestingMessage(string $mobile,$otp,String $smstype,$amount,$paymentlink)
	{
		$validMins 				= 	5;
		$text					=	"Your OTP for Empanelment Portal is ".$otp." CHiPS";

		$key = hash('sha512', trim($this->username) . trim($this->senderId) . trim($text) . trim($this->apiKey));
		
		$params = [
			'username'        => $this->username,
			'password'        => $this->password,
			'senderid'        => $this->senderId,
			'content'         => $text,
			'mobileno'        => $mobile,
			'smsservicetype'  => $this->sms_type,
			'key'             => $key,
			'templateid'      => $this->auth_template_id,
		];		

		try
		{
			$ch = curl_init("https://msdgweb.mgov.gov.in/esms/sendsmsrequestDLT");

			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query($params),
				CURLOPT_HTTPHEADER => [
					"Content-Type: application/x-www-form-urlencoded",
					"User-Agent: Mozilla/4.0"
				],
				CURLOPT_TIMEOUT => 20,
				CURLOPT_SSL_VERIFYPEER => false, // govt sites often fail SSL
			]);

			$response = curl_exec($ch);

			if (curl_errno($ch)) {
				Log::error("CURL Error: " . curl_error($ch));
				curl_close($ch);
				return false;
			}

			curl_close($ch);

			$data = json_decode($response);

			if(isset($data->statusCode) && ($data->statusCode == 200 || $data->statusCode == 2001))
			{
				return true;
			}

			Log::error("SMS Response : ".$response);
			return false;

		} catch (\Exception $e) {
			
			Log::error("SMS Exception: " . $e->getMessage());
			return false;
		}	
		
	}
	public function pushMessage(string $mobile,$otp,String $smstype,$amount,$paymentlink)
	{
		return true;
		
		if($smstype=='INVOICE')
		{
			$this->auth_template_id	=	env('SMS_INVOICE_TEMPLATE_ID');
			
			//$body	=	"Your passcode is $otp. You are just one step away to get the best ever service experience. Regards ScrewDriver";
			$text	=	"Invoice of INR {$amount} for Carwash has been generated. You can pay Digitally. View and pay here {#var#}. Regards, Screw Driver";
		}
		if($smstype=='PAYMENT_REQUEST')
		{
			$this->auth_template_id	=	env('SMS_INVOICE_TEMPLATE_ID');			
			$text	=	"Invoice of INR 500 for Carwash has been generated. You can pay Digitally. View and pay here {#var#}. Regards, Screw Driver";
		}
		if($smstype=='ASSIGNED')
		{
			$this->auth_template_id	=	env('SMS_ASSIGNED_TEMPLATE_ID');
			$text	=	"Your passcode is $otp. You are just one step away to get the best ever service experience. Regards ScrewDriver";
		}
		if($smstype=='BOOKING')
		{
			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('d-m-Y h.i A');
			
			$this->auth_template_id	=	"1707174478780731797";
			$text	=	"Welcome to The Screw Driver we are your service partner for booking, This is to confirm your booking of services at {$creationdate}. Regards Screw Driver";

		}
		if($smstype=='OTP')
		{
			$validMins = 5;
			$this->auth_template_id	=	"1307175499722109852";
			$text	=	"Your OTP for emapanelment portal is {$otp}. CHiPS";
		}
		if($smstype=='STARTFINISH')
		{
			$this->auth_template_id	=	"1707174486952609095";
			
			$text	=	"{$otp} is your OTP to confirmation of Sample Content Start/Complete the service, Please share this with the commander. Regards ScrewDriver";
		}
		$key = hash('sha512', trim($this->username) . trim($this->senderId) . trim($text) . trim($this->apiKey));
		
		$params = [
			'username'        => $this->username,
			'password'        => $this->password,
			'senderid'        => $this->senderId,
			'templateid'      => $this->auth_template_id,
			'content'         => $text,
			'smsservicetype'  => $this->sms_type,
			'mobileno'        => $mobile,
			'key'             => $key
		];		
		
		try
		{
			$ch = curl_init("https://msdgweb.mgov.gov.in/esms/sendsmsrequestDLT");

			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query($params),
				CURLOPT_HTTPHEADER => [
					"Content-Type: application/x-www-form-urlencoded",
					"User-Agent: Mozilla/4.0"
				],
				CURLOPT_TIMEOUT => 20,
				CURLOPT_SSL_VERIFYPEER => false, // govt sites often fail SSL
			]);

			$response = curl_exec($ch);

			if (curl_errno($ch)) {
				Log::error("CURL Error: " . curl_error($ch));
				curl_close($ch);
				return false;
			}

			curl_close($ch);

			$data = json_decode($response);

			if(isset($data->statusCode) && ($data->statusCode == 200 || $data->statusCode == 2001))
			{
				return true;
			}

			Log::error("SMS Error Response : ".$response);
			return false;

		} catch (\Exception $e) {
			
			Log::error("SMS Exception: " . $e->getMessage());
			return false;
		}	
	}

	public function pushPaymentMessage($mobile,$paymentlink,$amount,$servicetitles)
	{
		$this->auth_template_id	=	env('SMS_INVOICE_TEMPLATE_ID');

		$text	=	"Invoice of INR {$amount} for {$servicetitles} has been generated. You can pay Digitally. View and pay here '{$paymentlink}'. Regards, Screw Driver";
			
		$params = [
			'username'       => $this->username,
			'password'       => $this->password,
			'unicode'        => 'true',
			'from'           => $this->sender,
			'to'             => $mobile,
			'text'           => $text,
			'dltContentId'   => $this->auth_template_id
		];
		
		
		$ch = curl_init("https://gui.smartping.ai/fe/api/v1/send");

		//$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

		// No need to set headers or POST — it's a GET with query parameters
		$response = curl_exec($ch);
		if(curl_errno($ch))
		{
			curl_close($ch);
			return false;
		}
		else
		{
			curl_close($ch);
			$data = json_decode($response);
			
			if($data->statusCode==2001 || $data->statusCode==200)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		
	}

	
	public function sendOtp(string $mobile, string $otp)
	{
		$this->apiKey 			= "Ab52f1b208fe49237a7b7b63695c9c217";
		$this->senderid			= "HXIN1749026497IN";
		$this->auth_template_id = "1707174478869072248";
		  
		//$body = "Hi {#var#}, your booking is confirmed! ID: $otp, you can track it here from {#var#}. Regards, Nexify World";
		//$body = "Hi {#var#}, your booking is confirmed! ID: $otp, you can track it here from {#var#}. Regards, Nexify World";
		$body 	= "Your passcode is {$otp}. You are just one step away to get the best ever service experience. Regards ScrewDriver";

		// Set up cURL request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.kaleyra.io/v1/' . $this->senderid . '/messages');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "to=+91".$mobile."&type=OTP&sender=screwd&body=".$body."&template_id=".$this->auth_template_id);
		$headers 	=	array();
		$headers[] 	=	'Content-Type: application/x-www-form-urlencoded';
		$headers[] 	=	'Api-Key:' . $this->apiKey;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


		$result = curl_exec($ch);
		
		if(curl_errno($ch)) 
		{
			curl_close($ch);
			return false;
		} 
		else 
		{
			curl_close($ch);
			return true;
		}		
	}
	
	public function sendOtpTesting(string $mobile, string $otp)
	{

		$mobile = '9479020075';
		$body = urlencode("Your passcode is 1234. You are just one step away to get the best ever service experience. Regards ScrewDriver");
		$username = 'tsdtrmpg.trans';
		$password = 'djNYn'; // your actual password
		$from = 'SCREWD'; // your sender ID
		$dltContentId = '170717447886989797';

		// Construct the full URL like in Postman
		$url = "https://gui.smartping.ai/fe/api/v1/send?username=$username&password=$password&unicode=true&from=$from&to=$mobile&text=$body&dltContentId=$dltContentId";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// No need to set headers or POST — it's a GET with query parameters
		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			echo 'cURL Error: ' . curl_error($ch);
			curl_close($ch);
			return false;
		} else {
			curl_close($ch);
			echo "Success: " . $response;
			return true;
		}

	}
	
}
