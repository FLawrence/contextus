package com.thecollectedmike.utils 
{

	/**
	 * @author moj
	 */
	public class ArrayUtils 
	{
		public static function shuffle(array : Array) : Array
		{
			var newArray : Array = array.concat();
			newArray.sort(function () : int
			{
				return Math.round(Math.random());
			});
			return newArray;
		}
	}
}
