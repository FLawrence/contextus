package com.thecollectedmike.utils 
{
	import flash.display.DisplayObject;
	import flash.display.DisplayObjectContainer;
	import flash.display.LoaderInfo;

	/**
	 * @author moj
	 */
	public class ClipUtils 
	{
		private static var topParent : DisplayObject;

		public static function dumpClip(clip : DisplayObjectContainer, depth : uint = 0) : void
		{
			for(var i : uint = 0;i < clip.numChildren;i++) 
			{
				var child : DisplayObject = clip.getChildAt(i);
				trace(StringUtils.repeat("-", depth) + " " + child.name);
				if(child is DisplayObjectContainer) 
				{
					dumpClip(child as DisplayObjectContainer, depth + 1);
				}
			}
		}

		public static function getLoaderInfo(dispObj : DisplayObject) : LoaderInfo 
		{
			var root : DisplayObject = getRootDisplayObject(dispObj);
			if (root != null) 
			{
				return root.loaderInfo;
			}
			return null;
		}

		public static function getRootDisplayObject(dispObj : DisplayObject) : DisplayObject 
		{
			if (topParent == null) 
			{
				if (dispObj.parent != null) 
				{
					return getRootDisplayObject(dispObj.parent);
				} 
				else 
				{
					topParent = dispObj;
					return topParent;
				}
			} 
			else 
			{
				return topParent;
			}
		}
	}
}
