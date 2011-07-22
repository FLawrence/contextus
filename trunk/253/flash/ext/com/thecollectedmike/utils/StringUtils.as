package com.thecollectedmike.utils 
{

	/**
	 * @author moj
	 */
	public class StringUtils 
	{
		public static function repeat(string : String, n : uint) : String {
			var out : String = "";
			for(var i:uint=0; i<n; i++) {
				out += string;
			}
			return out;
		}
	}
}
