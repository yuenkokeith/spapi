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
	
?>

