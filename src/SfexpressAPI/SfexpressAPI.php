<?php

	namespace yuenkokeith\spapi\Sfexpress;
	require_once('SfexpressParam.php');
	use yuenkokeith\spapi\Sfexpress\SfexpressParam;

	class Sfexpress extends \yuenkokeith\spapi\SpAPI implements SfInterface 
	{
		private static $access_token = null;
		private static $refresh_token = null;
		private static $app_id = null;
		private static $app_key = null;
		private static $result = null;
		private static $resultJson = null;
		private static $isLiveMode = false;

		// @Order info
		private static $shipper = null;
		private static $recipient = null;
		private static $customerClearanceDetail = null;
		private static $specialServices = null;
		private static $sfaccount = null;
		private static $custMonthlyId = null;

		function __construct()
		{
			self::$app_id = "00027795";
			self::$app_key = "CA66B63E5A48A408FD845BF752FDC2BC";
			self::$custMonthlyId = '7550010173';
			self::$isLiveMode = false;
			self::applyToken();
		}

		public static function setCredential($app_id="", $app_key="", $custMonthlyId="", $isLiveMode=true)
		{
			// Get from DB later
			self::$app_id = $app_id;
			self::$app_key = $app_key;
			self::$custMonthlyId = $custMonthlyId;
			self::$isLiveMode = $isLiveMode;
			self::applyToken();
		}
		
		private static function __formatHeader($url,$data)
		{
			$temp = parse_url($url);
			$query = isset($temp['query']) ? $temp['query'] : ''; 
			$path = isset($temp['path']) ? $temp['path'] : '/'; 
			$header = array (
				"POST {$path}?{$query} HTTP/1.1",
				"Host: {$temp['host']}",
				"Content-Type: application/json",  
				"Content-length: ".strlen($data),
				"Connection: Close" 
			);
			return $header; 
		}

		private static function __getClient($method_name)
		{
			if(self::$isLiveMode) {
				$host = "open-prod.sf-express.com";
			} else { // deve test mode
				$host = "open-sbox.sf-express.com";
			}
	
			switch ($method_name)
			{
				case "applyToken":
					return "https://" . $host . "/public/v1.0/security/access_token/sf_appid/" . self::$app_id . "/sf_appkey/" . self::$app_key;
					break;

				case "getToken":
					return "https://" . $host . "/public/v1.0/security/access_token/query/sf_appid/" . self::$app_id . "/sf_appkey/" . self::$app_key;
					break;

				case "refreshToken":
					return "https://" . $host . "/public/v1.0/security/refresh_token/access_token/" . self::$access_token . "/refresh_token/" . self::$refresh_token . "/sf_appid/" . self::$app_id . "/sf_appkey/" . self::$app_key;
					break;

				case "getTTInfo":
					return "https://" . $host . "/rest/v1.0/route/query/access_token/" . self::$access_token . "/sf_appid/" . self::$app_id . "/sf_appkey/" . self::$app_key;
					break;

				case "getOrderInfo":
					return "https://" . $host . "/rest/v1.0/order/query/access_token/" . self::$access_token . "/sf_appid/" . self::$app_id . "/sf_appkey/" . self::$app_key;
					break;

				case "createOrder":
					return "https://" . $host . "/rest/v1.0/order/access_token/" . self::$access_token . "/sf_appid/" . self::$app_id . "/sf_appkey/" . self::$app_key;
					break;
			}
		}

		private static function __getRequest($requestType, $trackNo='')
		{
			$request = new \yuenkokeith\spapi\Sfexpress\SfexpressParam\Request($requestType, $trackNo, self::$shipper, self::$recipient, self::$customerClearanceDetail, self::$specialServices, self::$sfaccount);
			return $request::getRequest();
		}

		private static function __processRequest($requestJsonData, $client)
		{
			$url = $client;
			$ch=curl_init($url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_HEADER,0);
			curl_setopt($ch, CURLOPT_TIMEOUT,5);
			$data = $requestJsonData;
			curl_setopt($ch,CURLOPT_POST, true);
			$header = self::__formatHeader($url,$data);
			curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
			$result=curl_exec($ch);
			self::$resultJson = $result;
		}

		private static function __getOrderId()
		{
			$array = json_decode(self::$resultJson,TRUE);
			return $array['body']['orderId'];
		}

		private static function __getMailNo()
		{
			$array = json_decode(self::$resultJson,TRUE);
			return $array['body']['mailNo'];
		}

		private static function __getCode()
		{
			if(self::$result==null)
			{
				$array = json_decode(self::$resultJson,TRUE);
				return $array['head']['code'];
			}
		}

		public static function setShipper($address, $city, $company,
										$contact, $mobile, $province,
										$shipperCode, $tel)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Sfexpress\SfexpressParam\ParamCreateOrder();
			$paramCreateOrder::addShipper($address, $city, $company,
										$contact, $mobile, $province,
										$shipperCode, $tel);
			self::$shipper = $paramCreateOrder::getShipper();
		}

		public static function setRecipient($address, $city, $company, $contact,
											$mobile, $province, $shipperCode, $tel)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Sfexpress\SfexpressParam\ParamCreateOrder();
			$paramCreateOrder::addRecipient($address, $city, $company, $contact,
											$mobile, $province, $shipperCode, $tel);
			self::$recipient = $paramCreateOrder::getRecipient();
		}

		public static function setCustomerClearanceDetail($cargo, $cargoAmount, $cargoCount, $cargoTotalWeight,
													$cargoUnit, $cargoWeight, $parcelQuantity)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Sfexpress\SfexpressParam\ParamCreateOrder();
			$paramCreateOrder::addCustomClearanceDetail($cargo, $cargoAmount, $cargoCount, $cargoTotalWeight,
														$cargoUnit, $cargoWeight, $parcelQuantity);
			self::$customerClearanceDetail = $paramCreateOrder::getCustomerClearanceDetail();
		}

		public static function setSpecialServices($name, $value)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Sfexpress\SfexpressParam\ParamCreateOrder();
			$paramCreateOrder::addSpecialServices($name, $value);
			self::$specialServices = $paramCreateOrder::getSpecialServices();
		}

		public static function setSfAccount($expressType, $isDoCall, $payMethod, $remark, $sendStartTime)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Sfexpress\SfexpressParam\ParamCreateOrder();
			$paramCreateOrder::addSfAccount(self::$custMonthlyId, $isDoCall, $expressType, $payMethod, $remark, $sendStartTime);
			self::$sfaccount = $paramCreateOrder::getSfaccount();
		}

		public static function applyToken()
		{
			$client = self::__getClient("applyToken");
			
			$requestJsonData = self::__getRequest("applyToken");
			self::__processRequest($requestJsonData, $client);
			$array = json_decode(self::$resultJson,TRUE);
			self::$result = $array;
			if($array['head']['code']=="EX_CODE_OPENAPI_0200")
			{
				self::$access_token = $array['body']['accessToken'];
				self::$refresh_token = $array['body']['refreshToken'];
			}
		}

		public static function getToken()
		{
			$client = self::__getClient("getToken");
			$requestJsonData = self::__getRequest("getToken");
			self::__processRequest($requestJsonData, $client);
			$array = json_decode(self::$resultJson,TRUE);
			self::$result = $array;
			if($array['head']['code']=="EX_CODE_OPENAPI_0200")
			{
				self::$access_token = $array['body']['accessToken'];
				self::$refresh_token = $array['body']['refreshToken'];
			}
			else if($array['head']['code']=="EX_CODE_OPENAPI_0103" || // 访问令牌不存在 
					$array['head']['code']=="EX_CODE_OPENAPI_0105")   // 访问令牌过期 
			{
				// apply new accessToken
				self::applyToken();
			}
		}

		public static function refreshToken()
		{
			// if accessToken expired after 12 hours then need to re-apply
			$client = self::__getClient("refreshToken");
			$requestJsonData = self::__getRequest("refreshToken");
			self::__processRequest($requestJsonData, $client);
			$array = json_decode(self::$resultJson,TRUE);
			self::$result = $array;
			if($array['head']['code']=="EX_CODE_OPENAPI_0200")
			{
				self::$access_token = $array['body']['accessToken'];
				self::$refresh_token = $array['body']['refreshToken'];
			}
			else if($array['head']['code']=="EX_CODE_OPENAPI_0104" || // 更新令牌不存在 
					$array['head']['code']=="EX_CODE_OPENAPI_0106" || $array==null or $array=="")   // 更新令牌过期
			{
				// apply new accessToken
				self::applyToken();
			}
		}

		protected static function __doSfExpressToBexpressLocalTrackingStatus($trackingStatus)
		{
			switch ($trackingStatus) // SfExpress
			{
				case "50":
					return "1"; //Bexpress 1. 上門取件/ 到店寄件 Picked up /Dropped off
					break;

				case "34":
					return "3"; //Bexpress 3. 貨物派送中 Delivery in progress
					break;

				case "80": case "8000":
					return "4"; //Bexpress 4. 完成派送 Shipment is received
					break;

				case "627": case "626": case "70":
				 case "99": case "631": case "33":
					return "5"; /*Bexpress 5. 異常事件(更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送) 
								Exception (Change the scheduled delivery date/ Loss/Damage/Failed Attempt)
								*/
					break;	

				default:
					return "3"; //Bexpress 3. 貨物派送中 Delivery in progress
					break;
			}
		}

		protected static function __doSfExpressToBexpressCrossTrackingStatus($trackingStatus)
		{
			switch ($trackingStatus) // SfExpress
			{
				case "50":
					return "1"; //Bexpress 1. 上門取件/ 到店寄件 Picked up /Dropped off
					break;

				case "34":
					return "5"; //Bexpress 5. 貨物派送中 Delivery in progress
					break;

				case "80": case "8000":
					return "6"; //Bexpress 6. 完成派送 Shipment is received
					break;

				case "627": case "626": case "70": case "99":
				case "631": case "33":
					return "7"; //Bexpress 7. 異常事件(清關延誤/ 更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送)
					break;

				default:
					return "5"; //Bexpress 5. 貨物派送中 Delivery in progress
					break;
			}
		}

		private static function __convertTrackingStatus($callname, $result)
		{
			$max = sizeof($result['body']);
			if($callname=="getLocalTrackingStatus") {
				for($i=0; $i<$max; $i++)
				{
					self::$result['body'][$i]['opcode'] = self::__doSfExpressToBexpressLocalTrackingStatus($result['body'][$i]['opcode']);
				}
			} else if($callname=="getCrossTrackingStatus") {
				for($i=0; $i<$max; $i++)
				{
					self::$result['body'][$i]['opcode'] = self::__doSfExpressToBexpressCrossTrackingStatus($result['body'][$i]['opcode']);
				}
			}
		}

		public static function getTTInfo($trackNo)
		{
			// trackNo=444004789464
			$client = self::__getClient("getTTInfo");
			$requestJsonData = self::__getRequest("getTTInfo", $trackNo);
			self::__processRequest($requestJsonData, $client);
		}

		private static function __getTTInfo($orderno)
		{
			// trackNo=444004789464
			$client = self::__getClient("getTTInfo");
			$requestJsonData = self::__getRequest("getTTInfo", $orderno);
			self::__processRequest($requestJsonData, $client);
		}

		public static function getLocalTrackingStatus($orderno)
		{
			self::__getTTInfo($orderno);
			self::$result = self::getOriginalResult();
			self::__convertTrackingStatus("getLocalTrackingStatus", self::$result);

			$max = sizeof(self::$result['body']);
			$data = array ();
			$dataArr = array ();
			$tempArr = array ();
			$lastState = self::$result['body'][$max-1]['opcode'];
		
			if(self::$result['head']['code']== 'EX_CODE_OPENAPI_0200')
			{
				for($i=0; $i<$max; $i++)
				{
					$tempArr = array (
										'time' => self::$result['body'][$i]['acceptTime'],
										'description' => self::$result['body'][$i]['remark'],
									);
					array_push($dataArr,$tempArr);
				}

				$data = array (
							'status' => 200,
							'state'=> $lastState,
							'data'=> $dataArr
						);
			}
			else
			{
				$data = array ('status' => 500);
			}

			self::$result = $data;
			return self::$result;
		}
		
		public static function getCrossTrackingStatus($orderno)
		{
			self::__getTTInfo($orderno);
			self::$result = self::getOriginalResult();
			self::__convertTrackingStatus("getCrossTrackingStatus", self::$result);
			
			$max = sizeof(self::$result['body']);
			$data = array ();
			$dataArr = array ();
			$tempArr = array ();
			$lastState = self::$result['body'][$max-1]['opcode'];
		
			if(self::$result['head']['code']== 'EX_CODE_OPENAPI_0200')
			{
				for($i=0; $i<$max; $i++)
				{
					$tempArr = array (
										'time' => self::$result['body'][$i]['acceptTime'],
										'description' => self::$result['body'][$i]['remark'],
									);
					array_push($dataArr,$tempArr);
				}

				$data = array (
							'status' => 200,
							'state'=> $lastState,
							'data'=> $dataArr
						);
			}
			else
			{
				$data = array ('status' => 500);
			}

			self::$result = $data;
			
			return self::$result;
		}

		public static function getOrderInfo($orderid)
		{
			$client = self::__getClient("getOrderInfo");
			$requestJsonData = self::__getRequest("getOrderInfo", $orderid);
			self::__processRequest($requestJsonData, $client);
		}

		public static function createOrder($orderid)
		{
			$client = self::__getClient("createOrder");
			$requestJsonData = self::__getRequest("createOrder", $orderid);
			self::__processRequest($requestJsonData, $client);
			// needs to execute 'getOrderInfo' because trackNo/wayillNo data is not in create order return
			self::getOrderInfo(self::__getOrderId());
			while(self::__getMailNo()=="")
			{
				self::getOrderInfo(self::__getOrderId());
			}
		}

		public static function spCreateOrder($data)
		{
			/*
				 setShipper($address, $city, $company, $contact, $mobile, $province, $shipperCode, $tel
			*/
			self::setShipper($data['SenderAddress'], $data['SenderCity'], $data['SenderCompany'], $data['SenderName'], $data['SenderPhoneNo'] . " " . $data['SenderPhoneNoExt'], $data['SenderProvince'], $data['SenderPostalCode'], $data['SenderPhoneNo'] . " " . $data['SenderPhoneNoExt']);
			
			/*
				setRecipient($address, $city, $company, $contact, $mobile, $province, $shipperCode, $tel
			*/
			self::setRecipient($data['ReceiverAddress'], $data['ReceiverCity'], $data['ReceiverCompany'], $data['ReceiverName'], $data['ReceiverPhoneNo'] . " " . $data['ReceiverPhoneNoExt'], $data['ReceiverProvince'], $data['ReceiverPostalCode'], $data['ReceiverPhoneNo'] . " " . $data['ReceiverPhoneNoExt']);
			
			/*
				setCustomerClearanceDetail($cargo, $cargoAmount, $cargoCount, $cargoTotalWeight, $cargoUnit, $cargoWeight, $parcelQuantity
			*/
			self::setCustomerClearanceDetail($data['FirstShipmentItem'] . " " . $data['SecondShipmentItem'] . " " . $data['ThirdShipmentItem'],
											$data['TotalDeclaredValue(USD)'], $data['NoOfPackage'], $data['ChargeableWeight(kg)'], 'kg', $data['ChargeableWeight(kg)'], $data['NoOfPackage']);
			
			/*
				setSpecialServices($name, $value)
			*/
			self::setSpecialServices('', '0');

			/*
				setSfAccount($expressType, $isDoCall, $payMethod, $remark, $sendStartTime)
				expressType = 1(标准快递)
				self::$sfaccount = '"expressType": ' . $expressType . ',
						"isDoCall": ' . $isDoCall . ' ,
						"needReturnTrackingNo": 1,
						"payMethod": "' . $payMethod . '",
						"remark": "' . $remark . '",
						"sendStartTime": "' . $sendStartTime . '",
						"custId": "' . $custMonthlyId . '"';
			*/
			self::setSfAccount(1, 1, 1, $data['ItemDescription'],  date("Y-m-d h:m:s")); // expressType, isDoCall, payMethod, remark, sendStartTime
			
			/*
				createOrder($orderid)
			*/
			self::createOrder($data['bexOrderId']);

		}

		public static function printToken()
		{
			echo self::$access_token . "<br/>" . self::$refresh_token;
		}

		public static function getOrderResult()
		{
			$array = json_decode(self::$resultJson,TRUE);
			self::$result = $array;

			// generic化 return data
			if(self::$result['head']['code']=='EX_CODE_OPENAPI_0200') {
					self::$result = Array('status'=>200, 'waybillno'=> self::$result['body']['mailNo']);
			} else {
					self::$result = Array('status' => 500,
									'code' => '',
									'description' => ''
				);
			}

			return self::$result;
		}

		public static function getOriginalResult() // createOrder/Tracking result return
		{
			$array = json_decode(self::$resultJson,TRUE);
			self::$result = $array;
			return self::$result;
		}

		public static function getJsonResult()
		{
			return self::$resultJson;
		}
		
		public static function setLiveMode($onoff)
		{
			self::$isLiveMode= $onoff;
		}

	}

	// 声明一个'iTemplate'接口而已
	interface SfInterface
	{
		public static function setCredential($app_id="", $app_key="", $custMonthlyId="", $isLiveMode=true);
		public static function setShipper($address, $city, $company,
												$contact, $mobile, $province,
												$shipperCode, $tel);
		public static function setRecipient($address, $city, $company, $contact, $mobile, $province, $shipperCode, $tel);
		public static function setCustomerClearanceDetail($cargo, $cargoAmount, $cargoCount, $cargoTotalWeight,
															$cargoUnit, $cargoWeight, $parcelQuantity);
		public static function setSpecialServices($name, $value);
		public static function setSfAccount($expressType, $isDoCall, $payMethod, $remark, $sendStartTime);
		public static function applyToken();
		public static function getToken();
		public static function refreshToken();
		public static function getTTInfo($trackNo);
		public static function getLocalTrackingStatus($orderno);
		public static function getCrossTrackingStatus($orderno);
		public static function getOrderInfo($orderid);
		public static function createOrder($orderid);
		public static function spCreateOrder($data);
		public static function printToken();
		public static function getOrderResult();
		public static function getOriginalResult(); // createOrder/Tracking result return
		public static function getJsonResult();
		public static function setLiveMode($onoff);
	}
	
?>

