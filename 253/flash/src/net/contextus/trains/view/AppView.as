package net.contextus.trains.view 
{
	import net.contextus.trains.model.Person;

	import flash.display.MovieClip;
	import flash.display.Sprite;
	import flash.events.MouseEvent;
	import flash.text.TextField;

	/**
	 * @author moj
	 */
	public class AppView 
	{
		private var _parent : Sprite;
		private var _trainView : TrainView;
		private var _view : gfxMain;

		private var _infoNames : Array = ["Gender"];
		private var _infoClipNames : Array = ["btnInfo1"];
		private var _infoClips : Array = [];

		
		public function AppView(parent : Sprite) 
		{
			this._parent = parent;
			
			this._view = new gfxMain();
			this._view.stop();
			this._view.texture.mouseChildren = false;
			this._view.texture.mouseEnabled = false;
			
			this._view.storyClip.visible = true;
		
			this._view.infoButtonClip.visible = true;

			for(var i : uint = 0;i < _infoClipNames.length; i++) 
			{
				var clip : MovieClip = this._view.infoButtonClip[_infoClipNames[i]];
				_infoClips.push(clip);
				(clip["infoTxt"] as TextField).text = _infoNames[i];
			}
			
			_view.storyClip.alpha = 0;
			this._trainView = new TrainView(this._view);
		}

		
		public function showStoryClip(person : Person) : void
		{
			var nameText : TextField = (_view.storyClip.nameTxt as TextField);
			var appearanceText : TextField = (_view.storyClip.appearanceTxt as TextField);
			var informationText : TextField = (_view.storyClip.informationTxt as TextField);
			var thinkingText : TextField = (_view.storyClip.doingThinkingTxt as TextField);
			var carText : TextField = (_view.storyClip.carTxt as TextField);
			var numberText : TextField = (_view.storyClip.numberTxt as TextField);
			
			nameText.htmlText = person.name;
			appearanceText.htmlText = person.appearance;
			informationText.htmlText = person.inside;
			thinkingText.htmlText = person.thinking;
			carText.htmlText = person.car + "";
			numberText.htmlText = "" + ((person.pos+1) < 10 ? "0" : "") + (person.pos+1);
			
					
			_view.storyClip.alpha = 1;
		}

		public function showTrain(passengers : Array) : void 
		{
			for(var i : uint = 0;i < passengers.length; i++) 
			{
				var person : Person = passengers[i];
				var personView : PersonView = new PersonView(person);
				this._trainView.addPerson(personView);
			}
		}

		public function get view() : gfxMain
		{
			return _view;
		}

		public function get trainView() : TrainView
		{
			return _trainView;
		}

		public function get infoClips() : Array
		{
			return _infoClips;
		}
	}
}
