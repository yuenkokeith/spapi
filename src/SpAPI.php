<?php

	namespace yuenkokeith\spapi;
	
	class ApiLoader 
	{	
		public static function getAPI($sp_name)
		{
			require_once($sp_name. 'API\\' . $sp_name . 'API.php');
			$class = 'yuenkokeith\spapi\\' . $sp_name;
			return new $class;
		}
	}

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

