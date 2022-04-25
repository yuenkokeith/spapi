<?php
	
	namespace yuenkokeith\spapi\Fedex;
	require_once('FedexParam.php');
	require_once('FedexCode.php');
	use yuenkokeith\spapi\Fedex\FedexParam;
	use yuenkokeith\spapi\Fedex\FedexCode;
	//ini_set("soap.wsdl_cache_enabled", "1");
	
	class Fedex extends \yuenkokeith\spapi\SpAPI implements FedexInterface
	{
		// @Account Property
		private static $key = null;
		private static $password = null;
		private static $shipaccount = null;
		private static $billaccount = null;
		private static $dutyaccount = null;
		private static $freightaccount = null;
		private static $trackaccount = null;
		private static $dutiesaccount = null;
		private static $importeraccount = null;
		private static $brokeraccount = null;
		private static $distributionaccount = null;
		private static $locationid = null;
		private static $printlabels = null;
		private static $printdocuments = null;
		private static $packagecount = null;
		private static $validateaccount = null;
		private static $meter = null;
		private static $endpoint = null;
		private static $msg = "testapi/Fedex";
		private static $soapClient = null;
		private static $result = null;
		private static $resultJson = null;
		private static $localTrackingStatus = null;
		private static $crossTrackingStatus = null;

		// @Order info
		private static $shipper = null;
		private static $recipient = null;
		private static $shippingChargesPayment = null;
		private static $labelSpecification = null;
		private static $specialServices = null;
		private static $customerClearanceDetail = null;
		private static $packageLineItem = null;
		private static $request = null;
		private $paramCreateOrder = null;

		function __construct()
		{
			self::$key = 'X8LhUVmBco4ynsBJ';
			self::$password = 'H7Zt3hjc5wGQzkHgIOx9zTVkR';
			self::$shipaccount = '510087780';
			self::$billaccount = '510087780';
			self::$dutyaccount = '510087780';
			self::$freightaccount = '510087780';
			self::$trackaccount = '510087780';
			self::$dutiesaccount = '510087780';
			self::$importeraccount = '510087780';
			self::$brokeraccount = '510087780';
			self::$distributionaccount = '510087780';
			self::$locationid = 'PLBA';
			self::$printlabels = null;
			self::$printdocuments = null;
			self::$packagecount = '4';
			self::$validateaccount = '510087780';
			self::$meter = '118822538';
		}

		public static function setCredential($key="", $password="", $shipaccount="", $meter="", $isLiveMode=true)
		{
			// Get from DB later
			self::$key = $key;
			self::$password = $password;
			self::$shipaccount = $shipaccount;
			self::$billaccount = $shipaccount;
			self::$dutyaccount = $shipaccount;
			self::$freightaccount = $shipaccount;
			self::$trackaccount = $shipaccount;
			self::$dutiesaccount = $shipaccount;
			self::$importeraccount = $shipaccount;
			self::$brokeraccount = $shipaccount;
			self::$distributionaccount = $shipaccount;
			self::$locationid = 'PLBA';
			self::$printlabels = null;
			self::$printdocuments = null;
			self::$packagecount = '4';
			self::$validateaccount = $shipaccount;
			self::$meter = $meter;
		}

		protected static function __doFedexToBexpressLocalTrackingStatus($fedexCode, $trackingStatus)
		{
			if($fedexCode=="FDXE")
			{
				switch ($trackingStatus) // Fedex
				{
					case "PU":
						return "1"; //Bexpress 1. 上門取件/ 到店寄件 Picked up /Dropped off
						break;

					case "AF":
						return "2"; //Bexpress 2. 貨物到達處理中心 Shipment arrived at sorting center / Hub
						break;

					case "CP":
					case "IT":
					case "TR":
						return "3"; //Bexpress 3. 貨物派送中 Delivery in progress
						break;

					case "DL":
						return "4"; //Bexpress 4. 完成派送 Shipment is received
						break;

					case "CA":
					case "DE":
					case "SE":
					case "CD":
						return "5"; /*Bexpress 5. 異常事件(更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送) 
									Exception (Change the scheduled delivery date/ Loss/Damage/Failed Attempt)
									*/
						break;
				}
			}
		}

		protected static function __doFedexToBexpressCrossTrackingStatus($fedexCode, $trackingStatus)
		{
			if($fedexCode=="FDXE")
			{
				switch ($trackingStatus)
				{
					case "PU":
						return "1"; //Bexpress 1. 上門取件/ 到店寄件 Picked up /Dropped off
						break;

					case "AF":
						return "2"; //Bexpress 2. 貨物到達處理中心 Shipment arrived at sorting center / Hub
						break;

					case "CP":
						return "3"; //Bexpress 3. 開始發貨 Departure Status
						break;

					case "IT":
					case "TR":
						return "5"; //Bexpress 5. 貨物派送中 Delivery in progress
						break;

					case "DL":
						return "6"; //Bexpress 6. 完成派送 Shipment is received
						break;

					case "CA":
					case "DE":
					case "SE":
					case "CD":
						return "7"; /*Bexpress 7. 異常事件(清關延誤/ 更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送) 
									*/
						break;
				}
			}
		}

		private static function __getSoapClient($functionName)
		{
			switch ($functionName)
			{
				case "createOrder":
					return "FedExWebServices/wsdl/ShipService_v19.wsdl";
					break;

				case "getTTInfo":
					return "FedExWebServices/wsdl/TrackService_v12.wsdl";
					break;	
			}
		}

		private static function __setClient($client='')
		{
			return $client = new \SoapClient(APP . $client, array('trace' => 1));
		}
		
		private static function __convertTrackingStatus($callname, $trackingStatus)
		{
			if($callname=="getLocalTrackingStatus") {
				self::$result['CompletedTrackDetails']['TrackDetails']['StatusDetail']['Code'] = self::__doFedexToBexpressLocalTrackingStatus('FDXE', $trackingStatus);
			} else if($callname=="getCrossTrackingStatus") {
				self::$result['CompletedTrackDetails']['TrackDetails']['StatusDetail']['Code'] = self::__doFedexToBexpressCrossTrackingStatus('FDXE', $trackingStatus);
			}
		}

		public static function getLocalTrackingStatus($orderno)
		{
			self::__getTTInfo($orderno);
			$result = self::getOriginalResult();
			$dataArr = array ();
			$tempArr = array ();
			if($result['HighestSeverity']=='SUCCESS') {
				self::__convertTrackingStatus("getLocalTrackingStatus", $result['CompletedTrackDetails']['TrackDetails']['StatusDetail']['Code']);

				$tempArr = array (
									'time' =>  self::$result['CompletedTrackDetails']['TrackDetails']['StatusDetail']['CreationTime'],
									'description' => self::$result['CompletedTrackDetails']['TrackDetails']['StatusDetail']['Description'],
								);
				array_push($dataArr,$tempArr);

				self::$result = array (
								'status' => 200,
								'state'=> self::$result['CompletedTrackDetails']['TrackDetails']['StatusDetail']['Code'],
								'data'=> $dataArr
							);
			} else {
				self::$result = Array('status' => 500);
			}

			return self::$result;
		}
		
		public static function getCrossTrackingStatus($orderno)
		{
			self::__getTTInfo($orderno);
			$result = self::getOriginalResult();
			$dataArr = array ();
			$tempArr = array ();
			if($result['HighestSeverity']=='SUCCESS') {
				self::__convertTrackingStatus("getCrossTrackingStatus", $result['CompletedTrackDetails']['TrackDetails']['StatusDetail']['Code']);
				
				$tempArr = array (
									'time' =>  self::$result['CompletedTrackDetails']['TrackDetails']['StatusDetail']['CreationTime'],
									'description' => self::$result['CompletedTrackDetails']['TrackDetails']['StatusDetail']['Description'],
								);
				array_push($dataArr,$tempArr);

				self::$result = array (
								'status' => 200,
								'state'=> self::$result['CompletedTrackDetails']['TrackDetails']['StatusDetail']['Code'],
								'data'=> $dataArr
							);
			
			} else {
				self::$result = Array('status' => 500);
			}

			return self::$result;
		}

		public static function getOrderResult()
		{
			self::$result = json_decode(self::$resultJson,TRUE);
			// generic化 return data
			if(self::$result['HighestSeverity']=='SUCCESS') {
				self::$result = Array('status'=>200, 'waybillno'=> self::$result['CompletedShipmentDetail']['CompletedPackageDetails']['TrackingIds']['TrackingNumber']);
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
			if(self::$result==null)
			{
				self::$result = json_decode(self::$resultJson,TRUE);
			}
			
			//self::$result['CompletedShipmentDetail']['CompletedPackageDetails']['Label']['Parts'] = "";
			return self::$result;
		}

		public static function getJsonResult()
		{
			return self::$resultJson;
		}

		// ********************* Lists of Soap Functions *********************
		public static function setShipper($senderName, $senderCompanyName, $phoneNumber, $phoneExtension, $StreetLine1, $City, $StateOrProvinceCode, $PostalCode, $CountryCode)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Fedex\FedexParam\ParamCreateOrder();
			$paramCreateOrder::addShipper($senderName, $senderCompanyName, $phoneNumber, $phoneExtension, $StreetLine1, $City, $StateOrProvinceCode, $PostalCode, $CountryCode);
			self::$shipper = $paramCreateOrder::getShipper();
		}

		public static function setRecipient($recipientName, $companyName, $phoneNumber, $phoneExtension,$StreetLine1,
											$City, $StateOrProvinceCode, $PostalCode, $CountryCode,
											$residential=false)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Fedex\FedexParam\ParamCreateOrder();
			$paramCreateOrder::addRecipient($recipientName, $companyName, $phoneNumber, $phoneExtension, $StreetLine1,
											$City, $StateOrProvinceCode, $PostalCode, $CountryCode,
											$residential);
			self::$recipient = $paramCreateOrder::getRecipient();
		}

		public static function setShippingChargesPayment($paymentType='SENDER', $contact=null, $countryCode)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Fedex\FedexParam\ParamCreateOrder();
			$paramCreateOrder::addShippingChargesPayment($paymentType, self::$billaccount, $contact, $countryCode);
			self::$shippingChargesPayment = $paramCreateOrder::getShippingChargesPayment();
		}
		
		public static function setLabelSpecification()
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Fedex\FedexParam\ParamCreateOrder();
			$paramCreateOrder::addLabelSpecification();
			self::$labelSpecification = $paramCreateOrder::getLabelSpecification();
		}

		public static function setSpecialServices($specialServiceTypes='COD', $currency, $amount, $collectionType='ANY')
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Fedex\FedexParam\ParamCreateOrder();
			$paramCreateOrder::addSpecialServices($specialServiceTypes, $currency, $amount, $collectionType);
			self::$specialServices = $paramCreateOrder::getSpecialServices();
		}

		public static function setCustomerClearanceDetail($sender, $contact, $countryCode, $documentContent, $customsValueCurrency, 
														$customsValueAmount, $numberOfPieces, $description, $countryOfManufacture, $units, $value,
														$quantity, $quantityUnits, $unitPriceCurrency, $unitPriceAmount, $commoditiesCustomsValueCurrency, 
														$commoditiesCustomsValueAmount, $B13AFilingOption)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Fedex\FedexParam\ParamCreateOrder();
			$paramCreateOrder::addCustomClearanceDetail($sender, self::$dutiesaccount, $contact, $countryCode, $documentContent, $customsValueCurrency, 
														$customsValueAmount, $numberOfPieces, $description, $countryOfManufacture, $units, $value,
														$quantity, $quantityUnits, $unitPriceCurrency, $unitPriceAmount, $commoditiesCustomsValueCurrency, 
														$commoditiesCustomsValueAmount, $B13AFilingOption);
			self::$customerClearanceDetail = $paramCreateOrder::getCustomerClearanceDetail(); 
		}

		public static function setPackageLineItem($sequenceNumber, $groupPackageCountm, $weightValue, $weightUnits, 
									$dimensionsLength, $dimensionsWidth, $dimensionsHeight, $dimensionsUnits)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Fedex\FedexParam\ParamCreateOrder();
			$paramCreateOrder::addPackageLineItem1($sequenceNumber, $groupPackageCountm, $weightValue, $weightUnits, 
									$dimensionsLength, $dimensionsWidth, $dimensionsHeight, $dimensionsUnits);
			self::$packageLineItem = $paramCreateOrder::getPackageLineItem(); 
		}

		private static function __getRequest($requestType, $orderno='', $dropoffType='REGULAR_PICKUP', $serviceType='INTERNATIONAL_PRIORITY', $packagingType='YOUR_PACKAGING')
		{
			$request = new \yuenkokeith\spapi\Fedex\FedexParam\Request($requestType, $orderno, self::$key, self::$password, self::$shipaccount, self::$meter,
																$dropoffType, $serviceType, $packagingType, self::$shipper, self::$recipient, 
																self::$shippingChargesPayment, self::$customerClearanceDetail, 
																self::$labelSpecification, self::$packageLineItem);
			return $request::getRequest();
		}

		public static function createOrder()
		{
			$client = self::__getSoapClient("createOrder");
			$client = self::__setClient($client);

			if(self::$shipper!=null && self::$recipient!=null && self::$shippingChargesPayment!=null && self::$labelSpecification!=null 
				&& self::$specialServices!=null && self::$customerClearanceDetail!=null && self::$packageLineItem!=null)
			{
				$request = self::__getRequest("createOrder");
				$response = $client->processShipment($request); // FedEx web service invocation
			
				if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') 
				{
					self::$resultJson = json_encode($response);
					self::$result = self::getOriginalResult();
					
					// Create PNG or PDF label
					// Set LabelSpecification.ImageType to 'PDF' for generating a PDF label
					$labelfilename = "fedex_" . self::$result['CompletedShipmentDetail']['CompletedPackageDetails']['TrackingIds']['TrackingNumber'] . "_label.pdf";
					//$fp = fopen(APP . "Vendor/spapi/FedExWebServices/pdf/" . $labelfilename, 'wb');   
					$fp = fopen(APP . "../../uploadFiles/waybilllabel/fedex/" . $labelfilename, 'wb');   
					fwrite($fp, ($response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image));
					fclose($fp);
				}
				else
				{
					//print_r($response); exit;
					self::$result = array ('status' => 500);
				}
			}
			else {
				self::$result = array ('status' => 500);
			}
		}

		public static function spCreateOrder($data)
		{
			$convertCode = new \yuenkokeith\spapi\Fedex\FedexCode\ConvertCode();
			$receiverProvince = $convertCode::convertProvince($data['ReceiverProvince'], $data['ReceiverCountry']);
			
			// $senderName, $senderCompanyName, $phoneNumber, $phoneExtension, $StreetLine1, $City, $StateOrProvinceCode, $PostalCode, $CountryCode
			self::setShipper($data['SenderName'], $data['SenderCompany'], $data['SenderPhoneNo'], $data['SenderPhoneNoExt'] , 
								$data['SenderAddress'], $data['SenderCity'], $data['SenderProvince'], '0000', 'HKG');
			self::setRecipient($data['ReceiverName'], $data['ReceiverCompany'], $data['ReceiverPhoneNo'], $data['ReceiverPhoneNoExt'], 
								$data['ReceiverAddress'], $data['ReceiverCity'], $receiverProvince, $data['ReceiverPostalCode'], $data['ReceiverCountry'], false);
			self::setShippingChargesPayment('SENDER', null, 'USD');
			self::setLabelSpecification();
			self::setSpecialServices('', 'USD', 0);
			
			/*   *********** Param header *************
				sender, accountNumber, contact, countryCode, documentContent, customsValueCurrency,
				customsValueAmount, numberOfPieces, description, countryOfManufacture, units, value,
				quantity, quantityUnits, unitPriceCurrency, unitPriceAmount, commoditiesCustomsValueCurrency,
				commoditiesCustomsValueAmount, B13AFilingOption

				setCustomerClearanceDetail()
				$sender, $contact, $countryCode, $documentContent, $customsValueCurrency,
				$customsValueAmount, $numberOfPieces, $description, $countryOfManufacture, $units, $value,
				$quantity, $quantityUnits, $unitPriceCurrency, $unitPriceAmount, $commoditiesCustomsValueCurrency,
				$commoditiesCustomsValueAmount, $B13AFilingOption
			*   *********** Param header ************ */
			
			self::setCustomerClearanceDetail('SENDER', null, $data['SenderCountry'], 'NON_DOCUMENTS', 'USD', 
															$data['TotalDeclaredValue(USD)'], $data['NoOfPackage'], 
															$data['ItemDescription'], 'CN', 'KG', $data['TotalDeclaredValue(USD)'],
															$data['NoOfPackage'], 'EA', 'USD', 100.000000, 'USD', 
															$data['TotalDeclaredValue(USD)'], 'NOT_REQUIRED');
			
			/*  *********** Param header *************
				sequenceNumber, groupPackageCountm, weightValue, weightUnits,
				dimensionsLength, dimensionsWidth, dimensionsHeight, dimensionsUnits
			*   *********** Param header *************/
			//self::setPackageLineItem(1, 1, 20.0, 'LB', 20, 20, 10, 'CM');
			self::setPackageLineItem(1, 1, $data['ChargeableWeight(kg)'], 'KG', 20, 20, 10, 'CM');
			self::createOrder();

		}

		private static function __getTTInfo($orderno)
		{
			$client = self::__getSoapClient("getTTInfo");
			$client = self::__setClient($client);
			$request = self::__getRequest("getTTInfo", $orderno);
			$response = $client->track($request);
			if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR'){
				$json = json_encode($response);
				self::$resultJson = $json;
				self::$result = json_decode($json,TRUE);
			} else {
				self::$result = array ('status' => 500);
			} 
		}

	}

	interface FedexInterface
	{
		public static function setCredential($key="", $password="", $shipaccount="", $meter="", $isLiveMode=true);
		public static function getLocalTrackingStatus($orderno);
		public static function getCrossTrackingStatus($orderno);
		public static function getOrderResult();
		public static function getOriginalResult();
		public static function getJsonResult();
		public static function setShipper($senderName, $senderCompanyName, $phoneNumber, $phoneExtension, $StreetLine1, $City, $StateOrProvinceCode, $PostalCode, $CountryCode);
		public static function setRecipient($recipientName, $companyName, $phoneNumber, $phoneExtension,$StreetLine1,
													$City, $StateOrProvinceCode, $PostalCode, $CountryCode,
													$residential=false);
		public static function setShippingChargesPayment($paymentType='SENDER', $contact=null, $countryCode);
		public static function setLabelSpecification();
		public static function setSpecialServices($specialServiceTypes='COD', $currency, $amount, $collectionType='ANY');

		public static function setCustomerClearanceDetail($sender, $contact, $countryCode, $documentContent, $customsValueCurrency, 
																$customsValueAmount, $numberOfPieces, $description, $countryOfManufacture, $units, $value,
																$quantity, $quantityUnits, $unitPriceCurrency, $unitPriceAmount, $commoditiesCustomsValueCurrency, 
																$commoditiesCustomsValueAmount, $B13AFilingOption);
		public static function setPackageLineItem($sequenceNumber, $groupPackageCountm, $weightValue, $weightUnits, 
											$dimensionsLength, $dimensionsWidth, $dimensionsHeight, $dimensionsUnits);
		public static function createOrder();
		public static function spCreateOrder($data);
	}	
	
?>

