<?php

	namespace yuenkokeith\spapi\KuaiDi100\KuaiDi100Param;

	class Carriers 
	{
		private static $carrierEngList = null;

		private static function init()
		{
			self::$carrierEngList = array(
								'auspost','aramex','bpost','bpostinter','canpost','dhlen',
								'emsen','emsinten','fedex','fedexus','lianbangkuaidien','ruidianyouzheng','shunfengen','tnten',
								'upsen'
								);
		}

		public static function getCarrierList()
		{
			self::init();
			return self::$carrierEngList;	
		}

		public static function checkIfCarrierSupportEnglish($carrierName)
		{
			self::init();
			$isfound =false;
			foreach(self::$carrierEngList as $data)
			{
				if($data==$carrierName)
				{
					$isfound = true;
					return $isfound;
					break;
				}
			}
			return $isfound;	
		}
	}

?>