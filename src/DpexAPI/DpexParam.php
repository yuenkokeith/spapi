<?php

	namespace yuenkokeith\spapi\Dpex\DpexParam;

	class ParamCreateOrder
	{
		private static $shipper = null;
		private static $recipient = null;
		private static $customerClearanceDetail = null;

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

		public static function addShipper($CCSenderName, $CCSenderAdd1, $CCSenderAdd2, 
										$CCSenderAdd3, $CCSenderLocCode, $CCSenderLocName, 
										$CCSenderLocState, $CCSenderLocPostcode, 
										$CCSenderLocCtryCode, $CCSenderContact, 
										$CCSenderPhone, $CCSenderEmail, $CCSenderRef1)
		{
			self::$shipper = '<CCSenderName>' . $CCSenderName . '</CCSenderName>
									  <CCSenderAdd1>' . $CCSenderAdd1 . '</CCSenderAdd1>
									  <CCSenderAdd2>' . $CCSenderAdd2 . '</CCSenderAdd2>
									  <CCSenderAdd3>' . $CCSenderAdd3 . '</CCSenderAdd3>
									  <CCSenderLocCode>' . $CCSenderLocCode . '</CCSenderLocCode>
									  <CCSenderLocName>' . $CCSenderLocName . '</CCSenderLocName>
									  <CCSenderLocState>' . $CCSenderLocState . '</CCSenderLocState>
									  <CCSenderLocPostcode>' . $CCSenderLocPostcode . '</CCSenderLocPostcode>
									  <CCSenderLocCtryCode>' . $CCSenderLocCtryCode . '</CCSenderLocCtryCode>
									  <CCSenderContact>' . $CCSenderContact . '</CCSenderContact>
									  <CCSenderPhone>' . $CCSenderPhone . '</CCSenderPhone>
									  <CCSenderEmail>' . $CCSenderEmail . '</CCSenderEmail>
									  <CCSenderRef1>' . $CCSenderRef1 . '</CCSenderRef1>';
		}

		public static function addRecipient($CCReceiverName, $CCReceiverAdd1, $CCReceiverAdd2, $CCReceiverAdd3,
											$CCReceiverLocCode, $CCReceiverLocName, $CCReceiverLocState, $CCReceiverLocPostcode,
											$CCReceiverLocCtryCode, $CCReceiverContact, $CCReceiverPhone, $CCReceiverEmail)
		{
			self::$recipient = '<CCReceiverName>' . $CCReceiverName . '</CCReceiverName>
									  <CCReceiverAdd1>' . $CCReceiverAdd1 . '</CCReceiverAdd1>
									  <CCReceiverAdd2>' . $CCReceiverAdd2 . '</CCReceiverAdd2>
									  <CCReceiverAdd3>' . $CCReceiverAdd3 . '</CCReceiverAdd3>
									  <CCReceiverLocCode>' . $CCReceiverLocCode . '</CCReceiverLocCode>
									  <CCReceiverLocName>' . $CCReceiverLocName . '</CCReceiverLocName>
									  <CCReceiverLocState>' . $CCReceiverLocState . '</CCReceiverLocState>
									  <CCReceiverLocPostcode>' . $CCReceiverLocPostcode . '</CCReceiverLocPostcode>
									  <CCReceiverLocCtryCode>' . $CCReceiverLocCtryCode . '</CCReceiverLocCtryCode>
									  <CCReceiverContact>' . $CCReceiverContact . '</CCReceiverContact>
									  <CCReceiverPhone>' . $CCReceiverPhone . '</CCReceiverPhone>
									  <CCReceiverEmail>' . $CCReceiverEmail . '</CCReceiverEmail>';                
		}

		public static function addCustomClearanceDetail($CCAccCardCode, $CCCustDeclaredWeight, $CCWeightMeasure, $CCNumofItems,
													$CCSTypeCode, $CCWeight, $CCSenderRef1, $CCCustomsValue,
													 $CCCustomsCurrencyCode, $CCCubicLength, $CCCubicWidth, $CCCubicHeight,
													 $CCCubicMeasure, $CCCODAmount, $CCBag, $CCSystemNotes, 
													 $CCDeliveryInstructions, $CCGoodsDesc, $CCReceiverPhone2, $CCCreateJob)
		{
			self::$customerClearanceDetail = '<CCAccCardCode>' . $CCAccCardCode . '</CCAccCardCode>
									  <CCCustDeclaredWeight>' . $CCCustDeclaredWeight . '</CCCustDeclaredWeight>
									  <CCWeightMeasure>' . $CCWeightMeasure . '</CCWeightMeasure>
									  <CCNumofItems>' . $CCNumofItems . '</CCNumofItems>
									  <CCSTypeCode>' . $CCSTypeCode . '</CCSTypeCode>
									  <CCWeight>' . $CCWeight . '</CCWeight>
									  <CCSenderRef1>' . $CCSenderRef1 . '</CCSenderRef1>
									  <CCSenderRef2/>
									  <CCSenderRef3/>
									  <CCCustomsValue>' . $CCCustomsValue . '</CCCustomsValue>
									  <CCCustomsCurrencyCode>' . $CCCustomsCurrencyCode . '</CCCustomsCurrencyCode>
									  <CCClearanceRef/>
									  <CCCubicLength>' . $CCCubicLength . '</CCCubicLength>
									  <CCCubicWidth>' . $CCCubicWidth . '</CCCubicWidth>
									  <CCCubicHeight>' . $CCCubicHeight . '</CCCubicHeight>
									  <CCCubicMeasure>' . $CCCubicMeasure . '</CCCubicMeasure>
									  <CCCODAmount>' . $CCCODAmount . '</CCCODAmount>
									  <CCCODCurrCode/>
									  <CCBag>' . $CCBag . '</CCBag>
									  <CCNotes/>
									  <CCSystemNotes>' . $CCSystemNotes . '</CCSystemNotes>
									  <CCOriginLocCode/>
									  <CCBagNumber/>
									  <CCCubicWeight/>
									  <CCDeadWeight/>
									  <CCDeliveryInstructions>' . $CCDeliveryInstructions . '</CCDeliveryInstructions>
									  <CCGoodsDesc>' . $CCGoodsDesc . '</CCGoodsDesc>
									  <CCSenderFax/>
									  <CCReceiverFax/>
									  <CCGoodsOriginCtryCode/>
									  <CCReasonExport/>
									  <CCShipTerms/>
									  <CCDestTaxes/>
									  <CCManNoOfShipments/>
									  <CCSecurity/>
									  <CCInsurance/>
									  <CCInsuranceCurrCode/>
									  <CCSerialNo/>
									  <CCReceiverPhone2>' . $CCReceiverPhone2 . '</CCReceiverPhone2>
									  <CCCreateJob>' . $CCCreateJob . '</CCCreateJob>
									  <CCSurcharge/>';
		}
	}

	class Request
	{
		public static $request = null;

		function __construct($requestType, $accountUsername, $password,  
							$entityId, $entityPin,
							$shipper="", $recipient="", $customerClearanceDetail="", $trackNo="")
		{

			if($requestType=="createOrder")
			{
					self::$request = array(
										'Username' => $accountUsername,
										'Password' => $password,
										// Shipment Upload Method
										'xmlStream' => '<?xml version="1.0" encoding="utf-8" ?>
											<WSGET>
											<AccessRequest>
												<FileType>19</FileType>
												<Action>upload</Action>
												<EntityID>'. $entityId . '</EntityID>
												<EntityPIN>'. $entityPin . '</EntityPIN>
											</AccessRequest>
											<CMDetail>
												<CC>'
												. $shipper . $recipient . $customerClearanceDetail .
												'
												</CC>
											</CMDetail>
											</WSGET>
											',
												'LevelConfirm' => 'summary'

											);
			}
			else if($requestType=="getTTInfo")
			{
				self::$request = array(
						'Username' => $accountUsername,
						'Password' => $password,
						'xmlStream' => '<?xml version="1.0" encoding="utf-8" ?>
								<WSGET>
								<AccessRequest>
									<FileType>2</FileType>
									<Action>Download</Action>
									<EntityID>'. $entityId . '</EntityID>
									<EntityPIN>'. $entityPin . '</EntityPIN>
								</AccessRequest>
								<ReferenceNumber>' . $trackNo . ' </ReferenceNumber>
								<ShowAltRef>Y</ShowAltRef>
								</WSGET>'
						,
						'LevelConfirm' => 'summary'
				);	
			}	
    	}

		public static function getRequest()
		{
			return self::$request;
		}

	}

?>