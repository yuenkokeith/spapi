<?php

	namespace yuenkokeith\spapi\KuaiDi100;
	require_once('KuaiDi100Param.php');

	class KuaiDi100 extends \yuenkokeith\spapi\SpAPI {

		private static $post_data = array();
		private static $app_key = null;
		private static $orderNo = null;
		private static $apiEndpoint = null;
		private static $result = null;
		private static $powered = null; // required by KuaiDi100 as Free account service 请勿删除变量$powered 的信息，否者本站将不再为你提供快递接口服务。
		
		function __construct()
		{
			self::$apiEndpoint = "https://poll.kuaidi100.com/poll/query.do?customer=";
			self::$powered = '查询数据由：<a href="http://kuaidi100.com" target="_blank">KuaiDi100.Com （快递100）</a> 网站提供 ';
			self::$post_data["customer"] = 'C411EB8980458D78052FDB918E61BB97';
			self::$app_key = "vJHjDWzw6582";
		}

		protected static function __doKuaiDi100ToBexpressLocalTrackingStatus($trackingStatus)
		{
			switch ($trackingStatus)
			{
				case "0":
				case "1":
					return "2"; //Bexpress 2. 貨物到達處理中心 Shipment arrived at sorting center / Hub
					break;

				case "5":
					return "3"; //Bexpress 3. 貨物派送中 Delivery in progress
					break;

				case "3":
					return "4"; //Bexpress 4. 完成派送 Shipment is received
					break;

				case "2":
				case "4":
				case "6":
					return "5"; /*Bexpress 5. 異常事件(更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送) 
								Exception (Change the scheduled delivery date/ Loss/Damage/Failed Attempt)
								*/
					break;
			}
		}

		protected static function __doKuaiDi100ToBexpressCrossTrackingStatus($trackingStatus)
		{
			switch ($trackingStatus)
			{
				case "1":
					return "2"; //Bexpress 2. 貨物到達處理中心 Shipment arrived at sorting center / Hub
					break;

				case "0":
					return "3"; //Bexpress 3. 開始發貨 Departure Status
					break;

				case "0":
					return "4"; //Bexpress 4. 清關完成 Customs Clearance
					break;

				case "5":
					return "5"; //Bexpress 5. 貨物派送中 Delivery in progress
					break;

				case "3":
					return "6"; //Bexpress 6. 完成派送 Shipment is received
					break;

				case "2":
				case "4":
				case "6":
					return "7"; /*Bexpress 7. 異常事件(清關延誤/ 更改原定派送時間/ 遺失貨物/ 貨物受損/ 未能預約派送) 
								*/
					break;
			}
		}

		private static function __setOriginalTrackingStatus($url)
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); //这个是重点,规避ssl的证书检查。
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 跳过host验证
			self::$result = curl_exec($curl);
			curl_close($curl);
			return self::$result;
		}

		private static function __setLocalTrackingStatus($url, $lang="")
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); //这个是重点,规避ssl的证书检查。
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 跳过host验证
			self::$result = curl_exec($curl);
			curl_close($curl);
			self::$result = json_decode(self::$result, true); // true= to array, false = std obj
			if($lang=="en")
			{
				if(self::$result['returnCode']==500) { // 查询无结果，请隔段时间再查
					self::$result['status'] = 500;
					return self::$result;
				} 
				else { // 查询成功
					self::$result['state'] = self::__doKuaiDi100ToBexpressLocalTrackingStatus(self::$result['state']);
					return self::$result;
				}
			}
			else
			{
				if(self::$result['status']==0 || self::$result['status']==2) { // 物流单暂无结果 || 接口出现异常
					self::$result['status'] = 500;
					return self::$result;
				} 
				else { // 查询成功
					self::$result['state'] = self::__doKuaiDi100ToBexpressLocalTrackingStatus(self::$result['state']);
					return self::$result;
				}
			}
		}

		private static function __setCrossTrackingStatus($url, $lang="")
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); //这个是重点,规避ssl的证书检查。
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 跳过host验证
			self::$result = curl_exec($curl);
			curl_close($curl);
			self::$result = json_decode(self::$result, true); // true= to array, false = std obj
			if($lang=="en")
			{
				if(self::$result['returnCode']==500) { // 查询无结果，请隔段时间再查
					self::$result['status'] = 500;
					return self::$result;
				} 
				else { // 查询成功
					self::$result['state'] = self::__doKuaiDi100ToBexpressCrossTrackingStatus(self::$result['state']);
					return self::$result;
				}
			}
			else
			{
				if(self::$result['status']==0 || self::$result['status']==2) { // 物流单暂无结果 || 接口出现异常
					return self::$result;
				} 
				else { // 查询成功
					self::$result['state'] = self::__doKuaiDi100ToBexpressCrossTrackingStatus(self::$result['state']);
					return self::$result;
				}
			}
		}

		private static function __autoFindCarrierName($orderNo)
		{
			$url = "http://www.kuaidi100.com/autonumber/auto?num=" . $orderNo . "&key=" . self::$app_key;
			$curl = curl_init();
			curl_setopt ($curl, CURLOPT_URL, $url);
			curl_setopt ($curl, CURLOPT_HEADER,0);
			curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
			curl_setopt ($curl, CURLOPT_TIMEOUT,5);
			self::$result = curl_exec($curl);
			curl_close ($curl);
			self::$result = json_decode(self::$result, true); // true= to array, false = std obj
			return self::$result;
		}

		private static function __encryptyApiEndPoint($carrierNameArr, $orderNo, $isEng='')
		{
			self::$post_data["param"] = '{"com":"' . $carrierNameArr[0]["comCode"] . $isEng . '","num":"' . $orderNo . '","from":"","to":""}';
			self::$post_data["sign"] = md5(self::$post_data["param"].self::$app_key.self::$post_data["customer"]);
			self::$post_data["sign"] = strtoupper(self::$post_data["sign"]);
			$url= self::$apiEndpoint . self::$post_data["customer"] . '&param={"com":"' . $carrierNameArr[0]["comCode"] . $isEng . '","num":"' . $orderNo . '","from":"","to":""}&sign=' . self::$post_data["sign"];
			return $url;
		}

		public static function getOriginalTrackingStatus($orderNo, $lang=0) // $lang | 0 = simplified Chinese | 1 = English
		{
			$carrierNameArr = self::__autoFindCarrierName($orderNo);
			self::$post_data["param"] = '{"com":"' . $carrierNameArr[0]["comCode"] . '","num":"' . $orderNo . '","from":"","to":""}';
			self::$post_data["sign"] = md5(self::$post_data["param"].self::$app_key.self::$post_data["customer"]);
    		self::$post_data["sign"] = strtoupper(self::$post_data["sign"]);
			$url= self::$apiEndpoint . self::$post_data["customer"] . '&param={"com":"' . $carrierNameArr[0]["comCode"] . '","num":"' . $orderNo . '","from":"","to":""}&sign=' . self::$post_data["sign"];
			return self::__setOriginalTrackingStatus($url);
		}
		
		// @overriding \yuenkokeith\spapi\SpAPI - Mapping KuaiDi100 State code to Bexpress Local Status
		public static function getLocalTrackingStatus($orderNo, $lang=0) // $lang | 0 = simplified Chinese | 1 = English *** if available ***
		{
			$carrierNameArr = self::__autoFindCarrierName($orderNo);
			if($carrierNameArr==null) // 物流单暂无结果 
			{
				self::$result = array ('status' => 500);
				return self::$result;
			}
			else // Find Related Carrier
			{
				if($lang==1) // 1 = English *** if available ***
				{
					$carriers = new \yuenkokeith\spapi\KuaiDi100\KuaiDi100Param\Carriers();
					$isSupportEnglish = $carriers->checkIfCarrierSupportEnglish($carrierNameArr[0]["comCode"] . 'en');
					if($isSupportEnglish)
					{
						return self::__setLocalTrackingStatus(self::__encryptyApiEndPoint($carrierNameArr, $orderNo, "en"), "en");
					}
					else // NOT support English : default to 0 = simplified Chinese
					{
						return self::__setLocalTrackingStatus(self::__encryptyApiEndPoint($carrierNameArr, $orderNo));
					}
				}
				else // default 0 = simplified Chinese
				{
					return self::__setLocalTrackingStatus(self::__encryptyApiEndPoint($carrierNameArr, $orderNo));
				}
			}
		}

		// @overriding \yuenkokeith\spapi\SpAPI - Mapping KuaiDi100 State code to Bexpress Cross Status
		public static function getCrossTrackingStatus($orderNo, $lang=0) // $lang | 0 = simplified Chinese | 1 = English *** if available ***
		{
			$carrierNameArr = self::__autoFindCarrierName($orderNo);
			if($carrierNameArr==null) // 物流单暂无结果 
			{
				self::$result = array ('status' => 500);
				return self::$result;
			}
			else // Find Related Carrier
			{
				if($lang==1) // 1 = English *** if available ***
				{
					$carriers = new \yuenkokeith\spapi\KuaiDi100\KuaiDi100Param\Carriers();
					$isSupportEnglish = $carriers->checkIfCarrierSupportEnglish($carrierNameArr[0]["comCode"] . 'en');
					if($isSupportEnglish)
					{
						return self::__setCrossTrackingStatus(self::__encryptyApiEndPoint($carrierNameArr, $orderNo, "en"), "en");
					}
					else // NOT support English : default to 0 = simplified Chinese
					{
						return self::__setCrossTrackingStatus(self::__encryptyApiEndPoint($carrierNameArr, $orderNo));
					}
				}
				else // default 0 = simplified Chinese
				{
					return self::__setCrossTrackingStatus(self::__encryptyApiEndPoint($carrierNameArr, $orderNo));
				}
			}
		}

		public static function autoFindCarrierName($orderNo)
		{
			$url = "http://www.kuaidi100.com/autonumber/auto?num=" . $orderNo . "&key=" . self::$app_key;
			$curl = curl_init();
			curl_setopt ($curl, CURLOPT_URL, $url);
			curl_setopt ($curl, CURLOPT_HEADER,0);
			curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
			curl_setopt ($curl, CURLOPT_TIMEOUT,5);
			self::$result = curl_exec($curl);
			curl_close ($curl);
			self::$result = json_decode(self::$result, true); // true= to array, false = std obj
			return self::$result[0];
		}
		
	}
	
?>

