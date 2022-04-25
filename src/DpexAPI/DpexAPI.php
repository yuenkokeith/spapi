<?php

	namespace yuenkokeith\spapi\Dpex;
	require_once('DpexParam.php');
	use yuenkokeith\spapi\Dpex\DpexParam;
	ini_set("soap.wsdl_cache_enabled", "1");

	class Dpex extends \yuenkokeith\spapi\SpAPI implements DpexInterface
	{
		// @Account Property
		private static $accountUsername = null;
		private static $password = null;
		private static $entityId = null;
		private static $entityPin = null;
		private static $result = null;
		private static $resultJson = null;

		// soap client
		private static $soapClient = null;

		// @Order info
		private static $shipper = null;
		private static $recipient = null;
		private static $customerClearanceDetail = null;

		function __construct()
		{
			self::$accountUsername = 'F12339C300228E2CC47B1AB0E0D5396F';
			self::$password = '2877894D49655845D8C95756C88FB58F';
			self::$entityId = 'E384AC0CBE448B14B273D3D96B597CDD';
			self::$entityPin = 'EAJDF2KDAJF2NLADKF22';
		}

		public static function setCredential($accountUsername="", $password="", $entityId="", $entityPin="")
		{
			// load from db later
			self::$accountUsername = $accountUsername;
			self::$password = $password;
			self::$entityId = $entityId;
			self::$entityPin = $entityPin;
		}

		private static function __getSoapClient($functionName)
		{
			switch ($functionName)
			{
				case "createOrder":
					return "https://ws05.ffdx.net/ffdx_ws/v12/service_customer.asmx/UploadCMawbWithLabelToServer";
					break;

				case "getTTInfo":
					return "https://ws05.ffdx.net/ffdx_ws/v12/service_ffdx.asmx/WSDataTransfer";
					break;	
			}
		}

		private static function __getRequest($requestType, $trackNo='')
		{
			$request = new \yuenkokeith\spapi\Dpex\DpexParam\Request($requestType, self::$accountUsername, self::$password,  
														self::$entityId, self::$entityPin,
														self::$shipper, self::$recipient, 
														self::$customerClearanceDetail, $trackNo
														);
			return $request::getRequest();
		}

		private static function __processRequest($requestArr, $soapClient, $callname)
		{
			//url-ify the data for the POST
			$fields_string = '';
			foreach($requestArr as $key=>$value) 
			{ 
				$fields_string .= $key.'='.$value.'&'; 
			}

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch,CURLOPT_URL,$soapClient);
			curl_setopt($ch,CURLOPT_POST,count($requestArr));
			curl_setopt($ch,CURLOPT_POSTFIELDS, rtrim($fields_string,'&') );
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			
			// Send to remote and return data to caller.
			self::$result = curl_exec($ch);
			curl_close($ch);

			$xmlArray = simplexml_load_string(htmlspecialchars_decode(self::$result), 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
			$json = json_encode($xmlArray);
			self::$resultJson = $json;

			if($callname=="createOrder")
			{
				self::__createAndDownloadPDF();
			}
			
		}

		private static function __createAndDownloadPDF()
		{
			// Create PDF label for download
			self::$result = self::getOriginalResult();
			$downloadUrl = "https://ws01.ffdx.net/v4/printdoc/docConnoteStyle2.aspx?accessid=" . 
									self::$result['WSGET']['AccessRequest']['EntityID'] . 
									"&qr=1&shipno=" . self::$result['WSGET']['Status']['CC']['CCConnote'] . "&format=pdf";

			// the endpoint script(not pre-generated) will generate pdf on the fly
			$contents = file_get_contents($downloadUrl);
			$waybillno = self::$result['WSGET']['Status']['CC']['CCConnote'];
			// for test random number
			//$waybillno = $waybillno . mt_rand(100001,999999);
			$labelfilename = "dpex_" .  $waybillno . "_label.pdf";
			$fp = fopen(APP . "../../uploadFiles/waybilllabel/dpex/" . $labelfilename , 'wb');
			fwrite($fp, ($contents));
			fclose($fp);
		}

		protected static function __doDpexToBexpressLocalTrackingStatus($trackingStatus)
		{
			switch ($trackingStatus) // Dpex
			{
				case "2": case "260": 
					return "1"; //Bexpress 1. 上門取件/ 到店寄件 Picked up /Dropped off
					break;

				case "4": case "40": case "259": case "261": case "302":
					return "2"; //Bexpress 2. 貨物到達處理中心 Shipment arrived at sorting center / Hub
					break;

				case "3": case "10": case "15": case "23": case "37": case "48": case "50": case "53": case "55":
				 case "108": case "109": case "135": case "153": case "159": case "266":
					return "3"; //Bexpress 3. 貨物派送中 Delivery in progress
					break;

				case "1":
					return "4"; //Bexpress 4. 完成派送 Shipment is received
					break;

				default:
					return "5"; /*Bexpress 5. 異常事件(更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送) 
								Exception (Change the scheduled delivery date/ Loss/Damage/Failed Attempt)
								*/
					break;
			}
		}

		protected static function __doDpexToBexpressCrossTrackingStatus($trackingStatus)
		{
			switch ($trackingStatus) // Dpex
			{
				case "2": case "260":
					return "1"; //Bexpress 1. 上門取件/ 到店寄件 Picked up /Dropped off
					break;

				case "4": case "40": case "259":  case "261": case "302":
					return "2"; //Bexpress 2. 貨物到達處理中心 Shipment arrived at sorting center / Hub
					break;

				case "23": case "55":
					return "3"; //Bexpress 3. 開始發貨 Departure Status
					break;

				case "10": case "37":
					return "4"; //Bexpress 4. 清關完成 Customs Clearance 
					break;

				case "3": case "15": case "48": case "50": case "53": case "108": case "109": case "135":
				case "153": case "159": case "266":
					return "5"; //Bexpress 5. 貨物派送中 Delivery in progress
					break;

				case "1":
					return "6"; //Bexpress 6. 完成派送 Shipment is received
					break;

				default:
					return "7"; /*Bexpress 7. 異常事件(清關延誤/ 更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送) 
								*/
					break;
			}
		}

		private static function __convertTrackingStatus($callname, $result)
		{
			if($callname=="getLocalTrackingStatus") {
				$max = sizeof($result['WSGET']['Event']);
				for($i=0; $i<$max; $i++)
				{
					self::$result['WSGET']['Event'][$i]['EventID'] = self::__doDpexToBexpressLocalTrackingStatus($result['WSGET']['Event'][$i]['EventID']);
				}
			} else if($callname=="getCrossTrackingStatus") {
				$max = sizeof($result['WSGET']['Event']);
				for($i=0; $i<$max; $i++)
				{
					self::$result['WSGET']['Event'][$i]['EventID'] = self::__doDpexToBexpressCrossTrackingStatus($result['WSGET']['Event'][$i]['EventID']);
				}
			}
		}

		public static function getTTInfo($orderno)
		{
			self::$soapClient = self::__getSoapClient("getTTInfo");
			$requestArr = self::__getRequest("getTTInfo", $orderno);
			self::__processRequest($requestArr, self::$soapClient, "getTTInfo");
		}

		private static function __getTTInfo($orderno)
		{
			self::$soapClient = self::__getSoapClient("getTTInfo");
			$requestArr = self::__getRequest("getTTInfo", $orderno);
			self::__processRequest($requestArr, self::$soapClient, "getTTInfo");
		}

		public static function getLocalTrackingStatus($orderno)
		{
			self::__getTTInfo($orderno);
			self::$result = self::getOriginalResult();
			self::__convertTrackingStatus("getLocalTrackingStatus", self::$result);
		
			$max = sizeof(self::$result['WSGET']['Event']);
			$data = array ();
			$dataArr = array ();
			$tempArr = array ();
			$lastState = self::$result['WSGET']['Event'][$max-1]['EventID'];
		
			if(self::$result['WSGET']['AccessRequest']['Action']=='download')
			{
				for($i=0; $i<$max; $i++)
				{
					$tempArr = array (
										'time' => self::$result['WSGET']['Event'][$i]['EventDateTime'],
										'description' => self::$result['WSGET']['Event'][$i]['Remarks'],
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
			return self::$result;
		}

		public static function setShipper($CCSenderName, $CCSenderAdd1, $CCSenderAdd2, $CCSenderAdd3, $CCSenderLocCode, $CCSenderLocName, 
										$CCSenderLocState, $CCSenderLocPostcode, $CCSenderLocCtryCode, $CCSenderContact, 
										$CCSenderPhone, $CCSenderEmail, $CCSenderRef1)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Dpex\DpexParam\ParamCreateOrder();
			$paramCreateOrder::addShipper($CCSenderName, $CCSenderAdd1, $CCSenderAdd2, $CCSenderAdd3, $CCSenderLocCode, $CCSenderLocName, 
										$CCSenderLocState, $CCSenderLocPostcode, $CCSenderLocCtryCode, $CCSenderContact, 
										$CCSenderPhone, $CCSenderEmail, $CCSenderRef1);
			self::$shipper = $paramCreateOrder::getShipper();
		}

		public static function setRecipient($CCReceiverName, $CCReceiverAdd1, $CCReceiverAdd2, $CCReceiverAdd3,
											$CCReceiverLocCode, $CCReceiverLocName, $CCReceiverLocState, $CCReceiverLocPostcode,
											$CCReceiverLocCtryCode, $CCReceiverContact, $CCReceiverPhone, $CCReceiverEmail)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Dpex\DpexParam\ParamCreateOrder();
			$paramCreateOrder::addRecipient($CCReceiverName, $CCReceiverAdd1, $CCReceiverAdd2, $CCReceiverAdd3,
											$CCReceiverLocCode, $CCReceiverLocName, $CCReceiverLocState, $CCReceiverLocPostcode,
											$CCReceiverLocCtryCode, $CCReceiverContact, $CCReceiverPhone, $CCReceiverEmail);
			self::$recipient = $paramCreateOrder::getRecipient();
		}

		public static function setCustomerClearanceDetail($CCAccCardCode, $CCCustDeclaredWeight, $CCWeightMeasure, $CCNumofItems,
													$CCSTypeCode, $CCWeight, $CCSenderRef1, $CCCustomsValue,
													 $CCCustomsCurrencyCode, $CCCubicLength, $CCCubicWidth, $CCCubicHeight,
													 $CCCubicMeasure, $CCCODAmount, $CCBag, $CCSystemNotes, 
													 $CCDeliveryInstructions, $CCGoodsDesc, $CCReceiverPhone2, $CCCreateJob)
		{
			$paramCreateOrder = new \yuenkokeith\spapi\Dpex\DpexParam\ParamCreateOrder();
			$paramCreateOrder::addCustomClearanceDetail($CCAccCardCode, $CCCustDeclaredWeight, $CCWeightMeasure, $CCNumofItems,
													$CCSTypeCode, $CCWeight, $CCSenderRef1, $CCCustomsValue,
													 $CCCustomsCurrencyCode, $CCCubicLength, $CCCubicWidth, $CCCubicHeight,
													 $CCCubicMeasure, $CCCODAmount, $CCBag, $CCSystemNotes, 
													 $CCDeliveryInstructions, $CCGoodsDesc, $CCReceiverPhone2, $CCCreateJob);
			self::$customerClearanceDetail = $paramCreateOrder::getCustomerClearanceDetail(); 
		}

		public static function createOrder()
		{
			self::$soapClient = self::__getSoapClient("createOrder");
			$requestArr = self::__getRequest("createOrder");
			self::__processRequest($requestArr, self::$soapClient, "createOrder");
		}

		public static function spCreateOrder($data)
		{
			/*
				setShipper($CCSenderName, $CCSenderAdd1, $CCSenderAdd2, $CCSenderAdd3, $CCSenderLocCode, $CCSenderLocName,
										$CCSenderLocState, $CCSenderLocPostcode, $CCSenderLocCtryCode, $CCSenderContact,
										$CCSenderPhone, $CCSenderEmail, $CCSenderRef1
			*/
			self::setShipper($data['SenderName'], $data['SenderAddress'], '', '', $data['SenderCountry'], $data['SenderCity'],
                        	$data['SenderProvince'], $data['SenderPostalCode'], 'USD', $data['SenderName'], 
							$data['SenderPhoneNo'] . " " . $data['SenderPhoneNoExt'] , $data['senderEmail'], 'references');

			/*
				setRecipient($CCReceiverName, $CCReceiverAdd1, $CCReceiverAdd2, $CCReceiverAdd3, $CCReceiverLocCode,
								$CCReceiverLocName, $CCReceiverLocState, $CCReceiverLocPostcode,
											$CCReceiverLocCtryCode, $CCReceiverContact, $CCReceiverPhone, $CCReceiverEmail)
			*/
			self::setRecipient($data['ReceiverName'], $data['ReceiverAddress'], '', '', $data['ReceiverCountry'], $data['ReceiverCity'], 
							$data['ReceiverProvince'], $data['ReceiverPostalCode'], 'USD', 
							$data['ReceiverName'], $data['ReceiverPhoneNo'] . " " . $data['ReceiverPhoneNoExt'], $data['receiverEmail']);
			
			/*
				setCustomerClearanceDetail($CCAccCardCode, $CCCustDeclaredWeight, $CCWeightMeasure, $CCNumofItems,
										$CCSTypeCode, $CCWeight, $CCSenderRef1, $CCCustomsValue,
											$CCCustomsCurrencyCode, $CCCubicLength, $CCCubicWidth, $CCCubicHeight,
											$CCCubicMeasure, $CCCODAmount, $CCBag, $CCSystemNotes, 
											$CCDeliveryInstructions, $CCGoodsDesc, $CCReceiverPhone2, $CCCreateJob)
			*/
			/*
				CCSTypeCode = EXD(Express Document) or EXP(Express Parcel)
			*/
			
			self::setCustomerClearanceDetail('013617', $data['ChargeableWeight(kg)'], 'Kgs', $data['NoOfPackage'], 'EXP', 
										$data['ChargeableWeight(kg)'], 'references', $data['TotalDeclaredValue(USD)'], 'USD', '10', '10', '10', 'G', 
										'0', $data['NoOfPackage'], 'system notes', 'special instructions text text', 
										$data['ItemDescription'], $data['ReceiverPhoneNo'] . " " . $data['ReceiverPhoneNoExt'], '0');

			self::createOrder();
		}

		public static function getResult()
		{
			self::$result = json_decode(self::$resultJson, TRUE);
			return self::$result;
		}

		public static function getOrderResult()
		{
			self::$result = json_decode(self::$resultJson, TRUE);

			// generic化 return data
			if(self::$result['WSGET']['Status']['StatusUpdate']==0) {
				self::$result = Array('status'=>200, 'waybillno'=> self::$result['WSGET']['Status']['CC']['CCConnote']);
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
			self::$result = json_decode(self::$resultJson, TRUE);
			return self::$result;
		}

		public static function getJsonResult()
		{
			return self::$resultJson;
		}
		
	}

	interface DpexInterface
	{
		public static function setCredential($accountUsername="", $password="", $entityId="", $entityPin="");
		public static function getTTInfo($orderno);
		public static function getLocalTrackingStatus($orderno);
		public static function getCrossTrackingStatus($orderno);
		public static function setShipper($CCSenderName, $CCSenderAdd1, $CCSenderAdd2, $CCSenderAdd3, $CCSenderLocCode, $CCSenderLocName, 
												$CCSenderLocState, $CCSenderLocPostcode, $CCSenderLocCtryCode, $CCSenderContact, 
												$CCSenderPhone, $CCSenderEmail, $CCSenderRef1);
		public static function setRecipient($CCReceiverName, $CCReceiverAdd1, $CCReceiverAdd2, $CCReceiverAdd3,
													$CCReceiverLocCode, $CCReceiverLocName, $CCReceiverLocState, $CCReceiverLocPostcode,
													$CCReceiverLocCtryCode, $CCReceiverContact, $CCReceiverPhone, $CCReceiverEmail);
		public static function setCustomerClearanceDetail($CCAccCardCode, $CCCustDeclaredWeight, $CCWeightMeasure, $CCNumofItems,
															$CCSTypeCode, $CCWeight, $CCSenderRef1, $CCCustomsValue,
															$CCCustomsCurrencyCode, $CCCubicLength, $CCCubicWidth, $CCCubicHeight,
															$CCCubicMeasure, $CCCODAmount, $CCBag, $CCSystemNotes, 
															$CCDeliveryInstructions, $CCGoodsDesc, $CCReceiverPhone2, $CCCreateJob);
		public static function createOrder();
		public static function spCreateOrder($data);
		public static function getResult();
		public static function getOrderResult();
		public static function getOriginalResult();
		public static function getJsonResult();

	}
	
?>

