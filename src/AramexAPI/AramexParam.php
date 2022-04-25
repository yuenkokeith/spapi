<?php

	namespace yuenkokeith\spapi\Aramex\AramexParam;

	class ParamCreateOrder
	{
		private static $shipper = null;
		private static $recipient = null;
		private static $thridparty = null;
		private static $customerClearanceDetail = null;
		private static $orderid = null;

		public static function getShipper()
		{
			return self::$shipper;
		}

		public static function getRecipient()
		{
			return self::$recipient;
		}

		public static function getThridParty()
		{
			return self::$thridparty;
		}

		public static function getCustomerClearanceDetail()
		{
			return self::$customerClearanceDetail;
		}

		public static function addShipper($accountNumber, $senderName, $senderCompanyName, $phoneNumber, $phoneNumberExt, $cellNumber,
										$StreetLine1, $City, $StateOrProvinceCode, 
										$PostalCode, $CountryCode, $email)
		{
			self::$shipper = array(
									'Reference1' => 'Ref 111111',
									'Reference2' => 'Ref 222222',
									'AccountNumber' => $accountNumber,
									'PartyAddress' => array(
																'Line1'					=> $StreetLine1,
																'Line2' 				=> '',
																'Line3' 				=> '',
																'City'					=> $City,
																'StateOrProvinceCode'	=> $StateOrProvinceCode,
																'PostCode'				=> $PostalCode,
																'CountryCode'			=> $CountryCode
														),
														
									'Contact' => array(
															'Department'			=> '',
															'PersonName'			=> $senderName,
															'Title'					=> '',
															'CompanyName'			=> $senderCompanyName,
															'PhoneNumber1'			=> $phoneNumber,
															'PhoneNumber1Ext'		=> $phoneNumberExt,
															'PhoneNumber2'			=> '',
															'PhoneNumber2Ext'		=> '',
															'FaxNumber'				=> '',
															'CellPhone'				=> $cellNumber,
															'EmailAddress'			=> $email,
															'Type'					=> ''
													),
								);
								
		}

		public static function addRecipient($recipientName, $companyName, $phoneNumber, $phoneNumberExt, $cellNumber, $StreetLine1,
											$City, $StateOrProvinceCode, $PostalCode, $CountryCode,
											$email="")
		{
			self::$recipient = array(
									'Reference1' => 'Ref 333333',
									'Reference2' => 'Ref 444444',
									'AccountNumber' => '',
									'PartyAddress' => array(
															'Line1'					=> $StreetLine1,
															'Line2'					=> '',
															'Line3'					=> '',
															'City'					=> $City,
															'StateOrProvinceCode'	=> '',
															'PostCode'				=> '',
															'CountryCode'			=> $CountryCode
														),
									
									'Contact' => array(
														'Department'			=> '',
														'PersonName'			=> $recipientName,
														'Title'					=> '',
														'CompanyName'			=> $companyName,
														'PhoneNumber1'			=> $phoneNumber,
														'PhoneNumber1Ext'		=> $phoneNumberExt,
														'PhoneNumber2'			=> '',
														'PhoneNumber2Ext'		=> '',
														'FaxNumber'				=> '',
														'CellPhone'				=> $cellNumber,
														'EmailAddress'			=> $email,
														'Type'					=> ''
													),
								);   
		}

		public static function addThridParty($thridPartyName="", $thridPartyCompanyName="", $phoneNumber="", $phoneNumberExt="", $cellNumber="", $StreetLine1="",
											$City="", $StateOrProvinceCode="", $PostalCode="", $CountryCode="", $email="")
		{
			self::$thridparty = array(
										'Reference1' 	=> '',
										'Reference2' 	=> '',
										'AccountNumber' => '',
										'PartyAddress'	=> array(
																'Line1'					=> $StreetLine1,
																'Line2'					=> '',
																'Line3'					=> '',
																'City'					=> $City,
																'StateOrProvinceCode'	=> $StateOrProvinceCode,
																'PostCode'				=> $PostalCode,
																'CountryCode'			=> $CountryCode
															),
											'Contact' => array(
																'Department'			=> '',
																'PersonName'			=> $thridPartyName,
																'Title'					=> '',
																'CompanyName'			=> $thridPartyCompanyName,
																'PhoneNumber1'			=> $phoneNumber,
																'PhoneNumber1Ext'		=> $phoneNumberExt,
																'PhoneNumber2'			=> '',
																'PhoneNumber2Ext'		=> '',
																'FaxNumber'				=> '',
																'CellPhone'				=> $cellNumber,
																'EmailAddress'			=> $email,
																'Type'					=> ''							
															),
									);
		}

		public static function addCustomClearanceDetail($orderid, $dimensionsLength="", $dimensionsWidth="", $dimensionsHeight="", $dimensionsUnit="",
														$actualWeightValue="", $actualWeightUnit="", $productGroup="", $productType="", $paymentType="", 
														$numberOfPiece="", $descriptionOfGoods="", $goodsOriginCountry="", $cashOnDeliveryAmountValue="", $cashOnDeliveryAmountCurrencyCode="", 
														$insuranceAmountValue="", $insuranceAmountCurrencyCode="", $collectAmountValue="", $collectAmountCurrencyCode="", 
														$CashAdditionalAmountValue="", $CashAdditionalAmountCurrencyCode="",
														$CustomsValueAmountValue="", $CustomsValueAmountCurrencyCode="", 
														$ItemsPackageType="", $ItemsQuantity="", $ItemsWeightValue="", $ItemsWeightUnit="", $ItemsComments="", $ItemsReference="")
		{
			self::$orderid = $orderid;
			self::$customerClearanceDetail = array(
													'Dimensions' => array(
																			'Length' => $dimensionsLength,
																			'Width' => $dimensionsWidth,
																			'Height' => $dimensionsHeight,
																			'Unit' => $dimensionsUnit,
																		),
								
													'ActualWeight' => array(
																			'Value' => $actualWeightValue,
																			'Unit' => $actualWeightUnit
																		),
								
													'ProductGroup' 			=> $productGroup,
													'ProductType'			=> $productType,
													'PaymentType'			=> $paymentType,
													'PaymentOptions' 		=> '',
													'Services'				=> '',
													'NumberOfPieces'		=> $numberOfPiece,
													'DescriptionOfGoods' 	=> $descriptionOfGoods,
													'GoodsOriginCountry' 	=> $goodsOriginCountry,
																		
													'CashOnDeliveryAmount' 	=> array(
																				'Value'					=> $cashOnDeliveryAmountValue,
																				'CurrencyCode'			=> $cashOnDeliveryAmountCurrencyCode
																			),
													
													'InsuranceAmount' => array(
																				'Value'					=> $insuranceAmountValue,
																				'CurrencyCode'			=> $insuranceAmountCurrencyCode
																			),
														
													'CollectAmount' => array(
																				'Value'					=> $collectAmountValue,
																				'CurrencyCode'			=> $collectAmountCurrencyCode
																			),
								
													'CashAdditionalAmount'	=> array(
																				'Value'					=> $CashAdditionalAmountValue,
																				'CurrencyCode'			=> $CashAdditionalAmountCurrencyCode							
																			),
								
													'CashAdditionalAmountDescription' => '',
								
													'CustomsValueAmount' => array(
																				'Value'					=> $CustomsValueAmountValue,
																				'CurrencyCode'			=> $CustomsValueAmountCurrencyCode								
																			),
								
													'Items' => array(
																	'PackageType' 	=> $ItemsPackageType,
																	'Quantity'		=> $ItemsQuantity,
																	'Weight'		=> array(
																							'Value'		=> $ItemsWeightValue,
																							'Unit'		=> $ItemsWeightUnit,		
																							),
																	'Comments'		=> $ItemsComments,
																	'Reference'		=> $ItemsReference
																	)
													);
		}

		public static function convertCountryCode($countryCode)
		{

		}

		public static function convertCityCode($cityCode)
		{

		}

	}

	class Request
	{
		public static $request = null;

		function __construct($requestType, $orderno, $accountCountryCode="", $accountEntity="", $accountNumber="", 
							$accountPin="", $userName="", $password="", $version="", $shipper="", $recipient="", 
							$customerClearanceDetail="", $thridparty="") 
		{
			if($requestType=="createOrder")
			{
				self::$request = array(
														'Shipments' => array(
																				'Shipment' => array(
																									'Shipper' => $shipper,
																									'Consignee'	=> $recipient,
																									'ThirdParty' => $thridparty,
																									'Reference1' 				=> 'Shpt 0001',
																									'Reference2' 				=> '',
																									'Reference3' 				=> '',
																									'ForeignHAWB'				=> $orderno,
																									'TransportType'				=> 0,
																									'ShippingDateTime' 			=> time(),
																									'DueDate'					=> time(),
																									'PickupLocation'			=> 'Reception',
																									'PickupGUID'				=> '',
																									'Comments'					=> 'Shpt 0001',
																									'AccountingInstrcutions' 	=> '',
																									'OperationsInstructions'	=> '',
																					
																									'Details' => $customerClearanceDetail
																								),
																			),
										
														'ClientInfo' => array(
																				'AccountCountryCode'	=> $accountCountryCode,
																				'AccountEntity'		 	=> $accountEntity,
																				'AccountNumber'		 	=> $accountNumber,
																				'AccountPin'		 	=> $accountPin,
																				'UserName'			 	=> $userName,
																				'Password'			 	=> $password,
																				'Version'			 	=> $version
																			),

														'Transaction' => array(
																					'Reference1'			=> '001',
																					'Reference2'			=> '', 
																					'Reference3'			=> '', 
																					'Reference4'			=> '', 
																					'Reference5'			=> '',									
																				),
																				
														'LabelInfo' => array(
																				'ReportID' 				=> 9201,
																				'ReportType'			=> 'URL',
																			),
												);

			}
			else if($requestType=="getTTInfo")
			{
				self::$request = array(
										'ClientInfo' => array(
															'AccountCountryCode'	=> $accountCountryCode,
															'AccountEntity'		 	=> $accountEntity,
															'AccountNumber'		 	=> $accountNumber,
															'AccountPin'		 	=> $accountPin,
															'UserName'			 	=> $userName,
															'Password'			 	=> $password,
															'Version'			 	=> $version
														),

										'Transaction' => array(
																'Reference1'			=> '001' 
															),
										'Shipments' => array(
																$orderno
															)
									);
			}

		}

		public static function getRequest()
		{
			return self::$request;
		}
		
	}

?>