<?php

	//namespace SpAPI\HkPost;
	namespace yuenkokeith\spapi;
	//require_once('HkPostAPI\HkPostParam.php');
	use yuenkokeith\spapi\HkPost\HkPostParam;
	
	class api01Req {
		function api01Req() 
		{
			$this->ecshipUsername = 'tapi_demo_account';
			$this->integratorUsername = 'api_demo_account';
			$this->countryCode = 'USA';
			$this->shipCode= 'AEX';
			$this->weight='1';
		}
	}

	class HkPost extends \SpAPI\SpAPI implements HkPostInterface {

		public $tm_created = null;
		public $tm_expires = null; 
		public $simple_nonce = null;
		public $encoded_nonce = null;
		public $username = null; 
		public $password = null;
		public $passdigest = null;
		public $ns_wsse = null;
		public $ns_wsu = null;
		public $password_type = null;
		public $encoding_type = null;
		public $root = null;
		public $security = null;
		public $usernameToken = null;
		public $full = null;
		public $auth = null;
		public $objSoapVarWSSEHeader = null;
		public $objClient = null;
		public $apiReq = null;
		public $params = null;
		public $objResponse = null;
		public $ecshipUsername = "tapi_demo_account";
		public $integratorUsername = "api_demo_account";
		public $hkpid = "";
		public $soapClient = null;
		private $localTrackingStatus = null;
		private $crossTrackingStatus = null;
		private $result = null;
		private $resultJson = null;
		private $isLiveMode = false;

		function __construct()
		{
			$this->isLiveMode=false;
			// Creating date using yyyy-mm-ddThh:mm:ssZ format
			$this->tm_created = gmdate('Y-m-d\TH:i:s\Z');
			$this->tm_expires = gmdate('Y-m-d\TH:i:s\Z', gmdate('U') + 180);
			
			// Generating, packing and encoding a random number
			$this->simple_nonce = mt_rand();
			$this->encoded_nonce = base64_encode(pack('H*', $this->simple_nonce));
			$this->username = "api_demo_account"; 
			$this->password = "UTUb5TCnA5eOQCVJ1X0n7xn3Z";
			$this->passdigest = base64_encode(pack('H*',sha1(pack('H*', $this->simple_nonce) . pack('a*', $this->tm_created) . pack('a*', $this->password))));
			
			// Initializing namespaces
			$this->ns_wsse = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
			$this->ns_wsu = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
			$this->password_type = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest';
			$this->encoding_type = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary';
		}

		public function setCredential($username="", $password="", $isLiveMode=true)
		{
			// Get from DB later
			$this->isLiveMode = $isLiveMode;
			$this->username = $username;
			$this->$password = $password;
		}

		private function __setClient($client='')
		{
			// Creating WSS identification header using SimpleXML
			$this->root = new \SimpleXMLElement('<root/>');
			$this->security = $this->root->addChild('wsse:Security', null, $this->ns_wsse);
			$this->usernameToken = $this->security->addChild('wsse:UsernameToken', null, $this->ns_wsse);
			$this->usernameToken->addChild('wsse:Username', $this->username, $this->ns_wsse);
			$this->usernameToken->addChild('wsse:Password', $this->passdigest, $this->ns_wsse)->addAttribute('Type', $this->password_type);
			$this->usernameToken->addChild('wsse:Nonce', $this->encoded_nonce, $this->ns_wsse)->addAttribute('EncodingType', $this->encoding_type);
			$this->usernameToken->addChild('wsu:Created', $this->tm_created, $this->ns_wsu);
		
			// Recovering XML value from that object
			$this->root->registerXPathNamespace('wsse', $this->ns_wsse);
			$this->full = $this->root->xpath('/root/wsse:Security');
			$this->auth = $this->full[0]->asXML();
			$this->objSoapVarWSSEHeader = new \SoapHeader($this->ns_wsse, 'Security', new \SoapVar($this->auth, XSD_ANYXML), true);
			$this->objClient = new \SoapClient($client);
			$this->objClient->__setSoapHeaders(array($this->objSoapVarWSSEHeader));
		}

		private function __getClient($functionName)
		{
			if($this->isLiveMode) {
				$hostPath = "API";
			} else { // deve test mode
				$hostPath = "API-trial";
			}
	
			switch ($functionName)
			{
				case "getOrderInfo":
				case "getTemporaryOrderInfo":
				case "cancelTemporaryOrder":
				case "createTemporaryOrder":
				case "getAddressPack":
				case "cancelOrder":
				case "createOrder":
				case "getItemNo":
				case "getCOP":
					return "https://service.hongkongpost.hk/" . $hostPath . "/services/Posting?wsdl";
					break;

				case "getTotalPostage":
					return "https://service.hongkongpost.hk/" . $hostPath . "/services/Calculator?wsdl";
					break;

				case "getTTInfo":
				case "getMTTInfo":
					return "https://service.hongkongpost.hk/" . $hostPath . "/services/Tracking?wsdl";
					break;	
			}
		}

		private function __getSoapCall($callname)
		{
			$this->objResponse = $this->objClient->__soapCall($callname,array($this->params));
			$this->__getResultArr();
			if($callname=="getMTTInfo")
				$this->__convertTrackingStatus($callname);
		}
		
		private function __getObjResponse()
		{
			return $this->objResponse;
		}
		
		// Get Result Data
		private function __getResultArr()
		{
			$objResponse = $this->__getObjResponse();
			$soapObjToArray = new \SpAPI\HkPost\HkPostParam\SoapObjToArray();
			$soapObjToArray->getTotalPostageReturn($objResponse);
			$this->result = $soapObjToArray->getResultArr();
			$this->resultJson = json_encode($this->result);
		}

		private function __convertTrackingStatus($callname)
		{
			$objResponse = $this->__getObjResponse();
			$soapObjToArray = new \SpAPI\HkPost\HkPostParam\SoapObjToArray();
			$soapObjToArray->getTotalPostageReturn($objResponse);
			$trackingStatus = $soapObjToArray->getKeyValue($callname. "Return", 'ttStatus');
			$this->localTrackingStatus = $this->__doPostageToBexpressLocalTrackingStatus($trackingStatus);
			$this->crossTrackingStatus = $this->__doPostageToBexpressCrossTrackingStatus($trackingStatus);
		}

		protected function __doPostageToBexpressLocalTrackingStatus($trackingStatus)
		{
			switch ($trackingStatus)
			{
				case "0":
					return "1"; //Bexpress 1. 上門取件/ 到店寄件 Picked up /Dropped off
					break;

				case "2":
				case "13":
					return "2"; //Bexpress 2. 貨物到達處理中心 Shipment arrived at sorting center / Hub
					break;

				case "3":
				case "4":
					return "3"; //Bexpress 3. 貨物派送中 Delivery in progress
					break;

				case "5":
					return "4"; //Bexpress 4. 完成派送 Shipment is received
					break;

				case "1":
				case "6":
				case "7":
				case "8":
				case "9":
				case "10":
					return "5"; /*Bexpress 5. 異常事件(更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送) 
								Exception (Change the scheduled delivery date/ Loss/Damage/Failed Attempt)
								*/
					break;
			}
		}

		protected function __doPostageToBexpressCrossTrackingStatus($trackingStatus)
		{
			switch ($trackingStatus)
			{
				case "0":
					return "1"; //Bexpress 1. 上門取件/ 到店寄件 Picked up /Dropped off
					break;

				case "2":
					return "2"; //Bexpress 2. 貨物到達處理中心 Shipment arrived at sorting center / Hub
					break;

				case "3":
					return "3"; //Bexpress 3. 開始發貨 Departure Status
					break;

				case "4":
					return "4"; //Bexpress 4. 清關完成 Customs Clearance
					break;

				case "13":
					return "5"; //Bexpress 5. 貨物派送中 Delivery in progress
					break;

				case "5":
					return "6"; //Bexpress 6. 完成派送 Shipment is received
					break;

				case "1":
				case "6":
				case "7":
				case "8":
				case "9":
				case "10":
					return "7"; /*Bexpress 7. 異常事件(清關延誤/ 更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送) 
								*/
					break;
			}
		}

		public function getLocalTrackingStatus($itemNo)
		{
			$apiObj = $this->setGetMTTInfo($itemNo); // itemno  #KK981387689HK
            $this->getMTTInfo($apiObj); // Calling the API member function 

			$originalresult = $this->getOriginalResult();
			$result = array ();

			if($originalresult['getMTTInfoReturn']['status']==0)
			{
				$result = Array('status' => 200,
								'state' => $this->localTrackingStatus,
								'data' => array (
												'time' => $originalresult['getMTTInfoReturn']['trackingDate'],
												'description' => '',
												)
								);
			}
			else
			{
				$result = Array('status' => 500);
			}

			return $result;
		}
		
		public function getCrossTrackingStatus($itemNo)
		{
			$apiObj = $this->setGetMTTInfo($itemNo); // itemno  #KK981387689HK
            $this->getMTTInfo($apiObj); // Calling the API member function 

			$originalresult = $this->getOriginalResult();
			$result = array ();

			if($originalresult['getMTTInfoReturn']['status']==0)
			{
				$result = Array('status' => 200,
								'state' => $this->crossTrackingStatus,
								'data' => array (
												'time' => $originalresult['getMTTInfoReturn']['trackingDate'],
												'description' => '',
												)
								);
			}
			else
			{
				$result = Array('status' => 500);
			}

			return $result;
		}

		public function getOrderResult()
		{
			$result = $this->result;

			// generic化 return data
			if($result['createOrderReturn']['status']==0) {
				$result = Array('status'=>200, 'waybillno'=> $result['createOrderReturn']['itemNo']);
			} else {
				self::$result = Array('status' => 500,
									'code' => '',
									'description' => ''
				);
			}

			return $result;
		}

		public function getOriginalResult()
		{
			return $this->result;
		}
		
		public function getResult()
		{
			return $this->result;
		}
		
		public function getSoapObjResult()
		{
			$objResponse = $this->__getObjResponse();
			$soapObjToArray = new \SpAPI\HkPost\HkPostParam\SoapObjToArray();
			$soapObjToArray->getTotalPostageReturn($objResponse);
			return $soapObjToArray;
		}

		// ********************* Lists of Soap Functions *********************
		public function getOrderInfo($apiObj)
		{
			//$client = $this->soapClient->getClient("getOrderInfo");
			$client = $this->__getClient("getOrderInfo");
			$this->__setClient($client);
			$this->params = array("api07Req" => $apiObj); // Register API to use
			$this->__getSoapCall("getOrderInfo"); // Calling the API member function
		}

		public function getTemporaryOrderInfo($apiObj)
		{
			//$client = $this->soapClient->getClient("getTemporaryOrderInfo");
			$client = $this->__getClient("getTemporaryOrderInfo");
			$this->__setClient($client);
			$this->params = array("api07Req" => $apiObj); // Register API to use
			$this->__getSoapCall("getTemporaryOrderInfo"); // Calling the API member function
		}

		public function cancelTemporaryOrder($apiObj)
		{
			//$client = $this->soapClient->getClient("cancelTemporaryOrder");
			$client = $this->__getClient("cancelTemporaryOrder");
			$this->__setClient($client);
			$this->params = array("api12Req" => $apiObj); // Register API to use
			$this->__getSoapCall("cancelTemporaryOrder"); // Calling the API member function
		}

		public function createTemporaryOrder($apiObj)
		{
			//$client = $this->soapClient->getClient("createTemporaryOrder");
			$client = $this->__getClient("createTemporaryOrder");
			$this->__setClient($client);
			$this->params = array("api02Req" => $apiObj); // Register API to use
			$this->__getSoapCall("createTemporaryOrder"); // Calling the API member function
		}

		public function getAddressPack($apiObj)
		{
			//$client = $this->soapClient->getClient("getAddressPack");
			$client = $this->__getClient("getAddressPack");
			$this->__setClient($client);
			$this->params = array("api11Req" => $apiObj); // Register API to use
			$this->__getSoapCall("getAddressPack"); // Calling the API member function
		}

		public function cancelOrder($apiObj)
		{
			//$client = $this->soapClient->getClient("cancelOrder");
			$client = $this->__getClient("cancelOrder");
			$this->__setClient($client);
			$this->params = array("api03Req" => $apiObj); // Register API to use
			$this->__getSoapCall("cancelOrder"); // Calling the API member function
		}

		public function createOrder($data)
		{
			//$client = $this->soapClient->getClient("createOrder");
			$client = $this->__getClient("createOrder");
			$this->__setClient($client);
			$this->params = array("api02Req" => $data); // Register API to use
			$this->__getSoapCall("createOrder"); // Calling the API member function

			if($this->result['createOrderReturn']['status']==0)
			{
				$this->createAndDownloadPDF($this->result['createOrderReturn']['itemNo']);
			}
		}

		
		public function spCreateOrder($data)
		{
			$products = array(
				'item' => array(
						'contentDesc' => $data['FirstShipmentItem'],
						'currencyCode' => 'USD',
						'productCountry' => 'CNG',
						'productQty' => 1,
						'productTariffCode' => 1,
						'productValue' => $data['TotalDeclaredValue(USD)'],
						'productWeight' => $data['ChargeableWeight(kg)']
						),
				'item' => array(
						'contentDesc' => $data['SecondShipmentItem'],
						'currencyCode' => 'USD',
						'productCountry' => 'CNG',
						'productQty' => 1,
						'productTariffCode' => 1,
						'productValue' => 1,
						'productWeight' => 0.001
						),
				'item' => array(
					'contentDesc' => $data['ThirdShipmentItem'],
					'currencyCode' => 'USD',
					'productCountry' => 'CNG',
					'productQty' => 1,
					'productTariffCode' => 1,
					'productValue' => 1,
					'productWeight' => 0.001
					)
			);


			/*
				setCreateOrderParam($certNumber, $certQty, $countryCode, $declarationComments, $impEmail, $impFaxNo, $impRef, $impTelNo, 
								$insurAmount, $insurTypeCode, $invoiceNumber, $invoiceQty, $itemCategory, $licenceNumber, $mailType, $merchandiserEmail,
								$nonDeliveryOptions, $paidAmt, $products= array( array() ), 
								$recipientAddress, $recipientCity, $recipientContactNo, $recipientEmail, $recipientFax, $recipientName, $recipientPostalNo, 
								$refNo, $satchelTypeCode,
								$senderAddress, $senderContactNo, $senderCountry, $senderCustRef, $senderEmail, $senderFax, $senderName, 
								$shipCode, $noticeMethod, $smsLang)

				shipCode: {ARM - 空郵郵件
							APL - 空郵包裹
							SMP - 易送遞
							LPL - 本地包裹
							}
			*/
			
			$apiObj = $this->setCreateOrderParam('0', 1, $data['ReceiverCountry'], '', '', '', '', '', 
							null, null, $data['bexOrderId'], 1, $data['Product'], '', '', $data['senderEmail'], 
							'1', $data['TotalDeclaredValue(USD)'], 
							$products, 
							$data['ReceiverAddress'], $data['ReceiverCity'], $data['ReceiverPhoneNo'] . " " . $data['ReceiverPhoneNoExt'], $data['receiverEmail'], '', $data['ReceiverName'], $data['ReceiverPostalCode'], 
							'By Bexpress', null, 
							$data['SenderAddress'], $data['SenderPhoneNo'] . " " . $data['SenderPhoneNoExt'], $data['SenderCountry'], '', $data['senderEmail'], '', $data['SenderName'],
							'APL', 'E', 'chi');
							
							

			//$client = $this->soapClient->getClient("createOrder");
			$client = $this->__getClient("createOrder");
			$this->__setClient($client);
			$this->params = array("api02Req" => $apiObj); // Register API to use
			$this->__getSoapCall("createOrder"); // Calling the API member function

			if($this->result['createOrderReturn']['status']==0)
			{
				$this->createAndDownloadPDF($this->result['createOrderReturn']['itemNo']);
			}
		}
		

		public function createAndDownloadPDF($itemNo)
		{
			$items = array (
					'item' => array(new \SoapVar($itemNo, XSD_STRING, null, null, 'itemNo')),
					);

			$apiObj = $this->setGetAddressPack($items, '0'); // itemno, printmode 0 => (1 label per page) 2 => (3 labels per page) (This setting for “e-Express service to the US” only) 
			
			//$client = $this->soapClient->getClient("getAddressPack");
			$client = $this->__getClient("getAddressPack");
			$this->__setClient($client);
			$this->params = array("api11Req" => $apiObj); // Register API to use

			$this->objResponse = $this->objClient->__soapCall("getAddressPack",array($this->params));

			$objResponse = $this->__getObjResponse();
			$soapObjToArray = new \SpAPI\HkPost\HkPostParam\SoapObjToArray();
			$soapObjToArray->getTotalPostageReturn($objResponse);
			$labelResult = $soapObjToArray->getResultArr();

			if($labelResult['getAddressPackReturn']['status']==0)
			{
				$waybillno = $itemNo;
				$contents = $labelResult['getAddressPackReturn']['ap'];
				$labelfilename = "hkpost_" .  $waybillno . "_label.pdf";
				$fp = fopen(APP . "../../uploadFiles/waybilllabel/hkpost/" . $labelfilename , 'wb');   
				fwrite($fp, ($contents));
				fclose($fp);
			}
		}

		public function getItemNo($apiObj)
		{
			//$client = $this->soapClient->getClient("getItemNo");
			$client = $this->__getClient("getItemNo");
			$this->__setClient($client);
			$this->params = array("api26Req" => $apiObj); // Register API to use
			$this->__getSoapCall("getItemNo"); // Calling the API member function
		}

		public function getCOP($apiObj)
		{
			//$client = $this->soapClient->getClient("getCOP");
			$client = $this->__getClient("getCOP");
			$this->__setClient($client);
			$this->params = array("api10Req" => $apiObj); // Register API to use
			$this->__getSoapCall("getCOP"); // Calling the API member function
		}
		
		public function getTotalPostage($apiObj)
		{
			//$client = $this->soapClient->getClient("getTotalPostage");
			$client = $this->__getClient("getTotalPostage");
			$this->__setClient($client);
			
			$this->params = array("api01Req" => $apiObj); // $apiObj Register API to use
			$this->__getSoapCall("getTotalPostage"); // Calling the API member function
		}

		public function getTTInfo($apiObj)
		{
			//$client = $this->soapClient->getClient("getTTInfo");
			$client = $this->__getClient("getTTInfo");
			$this->__setClient($client);
			$this->params = array("api05Req" => $apiObj); // Register API to use
			$this->__getSoapCall("getTTInfo"); // Calling the API member function
		}

		public function getMTTInfo($apiObj)
		{
			//$client = $this->soapClient->getClient("getMTTInfo");
			$client = $this->__getClient("getTTInfo");
			$this->__setClient($client);
			$this->params = array("api04Req" => $apiObj); // Register API to use
			$this->__getSoapCall("getMTTInfo"); // Calling the API member function
		}

		// Original Soap Return Data for debug
		public function getSoapCallResult()
		{
			return $this->__getObjResponse();
		}

		public function setCreateOrderParam($certNumber, $certQty, $countryCode, $creditCardNo, $declarationComments, $dropAndGoFlag, 
												$impEmail, $impFaxNo, $impRef, $impTelNo, $insurAmount, $insurTypeCode, $invoiceNumber, $invoiceQty, 
												$itemCategory, $itemCategoryDesc, $itemNo, $licenceNumber, $mailSize, $mailType, $merchandiserEmail, $nonDeliveryOptions, $payFlag, $permitNo, $pickupOffice,  
												$products= array( array() ), 
												
												$recipientAddress, $recipientCity, $recipientContactNo, $recipientContactNoAreaCode, $recipientEmail, $recipientFax, $recipientName, 
												$recipientPostalNo, $refNo, $satchelTypeCode, $senderAddress, $senderContactNo, $senderContactNoAreaCode,$senderCountry, $senderCustRef, 
												$senderEmail, $senderFax, $senderName, $shipCode, $noticeMethod, $smsLang, $mcn,
												$iPostalStation)
		{
			return $param = new \SpAPI\HkPost\HkPostParam\ParamCreateOrder($this->ecshipUsername, $this->hkpid, $this->integratorUsername, $certNumber, $certQty, $countryCode, $creditCardNo, $declarationComments, $dropAndGoFlag, 
												$impEmail, $impFaxNo, $impRef, $impTelNo, $insurAmount, $insurTypeCode, $invoiceNumber, $invoiceQty, 
												$itemCategory, $itemCategoryDesc, $itemNo, $licenceNumber, $mailSize, $mailType, $merchandiserEmail, $nonDeliveryOptions, $payFlag, $permitNo, $pickupOffice, 
												$products, 
												$recipientAddress, $recipientCity, $recipientContactNo, $recipientContactNoAreaCode, $recipientEmail, $recipientFax, $recipientName, 
												$recipientPostalNo, $refNo, $satchelTypeCode, $senderAddress, $senderContactNo, $senderContactNoAreaCode,$senderCountry, $senderCustRef, 
												$senderEmail, $senderFax, $senderName, $shipCode, $noticeMethod, $smsLang, $mcn,
												$iPostalStation);
		}

		public function setCreateTemporaryOrder($certNumber, $certQty, $countryCode, $declarationComments, $impEmail, $impFaxNo, $impRef, $impTelNo, 
											$insurAmount, $insurTypeCode, $invoiceNumber, $invoiceQty, $itemCategory, $licenceNumber, $mailType, $merchandiserEmail,
											$nonDeliveryOptions, $paidAmt, $products= array( array() ), $recipientAddress, $recipientCity, $recipientContactNo, $recipientEmail, 
											$recipientFax, $recipientName, $recipientPostalNo, $refNo, $satchelTypeCode, $senderAddress,
											$senderContactNo, $senderCountry, $senderCustRef, $senderEmail, $senderFax, $senderName, $shipCode, $noticeMethod, $smsLang, $mcn)
		{
			return $param = new \SpAPI\HkPost\HkPostParam\ParamCreateOrder($this->ecshipUsername, $this->integratorUsername, $certNumber, $certQty, $countryCode, $declarationComments, $impEmail, 
											$impFaxNo, $impRef, $impTelNo, $insurAmount, $insurTypeCode, $invoiceNumber, $invoiceQty, $itemCategory, $licenceNumber, $mailType, 
											$merchandiserEmail, $nonDeliveryOptions, $paidAmt, $products, $recipientAddress, $recipientCity, $recipientContactNo, $recipientEmail, 
											$recipientFax, $recipientName, $recipientPostalNo, $refNo, $satchelTypeCode, $senderAddress, $senderContactNo, $senderCountry, 
											$senderCustRef, $senderEmail, $senderFax, $senderName, $shipCode, $noticeMethod, $smsLang, $mcn, true);
		}

		public function setGetOrderInfo($orderno)
		{
			return $param = new \SpAPI\HkPost\HkPostParam\ParamGetOrderInfo($this->ecshipUsername, $this->integratorUsername, $orderno);
		}

		public function setGetTemporaryOrderInfo($orderno)
		{
			return $param = new \SpAPI\HkPost\HkPostParam\ParamGetOrderInfo($this->ecshipUsername, $this->integratorUsername, $orderno);
		}

		public function setCancelOrder($itemno, $orderno)
		{
			return $param = new \SpAPI\HkPost\HkPostParam\ParamCancelOrderInfo($this->ecshipUsername, $this->integratorUsername, $itemno, $orderno);
		}

		public function setGetMTTInfo($itemno)
		{
           
			return $param = new \SpAPI\HkPost\HkPostParam\ParamGetMTTInfo($this->ecshipUsername, $this->integratorUsername, $itemno);
		}

		public function setGetTTInfo($itemno, $lang)
		{
			return $param = new \SpAPI\HkPost\HkPostParam\ParamGetTTInfo($this->ecshipUsername, $this->integratorUsername, $itemno, $lang);
		}

		public function setGetAddressPack($itemno, $printmode)
		{
			return $param = new \SpAPI\HkPost\HkPostParam\ParamGetAddressPack($this->ecshipUsername, $this->integratorUsername, $itemno, $printmode);
		}

		public function setCancelTemporaryOrder($orderno)
		{
			return $param = new \SpAPI\HkPost\HkPostParam\ParamCancelTemporaryOrder($this->ecshipUsername, $this->integratorUsername, $orderno);
		}

		public function setGetItemNo($searchParam, $searchType, $sortOrder)
		{
			return $param = new \SpAPI\HkPost\HkPostParam\ParamGetItemNo($this->ecshipUsername, $this->integratorUsername, $searchParam, $searchType, $sortOrder);
		}

		public function setGetTotalPostage($countryCode, $insuranceAmount, $insuranceTypeCode, $mailType, $mailSize, $shipCode, $weight)
		{
			return $param = new \SpAPI\HkPost\HkPostParam\paramGetTotalPostage($this->ecshipUsername, $this->hkpid, $this->integratorUsername, $countryCode, $insuranceAmount, $insuranceTypeCode, $mailType, $mailSize, $shipCode, $weight);
		}

		public function setGetCOP($itemno)
		{
			return $param = new \SpAPI\HkPost\HkPostParam\ParamGetMTTInfo($this->ecshipUsername, $this->integratorUsername, $itemno);
		}
		
	}

	interface HkPostInterface
	{
		public function setCredential($username="", $password="", $isLiveMode=true);
		public function getLocalTrackingStatus($itemNo);
		public function getCrossTrackingStatus($itemNo);
		public function getOrderResult();
		public function getOriginalResult();
		public function getResult();
		public function getSoapObjResult();
		public function getOrderInfo($apiObj);
		public function getTemporaryOrderInfo($apiObj);
		public function cancelTemporaryOrder($apiObj);
		public function createTemporaryOrder($apiObj);
		public function getAddressPack($apiObj);
		public function cancelOrder($apiObj);
		public function createOrder($data);
		public function spCreateOrder($data);
		public function createAndDownloadPDF($itemNo);
		public function getItemNo($apiObj);
		public function getCOP($apiObj);
		public function getTotalPostage($apiObj);
		public function getTTInfo($apiObj);
		public function getMTTInfo($apiObj);
		public function getSoapCallResult();
		public function setCreateOrderParam($certNumber, $certQty, $countryCode, $creditCardNo, $declarationComments, $dropAndGoFlag, 
												$impEmail, $impFaxNo, $impRef, $impTelNo, $insurAmount, $insurTypeCode, $invoiceNumber, $invoiceQty, 
												$itemCategory, $itemCategoryDesc, $itemNo, $licenceNumber, $mailSize, $mailType, $merchandiserEmail, $nonDeliveryOptions, $payFlag, $permitNo, $pickupOffice,  
												$products= array( array() ), 
												
												$recipientAddress, $recipientCity, $recipientContactNo, $recipientContactNoAreaCode, $recipientEmail, $recipientFax, $recipientName, 
												$recipientPostalNo, $refNo, $satchelTypeCode, $senderAddress, $senderContactNo, $senderContactNoAreaCode,$senderCountry, $senderCustRef, 
												$senderEmail, $senderFax, $senderName, $shipCode, $noticeMethod, $smsLang, $mcn,
												$iPostalStation);
		public function setCreateTemporaryOrder($certNumber, $certQty, $countryCode, $declarationComments, $impEmail, $impFaxNo, $impRef, $impTelNo, 
													$insurAmount, $insurTypeCode, $invoiceNumber, $invoiceQty, $itemCategory, $licenceNumber, $mailType, $merchandiserEmail,
													$nonDeliveryOptions, $paidAmt, $products= array( array() ), $recipientAddress, $recipientCity, $recipientContactNo, $recipientEmail, 
													$recipientFax, $recipientName, $recipientPostalNo, $refNo, $satchelTypeCode, $senderAddress,
													$senderContactNo, $senderCountry, $senderCustRef, $senderEmail, $senderFax, $senderName, $shipCode, $noticeMethod, $smsLang, $mcn);
		public function setGetOrderInfo($orderno);
		public function setGetTemporaryOrderInfo($orderno);
		public function setCancelOrder($itemno, $orderno);
		public function setGetMTTInfo($itemno);
		public function setGetTTInfo($itemno, $lang);
		public function setGetAddressPack($itemno, $printmode);
		public function setCancelTemporaryOrder($orderno);
		public function setGetItemNo($searchParam, $searchType, $sortOrder);
		public function setGetTotalPostage($countryCode, $insuranceAmount, $insuranceTypeCode, $mailType, $mailSize, $shipCode, $weight);
		public function setGetCOP($itemno);

	}
	
?>

