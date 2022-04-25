<?php

	//namespace SpAPI\HkPost\HkPostParam;
	namespace yuenkokeith\spapi\HkPost\HkPostParam;
	/*
	class SoapClient {
		public $clientName;

		public function getClient($functionName)
		{
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
					return "https://www.ec-ship.hk/API-trial/services/Posting?wsdl";
					break;

				case "getTotalPostage":
					return "https://www.ec-ship.hk/API-trial/services/Calculator?wsdl";
					break;

				case "getTTInfo":
				case "getMTTInfo": 
					return "https://www.ec-ship.hk/API-trial/services/Tracking?wsdl";
					break;	
			}
		}
	}
	*/

	class ParamCreateOrder {
		function __construct($ecshipUsername, $hkpid, $integratorUsername, $certNumber, 
		$certQty, $countryCode, $creditCardNo, $declarationComments, $dropAndGoFlag, 
												$impEmail, $impFaxNo, $impRef, $impTelNo, $insurAmount, $insurTypeCode, $invoiceNumber, $invoiceQty, 
												$itemCategory, $itemCategoryDesc, $itemNo, $licenceNumber, $mailSize, $mailType, $merchandiserEmail, $nonDeliveryOptions, $payFlag, $permitNo, $pickupOffice,  
											$products= array( array() ), 
										$recipientAddress, $recipientCity, $recipientContactNo, $recipientContactNoAreaCode, $recipientEmail, $recipientFax, $recipientName, 
												$recipientPostalNo, $refNo, $satchelTypeCode, $senderAddress, $senderContactNo, $senderContactNoAreaCode,$senderCountry, $senderCustRef, 
												$senderEmail, $senderFax, $senderName, $shipCode, $noticeMethod, $smsLang, $mcn,
												$iPostalStation, $temporary=false)
		{
			$this->ecshipUsername = $ecshipUsername;
			$this->hkpid = $hkpid;
			$this->integratorUsername = $integratorUsername;
			$this->certNumber = $certNumber;
			$this->certQty = $certQty;
			$this->countryCode = $countryCode;
			$this->creditCardNo = $creditCardNo;
			$this->declarationComments = $declarationComments;
			$this->dropAndGoFlag = $dropAndGoFlag;
			$this->impEmail  = $impEmail;
			$this->impFaxNo  = $impFaxNo;
			$this->impRef  = $impRef;
			$this->impTelNo  = $impTelNo;
			$this->insurAmount  = $insurAmount;
			$this->insurTypeCode  = $insurTypeCode;
			$this->invoiceNumber  = $invoiceNumber;
			$this->invoiceQty  = $invoiceQty;
			$this->itemCategory  = $itemCategory;
			$this->itemCategoryDesc  = $itemCategoryDesc;
			$this->itemNo  = $itemNo;
			$this->licenceNumber  = $licenceNumber;
			$this->mailSize  = $mailSize;
			$this->mailType  = $mailType;
			$this->merchandiserEmail  = $merchandiserEmail;
			$this->nonDeliveryOptions  = $nonDeliveryOptions;
			$this->payFlag  = $payFlag;
			$this->permitNo  = $permitNo;
			$this->pickupOffice  = $pickupOffice;
			$this->products = $products;
			$this->recipientAddress = $recipientAddress;
			$this->recipientCity = $recipientCity;
			$this->recipientContactNo = $recipientContactNo;
			$this->recipientContactNoAreaCode = $recipientContactNoAreaCode;
			$this->recipientEmail = $recipientEmail;
			$this->recipientFax = $recipientFax;
			$this->recipientName = $recipientName;
			$this->recipientPostalNo = $recipientPostalNo;
			$this->refNo = $refNo;
			$this->satchelTypeCode = $satchelTypeCode;
			$this->senderAddress = $senderAddress;
			$this->senderContactNo = $senderContactNo;
			$this->senderContactNoAreaCode = $senderContactNoAreaCode;
			$this->senderCountry = $senderCountry;
			$this->senderCustRef = $senderCustRef;
			$this->senderEmail = $senderEmail;
			$this->senderFax  = $senderFax;
			$this->senderName  = $senderName;
			$this->shipCode  = $shipCode;
			$this->noticeMethod  = $noticeMethod;
			$this->smsLang  = $smsLang;
			$this->mcn  = $mcn;
			$this->iPostalStation  = $iPostalStation;
			
			
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

	class ParamCancelOrderInfo {
		function __construct($ecshipUsername, $integratorUsername, $itemno, $orderno) 
		{
			$this->ecshipUsername = $ecshipUsername;
			$this->integratorUsername = $integratorUsername;
			$this->orderNo = $orderno;
			$this->itemNo = $itemno;
		}
	}

	class ParamGetMTTInfo {
		function __construct($ecshipUsername, $integratorUsername, $itemno) 
		{
			$this->ecshipUsername = $ecshipUsername;
			$this->integratorUsername = $integratorUsername;
			$this->itemNo = $itemno;
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

	class ParamGetAddressPack {
		function __construct($ecshipUsername, $integratorUsername, $itemno, $printmode) 
		{
			$this->ecshipUsername = $ecshipUsername;
			$this->integratorUsername = $integratorUsername;
			$this->itemNo = $itemno;
			$this->printMode = $printmode;
		}
	}

	class ParamCancelTemporaryOrder {
		function __construct($ecshipUsername, $integratorUsername, $orderno) 
		{
			$this->ecshipUsername = $ecshipUsername;
			$this->integratorUsername = $integratorUsername;
			$this->orderNo = $orderno;
		}
	}

	class ParamGetItemNo {
		function __construct($ecshipUsername, $integratorUsername, $searchParam, $searchType, $sortOrder) 
		{
			$this->ecshipUsername = $ecshipUsername;
			$this->integratorUsername = $integratorUsername;
			$this->searchParam = $searchParam;
			$this->searchType = $searchType;
			$this->sortOrder = $sortOrder;
		}
	}

	class ParamGetTotalPostage {
		function __construct($ecshipUsername, $hkpid, $integratorUsername, $countryCode, $insuranceAmount, $insuranceTypeCode, $mailType, $mailSize, $shipCode, $weight)
		{
			$this->ecshipUsername = $ecshipUsername;
			$this->hkpid = $hkpid;
			$this->integratorUsername = $integratorUsername;
			$this->countryCode = $countryCode;
			$this->insuranceAmount = $insuranceAmount;
			$this->insuranceTypeCode = $insuranceTypeCode;
			$this->mailType = $mailType;
			$this->mailSize = $mailSize;
			$this->shipCode = $shipCode;
			$this->weight = $weight;
		}
	}

	class SoapObjToArray {
		public $result = array();
		public $errMessage;
		
		public function getResultArr()
		{
			return $this->result;
		}
		
		public function getKeyValue($callname, $keyname)
		{
			if (is_array($this->result[$callname][$keyname])) {
				return $this->result[$callname][$keyname];
			} else {
				return $this->result[$callname][$keyname];
			}
		}
		
		public function getTotalPostageReturn($obj) {
			$this->result = $this->__getArray($obj);
		}
		
		private function __getArray($obj) 
		{
			$out = array();
			foreach ($obj as $key => $val) {
				switch(true) {
					case is_object($val):
					$out[$key] = $this->__getArray($val);
					break;
				case is_array($val):
					$out[$key] = $this->__getArray($val);
					break;
				default:
					$out[$key] = $val;
				}
			}
			return $out;
		}
	}

?>