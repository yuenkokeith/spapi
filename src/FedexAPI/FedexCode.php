<?php

	namespace yuenkokeith\spapi\Fedex\FedexCode;

	class ConvertCode
	{
		private static $countryCode = null;
		private static $cityCode = null;
		private static $provinceCode = null;

		public static function convertCountryCode($countryCode)
		{
			self::$countryCode = "Country Testing";
			return self::$countryCode;
		}

		public static function convertProvince($provinceCode, $countryCode)
		{
			if($countryCode=="CA")
			{
				switch ($provinceCode)
				{
					case 'Alberta':
						self::$provinceCode = 'AB';
					break;

					case 'British Columbia':
						self::$provinceCode = 'BC';
					break;

					case 'Manitoba':
						self::$provinceCode = 'MB';
					break;

					case 'New Brunswick':
						self::$provinceCode = 'NB';
					break;

					case 'Newfoundland':
						self::$provinceCode = 'NL';
					break;

					case 'Northwest Territories':
						self::$provinceCode = 'NT';
					break;

					case 'Nova Scotia':
						self::$provinceCode = 'NS';
					break;

					case 'Nunavut':
						self::$provinceCode = 'NU';
					break;

					case 'Ontario':
						self::$provinceCode = 'ON';
					break;

					case 'Prince Edward Island':
						self::$provinceCode = 'PE';
					break;

					case 'Quebec':
						self::$provinceCode = 'QC';
					break;

					case 'Saskatchewan':
						self::$provinceCode = 'SK';
					break;
					
					case 'Yukon':
						self::$provinceCode = 'YT';
					break;
				}
			}
			else if($countryCode=="US")
			{
				switch ($provinceCode)
				{
					case 'Alabama':
						self::$provinceCode = 'AL';
					break;

					case 'Alaska':
						self::$provinceCode = 'AK';
					break;

					case 'Arizona':
						self::$provinceCode = 'AZ';
					break;

					case 'Arkansas':
						self::$provinceCode = 'AR';
					break;

					case 'California':
						self::$provinceCode = 'CA';
					break;

					case 'Colorado':
						self::$provinceCode = 'CO';
					break;

					case 'Connecticut':
						self::$provinceCode = 'CT';
					break;

					case 'Delaware':
						self::$provinceCode = 'DE';
					break;

					case 'District of Columbia':
						self::$provinceCode = 'DC';
					break;

					case 'Florida':
						self::$provinceCode = 'FL';
					break;

					case 'Georgia':
						self::$provinceCode = 'GA';
					break;

					case 'Hawaii':
						self::$provinceCode = 'HI';
					break;

					case 'Idaho':
						self::$provinceCode = 'ID';
					break;

					case 'Illinois':
						self::$provinceCode = 'IL';
					break;

					case 'Indiana':
						self::$provinceCode = 'IN';
					break;

					case 'Iowa':
						self::$provinceCode = 'IA';
					break;

					case 'Kansas':
						self::$provinceCode = 'KS';
					break;

					case 'Kentucky':
						self::$provinceCode = 'KY';
					break;

					case 'Louisiana':
						self::$provinceCode = 'LA';
					break;

					case 'Maine':
						self::$provinceCode = 'ME';
					break;

					case 'Maryland':
						self::$provinceCode = 'MD';
					break;

					case 'Massachusetts':
						self::$provinceCode = 'MA';
					break;

					case 'Michigan':
						self::$provinceCode = 'MI';
					break;

					case 'Minnesota':
						self::$provinceCode = 'MN';
					break;

					case 'Mississippi':
						self::$provinceCode = 'MS';
					break;

					case 'Missouri':
						self::$provinceCode = 'MO';
					break;

					case 'Montana':
						self::$provinceCode = 'MT';
					break;

					case 'Nebraska':
						self::$provinceCode = 'NE';
					break;

					case 'Nevada':
						self::$provinceCode = 'NV';
					break;

					case 'New Hampshire':
						self::$provinceCode = 'NH';
					break;

					case 'New Jersey':
						self::$provinceCode = 'NJ';
					break;

					case 'New Mexico':
						self::$provinceCode = 'NM';
					break;

					case 'New York':
						self::$provinceCode = 'NY';
					break;

					case 'North Carolina':
						self::$provinceCode = 'NC';
					break;

					case 'North Dakota':
						self::$provinceCode = 'ND';
					break;

					case 'Ohio':
						self::$provinceCode = 'OH';
					break;

					case 'Oklahoma':
						self::$provinceCode = 'OK';
					break;

					case 'Oregon':
						self::$provinceCode = 'OR';
					break;

					case 'Pennsylvania':
						self::$provinceCode = 'PA';
					break;

					case 'Rhode Island':
						self::$provinceCode = 'RI';
					break;

					case 'South Carolina':
						self::$provinceCode = 'SC';
					break;

					case 'South Dakota':
						self::$provinceCode = 'SD';
					break;

					case 'Tennessee':
						self::$provinceCode = 'TN';
					break;

					case 'Texas':
						self::$provinceCode = 'TX';
					break;

					case 'Utah':
						self::$provinceCode = 'UT';
					break;

					case 'Vermont':
						self::$provinceCode = 'VT';
					break;

					case 'Virginia':
						self::$provinceCode = 'VA';
					break;

					case 'Washington State':
						self::$provinceCode = 'WA';
					break;

					case 'West Virginia':
						self::$provinceCode = 'WV';
					break;

					case 'Wisconsin':
						self::$provinceCode = 'WI';
					break;

					case 'Wyoming':
						self::$provinceCode = 'WY';
					break;

					case 'Puerto Rico':
						self::$provinceCode = 'PR';
					break;
				}
			}
			else
			{
				self::$provinceCode = '';
			}
			return self::$provinceCode;
		}

		public static function convertCityCode($cityCode)
		{
			return self::$cityCode = "City Testing";
		}

	}

?>