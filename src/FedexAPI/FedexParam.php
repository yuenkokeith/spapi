<?php

	namespace yuenkokeith\spapi\Fedex\FedexParam;

	class ParamCreateOrder
	{
		private static $shipper = null;
		private static $recipient = null;
		private static $shippingChargesPayment = null;
		private static $labelSpecification = null;
		private static $specialServices = null;
		private static $customerClearanceDetail = null;
		private static $packageLineItem = null;

		public static function getShipper()
		{
			return self::$shipper;
		}

		public static function getRecipient()
		{
			return self::$recipient;
		}

		public static function getShippingChargesPayment()
		{
			return self::$shippingChargesPayment;
		}

		public static function getLabelSpecification()
		{
			return self::$labelSpecification;
		}

		public static function getSpecialServices()
		{
			return self::$specialServices;
		}

		public static function getCustomerClearanceDetail()
		{
			return self::$customerClearanceDetail;
		}

		public static function getPackageLineItem()
		{
			return self::$packageLineItem;
		}

		public static function addShipper($senderName, $senderCompanyName, $phoneNumber, $phoneExtension, 
										$StreetLine1, $City, $StateOrProvinceCode, 
										$PostalCode, $CountryCode)
		{
			self::$shipper = array(
				'Contact' => array(
					'PersonName' => $senderName,
					'CompanyName' => $senderCompanyName,
					'PhoneNumber' => $phoneNumber,
					'PhoneExtension' => $phoneExtension,
				),
				'Address' => array(
					'StreetLines' => array($StreetLine1),
					'City' => $City,
					'StateOrProvinceCode' => $StateOrProvinceCode,
					'PostalCode' => $PostalCode,
					'CountryCode' => $CountryCode
				)
			);
		}

		public static function addRecipient($recipientName, $companyName, $phoneNumber, $phoneExtension, $StreetLine1,
											$City, $StateOrProvinceCode, $PostalCode, $CountryCode,
											$residential=false)
		{
			self::$recipient = array(
				'Contact' => array(
					'PersonName' => $recipientName,
					'CompanyName' => $companyName,
					'PhoneNumber' => $phoneNumber,
					'PhoneExtension' => $phoneExtension,
				),
				'Address' => array(
					'StreetLines' => array($StreetLine1),
					'City' => $City,
					'StateOrProvinceCode' => $StateOrProvinceCode,
					'PostalCode' => $PostalCode,
					'CountryCode' => $CountryCode,
					'Residential' => $residential
				)
			);	                                    
		}

		public static function addShippingChargesPayment($paymentType='SENDER', $billAccount, $contact=null, $countryCode)
		{
			self::$shippingChargesPayment = array(
								'PaymentType' => $paymentType,
								'Payor' => array(
								'ResponsibleParty' => array(
															'AccountNumber' => $billAccount,
															'Contact' => $contact,
															'Address' => array('CountryCode' => $countryCode)
															)
													)
								);
		}

		public static function addLabelSpecification($labelFormatType='COMMON2D', $imageType='PDF', $labelStockType='PAPER_7X4.75')
		{
			self::$labelSpecification = array(
				'LabelFormatType' => $labelFormatType, // valid values COMMON2D, LABEL_DATA_ONLY
				'ImageType' => $imageType,  // valid values DPL, EPL2, PDF, ZPLII and PNG
				'LabelStockType' => $labelStockType
			);
		}

		public static function addSpecialServices($specialServiceTypes, $currency, $amount, $collectionType)
		{
			self::$specialServices = array(
									'SpecialServiceTypes' => array($specialServiceTypes),
									'CodDetail' => array(
														'CodCollectionAmount' => array(
															'Currency' => $currency, 
															'Amount' => $amount
														),
										'CollectionType' => $collectionType // ANY, GUARANTEED_FUNDS
									)
			);
		}

		public static function addCustomClearanceDetail($sender, $accountNumber, $contact, $countryCode, $documentContent, $customsValueCurrency, 
														$customsValueAmount, $numberOfPieces, $description, $countryOfManufacture, $units, $value,
														$quantity, $quantityUnits, $unitPriceCurrency, $unitPriceAmount, $commoditiesCustomsValueCurrency, 
														$commoditiesCustomsValueAmount, $B13AFilingOption)
		{
			self::$customerClearanceDetail = array(
													'DutiesPayment' => array(
																			'PaymentType' => $sender, // valid values RECIPIENT, SENDER and THIRD_PARTY
																			'Payor' => array(
																								'ResponsibleParty' => array(
																															'AccountNumber' => $accountNumber,
																															'Contact' => $contact,
																															'Address' => array(
																																					'CountryCode' => $countryCode
																																				)
																															)
																							)
																			),
													'DocumentContent' => $documentContent,                                                              
													'CustomsValue' => array(
																				'Currency' => $customsValueCurrency,
																				'Amount' => $customsValueAmount
																			),
													'Commodities' => array(
																			'0' => array(
																							'NumberOfPieces' => $numberOfPieces,
																							'Description' => $description,
																							'CountryOfManufacture' => $countryOfManufacture,
																							'Weight' => array(
																												'Units' => $units, 
																												'Value' => $value
																											),
																							'Quantity' => $quantity,
																							'QuantityUnits' => $quantityUnits,
																							'UnitPrice' => array(
																													'Currency' => $unitPriceCurrency, 
																													'Amount' => $unitPriceAmount
																												),
																							'CustomsValue' => array(
																													'Currency' => $commoditiesCustomsValueCurrency, 
																													'Amount' => $commoditiesCustomsValueAmount
																												)
																						)
																		),
													'ExportDetail' => array(
																				'B13AFilingOption' => $B13AFilingOption
																			)
												);
		}

		public static function addPackageLineItem1($sequenceNumber, $groupPackageCountm, $weightValue, $weightUnit, 
									$dimensionsLength, $dimensionsWidth, $dimensionsHeight, $dimensionsUnits)
		{
			self::$packageLineItem = array(
									'SequenceNumber'=>$sequenceNumber,
									'GroupPackageCount'=>$groupPackageCountm,
									'Weight' => array(
														'Value' => $weightValue,
														'Units' => $weightUnit
													),
									'Dimensions' => array(
															'Length' => $dimensionsLength,
															'Width' => $dimensionsWidth,
															'Height' => $dimensionsHeight,
															'Units' => $dimensionsUnits
														)
							);
			
		}
	}

	class ParamGetTTInfo 
	{
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

		function __construct($requestType, $orderno, $key, $password, $shipaccount, $meter,
								$dropoffType, $serviceType, $packagingType, $shipper, $recipient, 
								$shippingChargesPayment, $customerClearanceDetail, 
								$labelSpecification, $packageLineItem) 
		{
			self::$request['WebAuthenticationDetail'] = array(
				'ParentCredential' => array(
					'Key' => $key, 
					'Password' => $password
				),
				'UserCredential' => array(
					'Key' => $key, 
					'Password' => $password
				)
			);

			self::$request['ClientDetail'] = array(
				'AccountNumber' => $shipaccount, 
				'MeterNumber' => $meter
			);

			self::$request['TransactionDetail'] = array('CustomerTransactionId' => '*** Express International Shipping Request using PHP中文 ***');

			if($requestType=="createOrder")
			{
				self::$request['Version'] = array(
					'ServiceId' => 'ship', 
					'Major' => '19', 
					'Intermediate' => '0', 
					'Minor' => '0'
				);

				self::$request['RequestedShipment'] = array(
						'ShipTimestamp' => date('c'),
						'DropoffType' => $dropoffType, // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
						'ServiceType' => $serviceType, // valid values STANDARD_OVERNIGHT, PRIORITY_OVERNIGHT, FEDEX_GROUND, ...
						'PackagingType' => $packagingType, // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
						'Shipper' => $shipper,
						'Recipient' => $recipient,
						'ShippingChargesPayment' => $shippingChargesPayment,
						'CustomsClearanceDetail' => $customerClearanceDetail,
						'LabelSpecification' => $labelSpecification,
						'CustomerSpecifiedDetail' => array(
							'MaskedData'=> 'SHIPPER_ACCOUNT_NUMBER'
						), 
						'PackageCount' => 1,
							'RequestedPackageLineItems' => array(
							'0' => $packageLineItem
						),
						'CustomerReferences' => array(
							'0' => array(
								'CustomerReferenceType' => 'CUSTOMER_REFERENCE', 
								'Value' => 'TC007_07_PT1_ST01_PK01_SNDUS_RCPCA_POS'
							)
						)
					);
			}
			else if($requestType=="getTTInfo")
			{
				self::$request['Version'] = array(
					'ServiceId' => 'trck', 
					'Major' => '12', 
					'Intermediate' => '0', 
					'Minor' => '0'
				);

				self::$request['SelectionDetails'] = array(
					'PackageIdentifier' => array(
						'Type' => 'TRACKING_NUMBER_OR_DOORTAG',
						'Value' => $orderno // Replace 'XXX' with a valid tracking identifier
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