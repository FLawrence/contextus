package net.contextus.trains.controller 
{
	import net.contextus.trains.model.Person;
	import net.contextus.trains.model.Train;
	import net.contextus.trains.view.AppView;
	import net.contextus.trains.view.PersonView;

	import com.adobe.serialization.json.JSON;

	import flash.display.MovieClip;
	import flash.events.MouseEvent;

	/**
	 * @author moj
	 */
	public class AppController 
	{

		[Embed(source="../../../../../config.json", mimeType="application/octet-stream")]
		private var Config : Class;

		private var _view : AppView;
		private var _train : Train;
		private var _car : uint;

		private var _infoFilters : Array = [PersonView.ICON_GENDER];
		private var _filterShowing : Boolean;

		public function AppController(view : AppView) 
		{
			this._view = view;
			
			var config : String = new Config();
			var people : Object = JSON.decode(config, false);
			
			for(var i : uint = 0;i < view.infoClips.length; i++) 
			{
				var clip : MovieClip = view.infoClips[i];
				clip.addEventListener(MouseEvent.CLICK, handleInfoClipClicked);
			}
			this._train = new Train(people);
			_filterShowing = false;
			showCar(1);
		}

		private function handleInfoClipClicked(event : MouseEvent) : void
		{
			if(_filterShowing) 
			{
				_view.trainView.hideFilter();
			}
			else
			{
				var index : uint = _view.infoClips.indexOf(event.target);
				_view.trainView.applyFilter(_infoFilters[index]);
			}
			
			_filterShowing = !_filterShowing;
		}

		private function showCar(car : uint) : void 
		{
			this._car = car;
			_view.showTrain(this._train.cars[car]);
			
			var personViews : Array = _view.trainView.personViews;
			for(var i : uint = 0;i < personViews.length; i++) 
			{
				var personView : PersonView = personViews[i];
				personView.view.addEventListener(MouseEvent.CLICK, handlePersonClick);
				personView.view.addEventListener(MouseEvent.ROLL_OVER, handlePersonOver);
				personView.view.addEventListener(MouseEvent.ROLL_OUT, handlePersonOut);
			}
		}
		
		private function handlePersonOut(event : MouseEvent) : void
		{
		//	_view.trainView.hideLinks();
		}

		private function handlePersonOver(event : MouseEvent) : void
		{
			var personid : uint = _view.trainView.personClips.indexOf(event.currentTarget);
			var person : Person = _train.cars[_car][personid];
		}

		private function handlePersonClick(event : MouseEvent) : void
		{
			var personid : uint = _view.trainView.personClips.indexOf(event.currentTarget);
			var person : Person = _train.cars[_car][personid];
			
			this._view.showStoryClip(person);
			_view.trainView.showLinks(person);
		}

		public function init() : void  
		{
		}
	}
}
