<?php
	
	namespace yuenkokeith\spapi\Aramex;
	require_once('AramexParam.php');
	require_once('AramexCode.php');
	use yuenkokeith\spapi\Aramex\AramexParam;
	use yuenkokeith\spapi\Aramex\AramexCode;

	class Aramex extends \yuenkokeith\spapi\SpAPI implements AramexInterface 
	{
		// @Account Property
		private static $accountCountryCode = null;
		private static $accountEntity = null;
		private static $accountNumber = null;
		private static $accountPin = null;
		private static $accountUsername = null;
		private static $password = null;
		private static $version = null;
		private static $result = null;
		private static $resultJson = null;
		private static $isLiveMode = false;

		// @Order info
		private static $shipper = null;
		private static $recipient = null;
		private static $thridparty = null;
		private static $shippingChargesPayment = null;
		private static $labelSpecification = null;
		private static $specialServices = null;
		private static $customerClearanceDetail = null;
		private static $packageLineItem = null;
		private static $request = null;
		private $paramCreateOrder = null;

		function __construct()
		{
			self::$accountCountryCode = "HK"; //live: HK       Dev: JO / HK
			self::$accountEntity = "HKG"; // live: HKG          Dev: AMM / HKG
			self::$accountNumber = "70546291";  // live: 125740    Dev: 20016 / 70546291
			self::$accountPin = "123123";	// live: 654154     Dev: 331421 / 123123
			self::$accountUsername = "testingapi@aramex.com"; // live: denis.kwok@bexpress.hk     Dev: testingapi@aramex.com
			self::$password = 'R123456789$r'; // live: Belogin777       Dev: R123456789$r
			self::$version = "v1";  // 1.0 /  v1
			self::$isLiveMode = false;
		}

		public static function setCredential($accountCountryCode="", $accountEntity="", $accountNumber="", $accountPin="",
							$accountUsername="", $password="", $version="", $isLiveMode=true)
		{
			// Get from DB later
			self::$accountCountryCode = $accountCountryCode;
			self::$accountEntity = $accountEntity;
			self::$accountNumber = $accountNumber;
			self::$accountPin = $accountPin;
			self::$accountUsername = $accountUsername;
			self::$password = $password;
			self::$version = $version;
			self::$isLiveMode = $isLiveMode;
		}

		protected static function __doAramexToBexpressLocalTrackingStatus($trackingStatus)
		{
			switch ($trackingStatus) // Aramex
			{
				case "SH296": case "SH375": case "SH306": case "SH307": case "SH308": case "SH309":
				case "SH310": case "SH311": case "SH312": case "SH313": case "SH314": case "SH315":
				case "SH316": case "SH317": case "SH318": case "SH319": case "SH320": case "SH321":
				case "SH322": case "SH323": case "SH324": case "SH325": case "SH326": case "SH327":
				case "SH328": case "SH329": case "SH330": case "SH331": case "SH332": case "SH333":
				case "SH334": case "SH335": case "SH336": case "SH337": case "SH338": case "SH339":
				case "SH346": case "SH347": case "SH348": case "SH349": case "SH350": case "SH351":
				case "SH352": case "SH353": case "SH409": case "SH013": case "SH014": case "SH015":
				case "SH016": case "SH203": case "SH047": case "SH048": case "SH049": case "SH406":
				case "SH011": case "SH012":
					return "1"; //Bexpress 1. 上門取件/ 到店寄件 Picked up /Dropped off
					break;

				case "SH373": case "SH229": case "SH378": case "SH490":
					return "2"; //Bexpress 2. 貨物到達處理中心 Shipment arrived at sorting center / Hub
					break;

				case "SH515": case "SH516": case "SH273": case "SH003": case "SH004": case "SH073":
				case "SH252": case "SH374":
					return "3"; //Bexpress 3. 貨物派送中 Delivery in progress
					break;

				case "SH354":
					return "4"; //Bexpress 4. 完成派送 Shipment is received
					break;

				case "SH505": case "SH157": case "SH005": case "SH162": case "SH163": case "SH038": case "SH278": case "SH380":
				case "SH074": case "SH026": case "SH027": case "SH028": case "SH029": case "SH231": case "SH008": case "SH009":
				case "SH031": case "SH155": case "SH156": case "SH376": case "SH381": case "SH410": case "SH467": case "SH468":
				case "SH469": case "SH470": case "SH471": case "SH472": case "SH473": case "SH498": case "SH199": case "SH162":
				case "SH377": case "SH017": case "SH018": case "SH492": case "SH493": case "SH504": case "SH508": case "SH256":
				case "SH404": case "SH439": case "SH033": case "SH043": case "SH294": case "SH480": case "SH020": case "SH021":
				case "SH237": case "SH476": case "SH477": case "SH478": case "SH208": case "SH209": case "SH210": case "SH211":
				case "SH235": case "SH287": case "SH288": case "SH289": case "SH290":

					return "5"; /*Bexpress 5. 異常事件(更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送) 
								Exception (Change the scheduled delivery date/ Loss/Damage/Failed Attempt)
								*/
					break;
			}
		}

		protected static function __doAramexToBexpressCrossTrackingStatus($trackingStatus)
		{
			switch ($trackingStatus) // Aramex
			{
				case "SH515": case "SH516": case "SH296": case "SH375": case "SH306": case "SH307": case "SH308": case "SH309":
				case "SH310": case "SH311": case "SH312": case "SH313": case "SH314": case "SH315": case "SH316": case "SH317":
				case "SH318": case "SH319": case "SH320": case "SH321": case "SH322": case "SH323": case "SH324": case "SH325":
				case "SH326": case "SH327": case "SH328": case "SH329": case "SH330": case "SH331": case "SH332": case "SH333":
				case "SH334": case "SH335": case "SH336": case "SH337": case "SH338": case "SH339": case "SH346": case "SH347":
				case "SH348": case "SH349": case "SH350": case "SH351": case "SH352": case "SH353": case "SH409": case "SH013":
				case "SH014": case "SH015": case "SH016": case "SH203": case "SH047": case "SH048": case "SH049": case "SH406":
				case "SH011": case "SH012":
					return "1"; //Bexpress 1. 上門取件/ 到店寄件 Picked up /Dropped off
					break;

				case "SH373": case "SH229": case "SH378": case "SH490":
					return "2"; //Bexpress 2. 貨物到達處理中心 Shipment arrived at sorting center / Hub
					break;

				case "SH374":
					return "3"; //Bexpress 3. 開始發貨 Departure Status
					break;

				case "SH456": case "SH457": case "SH458": case "SH459": case "SH460": case "SH461": case "SH463": case "SH041":
				case "SH462": case "SH464": case "SH032": case "SH068": case "SH069": case "SH071": case "SH345": case "SH474":
					return "4"; //Bexpress 4. 清關完成 Customs Clearance "
					break;

				case "SH505": case "SH003": case "SH004": case "SH073": case "SH252":
					return "5"; //Bexpress 5. 貨物派送中 Delivery in progress
					break;

				case "SH354":
					return "6"; //Bexpress 6. 完成派送 Shipment is received
					break;

				case "SH157": case "SH005": case "SH162": case "SH163": case "SH038": case "SH278": case "SH273": case "SH380":
				case "SH074": case "SH026": case "SH027": case "SH028": case "SH029": case "SH231": case "SH008": case "SH009":
				case "SH031": case "SH155": case "SH156": case "SH376": case "SH381": case "SH410": case "SH467": case "SH468":
				case "SH469": case "SH470": case "SH471": case "SH472": case "SH473": case "SH498": case "SH199": case "SH162":
				case "SH377": case "SH017": case "SH018": case "SH492": case "SH493": case "SH504": case "SH508": case "SH256":
				case "SH404": case "SH439": case "SH033": case "SH043": case "SH294": case "SH480": case "SH020": case "SH021":
				case "SH237": case "SH476": case "SH477": case "SH478": case "SH208": case "SH209": case "SH210": case "SH211":
				case "SH235": case "SH287": case "SH288": case "SH289": case "SH290":
					return "7"; /*Bexpress 7. 異常事件(清關延誤/ 更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送) 
								*/
					break;

				default:
					return "7"; /*Bexpress 7. 異常事件(清關延誤/ 更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送) 
								*/
				 	break;
			}
		}

		private static function __getSoapClient($functionName)
		{
			if(self::$isLiveMode) {
				$host = "ws.aramex.net";
			} else { // deve test mode
				$host = "ws.dev.aramex.net";
			}

			switch ($functionName)
			{
				case "createOrder":
					return "https://" . $host . "/ShippingAPI.V2/Shipping/Service_1_0.svc?singleWsdl";
					break;

				case "getTTInfo":
					// https://ws.dev.aramex.net/ShippingAPI.V2/Tracking/Service_1_0.svc?singleWsdl
					// https://ws.aramex.net/ShippingAPI.V2/Tracking/Service_1_0.svc
					return "https://" . $host . "/ShippingAPI.V2/Tracking/Service_1_0.svc?singleWsdl";
					break;
			}
		}

		private static function __setClient($client='')
		{
			return $client = new \SoapClient($client, array('trace' => 1));
		}
		
		private static function __convertTrackingStatus($callname, $trackingStatus, $max)
		{
			if($callname=="getLocalTrackingStatus") {
				self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult'][$max-1]['UpdateCode'] = self::__doAramexToBexpressLocalTrackingStatus($trackingStatus);
			} else if($callname=="getCrossTrackingStatus") {
				self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult'][$max-1]['UpdateCode'] = self::__doAramexToBexpressCrossTrackingStatus($trackingStatus);
			}
		}

		public static function getLocalTrackingStatus($orderno)
		{
			self::__getTTInfo($orderno);
			$result = self::getOriginalResult();
			$max = sizeof(self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult']);
			self::__convertTrackingStatus("getLocalTrackingStatus", $result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult'][$max-1]['UpdateCode'], $max);
			
			$data = array ();
			$dataArr = array ();
			$tempArr = array ();
			$lastState = self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult'][$max-1]['UpdateCode'];
			
			if(self::$result['HasErrors']== '')
			{
				for($i=0; $i<$max; $i++)
				{
					$tempArr = array (
										'time' => self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult'][$i]['UpdateDateTime'],
										'description' => self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult'][$i]['UpdateDescription'],
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
			$result = self::getOriginalResult();
			$max = sizeof(self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult']);
			self::__convertTrackingStatus("getCrossTrackingStatus", $result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult'][$max-1]['UpdateCode'], $max);
			
			$data = array ();
			$dataArr = array ();
			$tempArr = array ();
			$lastState = self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult'][$max-1]['UpdateCode'];
			
			if(self::$result['HasErrors']== '')
			{
				for($i=0; $i<$max; $i++)
				{
					$tempArr = array (
										'time' => self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult'][$i]['UpdateDateTime'],
										'description' => self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult'][$i]['UpdateDescription'],
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

		public static function getOrderResult()
		{
			$array = json_decode(self::$resultJson,TRUE);
			self::$result = $array;

			// generic化 return data
			if(self::$result['HasErrors']=='') {
				self::$result = Array('status'=>200,
										'waybillno'=> self::$result['Shipments']['ProcessedShipment']['ID']
									);
			} else {
				self::$result = Array('status' => 500,
									'code' => self::$result['Shipments']['ProcessedShipment']['Notifications']['Notification']['Code'],
									'description' => self::$result['Shipments']['ProcessedShipment']['Notifications']['Notification']['Message']
				);
			}

			return self::$result;
		}

		public static function getOriginalResult() // createOrder/Tracking result return
		{
			if(self::$result==null)
			{
				self::$result = json_decode(self::$resultJson,TRUE);
			}
			return self::$result;
		}

		public static function getJsonResult()
		{
			return self::$resultJson;
		}

		// ********************* Lists of Soap Functions *********************
		public static function setShipper($senderName, $senderCompanyName, $phoneNumber, $phoneNumberExt, $cellNumber,
										$StreetLine1, $City, $StateOrProvinceCode, 
										$PostalCode, $CountryCode, $email)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Aramex\AramexParam\ParamCreateOrder();
			$paramCreateOrder::addShipper(self::$accountNumber, $senderName, $senderCompanyName, $phoneNumber, $phoneNumberExt, $cellNumber, $StreetLine1, $City, $StateOrProvinceCode, $PostalCode, $CountryCode, $email);
			self::$shipper = $paramCreateOrder::getShipper();
		}

		public static function setRecipient($recipientName, $companyName, $phoneNumber, $phoneNumberExt, $cellNumber, $StreetLine1,
											$City, $StateOrProvinceCode, $PostalCode, $CountryCode, $email="")
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Aramex\AramexParam\ParamCreateOrder();
			$paramCreateOrder::addRecipient($recipientName, $companyName, $phoneNumber, $phoneNumberExt, $cellNumber, $StreetLine1,
											$City, $StateOrProvinceCode, $PostalCode, $CountryCode, $email);
			self::$recipient = $paramCreateOrder::getRecipient();
		}

		public static function setThridParty($thridPartyName="", $thridPartyCompanyName="", $phoneNumber="", $phoneNumberExt="", $cellNumber="", $StreetLine1="",
											$City="", $StateOrProvinceCode="", $PostalCode="", $CountryCode="", $email="")
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Aramex\AramexParam\ParamCreateOrder();
			$paramCreateOrder::addThridParty($thridPartyName, $thridPartyCompanyName, $phoneNumber, $phoneNumberExt, $cellNumber, $StreetLine1,
											$City, $StateOrProvinceCode, $PostalCode, $CountryCode, $email);
			self::$thridparty = $paramCreateOrder::getThridParty();
		}

		public static function setCustomerClearanceDetail($orderid, $dimensionsLength="", $dimensionsWidth="", $dimensionsHeight="", $dimensionsUnit="",
														$actualWeightValue="", $actualWeightUnit="", $productGroup="", $productType="", $paymentType="", 
														$numberOfPiece="", $descriptionOfGoods="", $goodsOriginCountry="", $cashOnDeliveryAmountValue="", $cashOnDeliveryAmountCurrencyCode="", 
														$insuranceAmountValue="", $insuranceAmountCurrencyCode="", $collectAmountValue="", $collectAmountCurrencyCode="", 
														$CashAdditionalAmountValue="", $CashAdditionalAmountCurrencyCode="",
														$CustomsValueAmountValue="", $CustomsValueAmountCurrencyCode="", 
														$ItemsPackageType="", $ItemsQuantity="", $ItemsWeightValue="", $ItemsWeightUnit="", $ItemsComments="", $ItemsReference="")
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Aramex\AramexParam\ParamCreateOrder();
			$paramCreateOrder::addCustomClearanceDetail($orderid, $dimensionsLength, $dimensionsWidth, $dimensionsHeight, $dimensionsUnit,
														$actualWeightValue, $actualWeightUnit, $productGroup, $productType, $paymentType, 
														$numberOfPiece, $descriptionOfGoods, $goodsOriginCountry, $cashOnDeliveryAmountValue, $cashOnDeliveryAmountCurrencyCode, 
														$insuranceAmountValue, $insuranceAmountCurrencyCode, $collectAmountValue, $collectAmountCurrencyCode, 
														$CashAdditionalAmountValue, $CashAdditionalAmountCurrencyCode,
														$CustomsValueAmountValue, $CustomsValueAmountCurrencyCode,
														$ItemsPackageType, $ItemsQuantity, $ItemsWeightValue, $ItemsWeightUnit, $ItemsComments, $ItemsReference);
			self::$customerClearanceDetail = $paramCreateOrder::getCustomerClearanceDetail(); 
		}

		private static function __getRequest($requestType, $orderno='')
		{
			$request = new \yuenkokeith\spapi\Aramex\AramexParam\Request($requestType, $orderno, self::$accountCountryCode, 
															self::$accountEntity, self::$accountNumber, self::$accountPin, 
															self::$accountUsername, self::$password, self::$version,
															self::$shipper , self::$recipient, self::$customerClearanceDetail, self::$thridparty);
			return $request::getRequest();
		}

		private static function __getTTInfo($orderno)
		{
			$client = self::__getSoapClient("getTTInfo");
			$client = self::__setClient($client);
			$request = self::__getRequest("getTTInfo", $orderno);
		
			try {
				$auth_call = $client->TrackShipments($request);
				$json = json_encode($auth_call);
				self::$resultJson = $json;
				self::$result = json_decode($json,TRUE);
				
				//echo self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult']['WaybillNumber'];
				//echo "<br/>";
				//echo self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult']['UpdateCode'];
				//echo "<br/><br/><br/><br/><br/>";
				//print_r(self::$result); exit;

			} catch (SoapFault $fault) {
				self::$result = array ('status' => 500);
			}
		}

		private static function __curl_get_file_contents($URL)
		{
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_URL, $URL);
			$contents = curl_exec($c);
			curl_close($c);

			if ($contents) return $contents;
				else return FALSE;
		}

		public static function createOrder()
		{
			$client = self::__getSoapClient("createOrder");
			$client = self::__setClient($client);

			if(self::$shipper!=null && self::$recipient!=null && self::$customerClearanceDetail!=null)
			{
				$request = self::__getRequest("createOrder");
				try {
					$auth_call = $client->CreateShipments($request);
				
					$json = json_encode($auth_call);
					self::$resultJson = $json;
					self::$result = json_decode($json,TRUE);
					
					// Create PDF label for download
					$downloadUrl = self::$result['Shipments']['ProcessedShipment']['ShipmentLabel']['LabelURL'];
					$waybillno = self::$result['Shipments']['ProcessedShipment']['ID'];
					$contents = self::__curl_get_file_contents($downloadUrl);
					$labelfilename = "aramex_" .  $waybillno . "_label.pdf";
					$fp = fopen(APP . "../../uploadFiles/waybilllabel/aramex/" . $labelfilename , 'wb');
					fwrite($fp, ($contents));
					fclose($fp);

				} catch (SoapFault $fault) {
					self::$result = array ('status' => 500);
				}
			}
			else {
				self::$result = array ('status' => 500);
			}
		}

		public static function spCreateOrder($data)
		{
			/****use the same 'Key' name in array ****/
			// mainland form
			/*
			$data = array ('ServiceProviderSelection'=> $value1, 'Product'=> $value2, 'SenderName'=> $value3, 'SenderPhoneNo'=> $value4, 
							'SenderAddress'=> $value5, 'ReceiverName'=> $value6, 'ReceiverCompany'=> $value7, 'ReceiverPhoneNo'=> $value8, 
							'ReceiverAddress'=> $value9, 'ReceiverCity'=> $value10, 'ReceiverProvince'=> $value11, 'ReceiverPostalCode'=> $value12, 
							'FirstShipmentItem'=> $value13, 'SecondShipmentItem'=> $value14, 'ThirdShipmentItem'=> $value15, 'ItemDescription'=> $value16, 
							'NoOfPackage'=> $value17, 'ChargeableWeight(kg)'=> $value18,'TotalDeclaredValue(RMB)'=> $value19, 'ShipmentProtection'=> $value20, 
							'CustomerReferences'=> $value21);
			*/

			//e.g $senderName, $senderCompanyName, $phoneNumber, $phoneNumberExt, $cellNumber, $StreetLine1, $City, $StateOrProvinceCode, $PostalCode, $CountryCode, $email
			self::setShipper($data['SenderName'], $data['SenderCompany'], $data['SenderPhoneNo'], $data['SenderPhoneNoExt'], $data['SenderPhoneNo'], $data['SenderAddress'], 
									$data['SenderCity'], $data['SenderCountry'], $data['SenderPostalCode'], $data['SenderCountry'], $data['senderEmail']);

			//e.g $recipientName, $companyName, $phoneNumber, $phoneNumberExt, $cellNumber, $StreetLine1, $City, $StateOrProvinceCode, $PostalCode, $CountryCode, $email=""
			self::setRecipient($data['ReceiverName'], $data['ReceiverCompany'], $data['ReceiverPhoneNo'], $data['ReceiverPhoneNoExt'], $data['ReceiverPhoneNo'], $data['ReceiverAddress'], 
								$data['ReceiverCity'], '', $data['ReceiverPostalCode'], 'AE', $data['receiverEmail']);

			self::setThridParty();
			
			/* 		
				PDX: Priority Document Express 
				PPX: Priority Parcel Express
				PLX: Priority Letter Express
			*/
			$productType = 'PPX';
			self::setCustomerClearanceDetail($data['bexOrderId'], 10, 10, 10, 'cm',
													$data['ChargeableWeight(kg)'], 'Kg', 'EXP', $productType, 'P',
													$data['NoOfPackage'], $data['ItemDescription'], '', 0,
													'', 0, '',
													0, '', 0,
													'', 1, 'USD',
													'Box', $data['NoOfPackage'], $data['ChargeableWeight(kg)'],
													'Kg', '', '');

			self::createOrder();
			
		}
		
		public static function getTTInfo($orderno)
		{
			$client = self::__getSoapClient("getTTInfo");
			$client = self::__setClient($client);
			$request = self::__getRequest("getTTInfo", $orderno);

			try {
				$auth_call = $client->TrackShipments($request);
				$json = json_encode($auth_call);
				self::$resultJson = $json;
				self::$result = json_decode($json,TRUE);
				
				//echo self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult']['WaybillNumber'];
				//echo "<br/>";
				//echo self::$result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult']['UpdateCode'];
				//echo "<br/><br/><br/><br/><br/>";
				//print_r(self::$result); exit;

			} catch (SoapFault $fault) {
				self::$result = array ('status' => 500);
			}
		}

		public static function setLiveMode($onoff)
		{
			self::$isLiveMode= $onoff;
		}

	}

	interface AramexInterface
	{
		public static function setCredential($accountCountryCode="", $accountEntity="", $accountNumber="", $accountPin="",
							$accountUsername="", $password="", $version="", $isLiveMode=true);
		public static function getLocalTrackingStatus($orderno);
		public static function getCrossTrackingStatus($orderno);
		public static function getOrderResult();
		public static function getOriginalResult(); // createOrder/Tracking result return
		public static function getJsonResult();
		public static function setShipper($senderName, $senderCompanyName, $phoneNumber, $phoneNumberExt, $cellNumber,
												$StreetLine1, $City, $StateOrProvinceCode, 
												$PostalCode, $CountryCode, $email);
		public static function setRecipient($recipientName, $companyName, $phoneNumber, $phoneNumberExt, $cellNumber, $StreetLine1,
											$City, $StateOrProvinceCode, $PostalCode, $CountryCode, $email="");
		public static function setThridParty($thridPartyName="", $thridPartyCompanyName="", $phoneNumber="", $phoneNumberExt="", $cellNumber="", $StreetLine1="",
											$City="", $StateOrProvinceCode="", $PostalCode="", $CountryCode="", $email="");
		public static function setCustomerClearanceDetail($orderid, $dimensionsLength="", $dimensionsWidth="", $dimensionsHeight="", $dimensionsUnit="",
																$actualWeightValue="", $actualWeightUnit="", $productGroup="", $productType="", $paymentType="", 
																$numberOfPiece="", $descriptionOfGoods="", $goodsOriginCountry="", $cashOnDeliveryAmountValue="", $cashOnDeliveryAmountCurrencyCode="", 
																$insuranceAmountValue="", $insuranceAmountCurrencyCode="", $collectAmountValue="", $collectAmountCurrencyCode="", 
																$CashAdditionalAmountValue="", $CashAdditionalAmountCurrencyCode="",
																$CustomsValueAmountValue="", $CustomsValueAmountCurrencyCode="", 
																$ItemsPackageType="", $ItemsQuantity="", $ItemsWeightValue="", $ItemsWeightUnit="", $ItemsComments="", $ItemsReference="");
		public static function createOrder();
		public static function spCreateOrder($data);
		public static function getTTInfo($orderno);

	}
	
?>

