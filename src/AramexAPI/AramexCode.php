<?php

	namespace yuenkokeith\spapi\Aramex\AramexCode;

	class ConvertCode
	{
		private static $countryCode = null;
		private static $cityCode = null;

		public static function getShipper()
		{
			return self::$shipper;
		}

		public static function convertCountryCode($countryCode)
		{
			self::$countryCode = "Country Testing";
			return self::$countryCode;
		}

		public static function convertCityCode($cityCode)
		{
			return self::$cityCode = "City Testing";
		}

	}

?>