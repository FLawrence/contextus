package net.contextus.trains.view 
{
	import net.contextus.trains.model.Person;

	import flash.display.DisplayObject;
	import flash.display.MovieClip;

	/**
	 * @author moj
	 */
	public class PersonView
	{
		private var _view : DisplayObject;
		private var _person : Person;

		public static const ICON_GENDER : uint = 0;
		private var _iconHolder : MovieClip;
		private var _image : MovieClip;
		public function PersonView(person : Person) 
		{
			this._person = person;
			this._view = (person.side == Person.SIDE_LEFT ? new gfxPersonLeft() : new gfxPersonRight());
			this._iconHolder = this._view["infoIconHolder"];
			this._iconHolder.visible = false;
		}
		
		public function hideIcon() : void
		{
			if(_image) {
				_iconHolder.removeChild(_image);	
				_image = null;
			}
			_iconHolder.visible = false;
		}

		
		public function showIcon(type : uint) : void
		{
			switch(type) 
			{
				case ICON_GENDER:
					var image : MovieClip = new gfxInfoIconGender();
					if(person.gender == Person.GENDER_MALE) 
					{
						image.gotoAndStop(1);
					}
					else
					{
						image.gotoAndStop(2);
					}
					switchImage(image);
			}
		}
		
		private function switchImage(image : MovieClip) : void
		{
			if(_image) {
				_iconHolder.removeChild(_image);	
			}
			_iconHolder.addChild(image);
			_image = image;
			_iconHolder.visible = true;
			
		}

		public function get view() : DisplayObject
		{
			return _view;
		}

		public function get person() : Person
		{
			return _person;
		}
	}
}
