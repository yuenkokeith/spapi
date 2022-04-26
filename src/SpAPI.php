<?php

	namespace yuenkokeith\spapi;
	
	class SpAPI 
	{
		private static $result;
		private static $localTrackingStatus = null;
		private static $crossTrackingStatus = null;

		function __construct()
		{

		}
		
		private static function __setCrossTrackingStatus($trackingStatus)
		{
			self::$crossTrackingStatus = $trackingStatus;
		}

		public static function setCrossTrackingStatus($trackingStatus)
		{
			self::__setCrossTrackingStatus($trackingStatus);
		}
	
	}
	
?>

