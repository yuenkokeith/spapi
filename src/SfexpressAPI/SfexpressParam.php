<?php

	namespace yuenkokeith\spapi\Sfexpress\SfexpressParam;

	class ParamCreateOrder
	{		
		private static $shipper = null;
		private static $recipient = null;
		private static $customerClearanceDetail = null;
		private static $specialServices = null;
		private static $sfaccount = null;

		public static function getShipper()
		{
			return self::$shipper;
		}

		public static function getRecipient()
		{
			return self::$recipient;
		}

		public static function getCustomerClearanceDetail()
		{
			return self::$customerClearanceDetail;
		}

		public static function getSpecialServices()
		{
			return self::$specialServices;
		}

		public static function getSfaccount()
		{
			return self::$sfaccount;
		}

		public static function addShipper($address, $city, $company, 
										$contact, $mobile, $province, 
										$shipperCode, $tel)
		{
			self::$shipper = '"consigneeInfo": { 
										"address": "' . $address . '", 
										"city": "' . $city .'", 
										"company": "' . $company .'", 
										"contact": "' . $contact .'", 
										"mobile": "' . $mobile .'", 
										"province": "' . $province .'", 
										"shipperCode": "' . $shipperCode .'", 
										"tel": "' . $tel .'" 
						 			}';
		}

		public static function addRecipient($address, $city, $company, $contact,
											$mobile, $province, $shipperCode, $tel)
		{
				self::$recipient = '"deliverInfo": {
								"address": "' . $address . '", 
								"city": "' . $city . '", 
								"company": "' . $company . '", 
								"contact": "' . $contact . '", 
								"mobile": "' . $mobile . '", 
								"province": "' . $province . '", 
								"shipperCode ": "' . $shipperCode . '", 
								"tel": "' . $tel . '"}';
		}

		public static function addCustomClearanceDetail($cargo, $cargoAmount, $cargoCount, $cargoTotalWeight,
													$cargoUnit, $cargoWeight, $parcelQuantity)
		{
			self::$customerClearanceDetail = '"cargoInfo": {
												"cargo": "' . $cargo . '", 
												"cargoAmount": "' . $cargoAmount . '", 
												"cargoCount": "' . $cargoCount . '", 
												"cargoTotalWeight": "' . $cargoTotalWeight . '", 
												"cargoUnit": "' . $cargoUnit . '", 
												"cargoWeight": "' . $cargoWeight . '", 
												"parcelQuantity": ' . $parcelQuantity . ' 
											}';
		}

		public static function addSpecialServices($name, $value)
		{
			self::$specialServices = '"addedServices": [ {"name": "' . $name . '", "value": "' . $value . '" } ]';
		}

		public static function addSfAccount($custMonthlyId, $expressType, $isDoCall, $payMethod, $remark, $sendStartTime)
		{
			self::$sfaccount = '"expressType": ' . $expressType . ',
						"isDoCall": ' . $isDoCall . ',
						"needReturnTrackingNo": 1,
						"payMethod": "' . $payMethod . '",
						"remark": "' . $remark . '", 
						"sendStartTime": "' . $sendStartTime . '", 
						"custId": "' . $custMonthlyId . '"';
		}

	}

	class ParamGetOrderInfo {
		function __construct($ecshipUsername, $integratorUsername, $orderno) 
		{
			$this->ecshipUsername = $ecshipUsername;
			$this->integratorUsername = $integratorUsername;
			$this->orderNo = $orderno;
		}
	}

	class ParamGetTTInfo {
		function __construct($ecshipUsername, $integratorUsername, $itemno, $lang) 
		{
			$this->ecshipUsername = $ecshipUsername;
			$this->integratorUsername = $integratorUsername;
			$this->itemNo = $itemno;
			$this->language = $lang;
		}
	}

	class Request
	{
		public static $request = null;

		function __construct($requestType, $trackOrOrderNo="", $shipper="", $recipient="", $customerClearanceDetail="", $specialServices="", $sfaccount="")
		{
			$transdate = date("Ymd");
			$transno = mt_rand(1000000000, 9999999991);
			$transMessageId = $transdate . $transno;

			if($requestType=="applyToken")
			{
				return self::$request = '{"head":{"transType":"301","transMessageId":"' . $transMessageId . '"},"body":{}}';
			}
			else if($requestType=="getToken")
			{
				return self::$request = '{"head":{"transType":"300","transMessageId":"' . $transMessageId . '"},"body":{}}';
			}
			else if($requestType=="refreshToken")
			{
				return self::$request = '{"head":{"transType":"302","transMessageId":"' . $transMessageId . '"},"body":{}}';
			}
			else if($requestType=="getTTInfo")
			{
				return self::$request = '{"head":{"transType":"501","transMessageId":"' . $transMessageId . '"},"body":{"trackingType": "1","trackingNumber": "' . $trackOrOrderNo .'","methodType": "1"}}';  // transType:"300"  or "301" **apply  or 250 ** serviceInfo **  or 501 ** route info **
			}
			else if($requestType=="getOrderInfo")
			{
				return self::$request = '{"head":{"transType":"203","transMessageId":"' . $transMessageId . '"},"body":{"orderId": "' . $trackOrOrderNo .'"}}';  // transType:"300"  or "301" **apply  or 250 ** serviceInfo **  or 501 ** route info **
			}
			else if($requestType=="createOrder")
			{
				return self::$request = '{"head":{"transType":"200","transMessageId":"' . $transMessageId . '"},"body": {"orderId": "' . $trackOrOrderNo . '",
											' . $sfaccount . ',
											' . $customerClearanceDetail . ',
											' . $specialServices . ',
											' . $shipper . ',
											' . $recipient . '
										}}';
			}
    	}

		public static function getRequest()
		{
			return self::$request;
		}

	}

?>