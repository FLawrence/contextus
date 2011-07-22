package net.contextus.trains.view 
{
	import net.contextus.trains.model.Person;

	import flash.display.DisplayObjectContainer;
	import flash.display.MovieClip;

	/**
	 * @author moj
	 */
	public class TrainView 
	{
		private var _guides : Array;
		private var _personViews : Array;
		private var _personClips : Array;

		public function TrainView(parent : gfxMain) 
		{
			_guides = [];
			_personViews = [];
			_personClips = [];
			
			for(var i : uint = 1;i < 36; i++)
			{
				_guides.push(parent.train["person" + i]);
			}
		}

		public function showLinks(person : Person) : void 
		{
			// Alpha out everyone first

			for(var i : uint = 0;i < _personClips.length; i++) 
			{
				(_personClips[i] as MovieClip).gotoAndStop("deselected");
				(_personClips[i] as MovieClip).alpha = 1;
			}
			
			for(var j : uint = 0;j < person.links.length; j++) 
			{
				var link : Person = person.links[j];
				if(link.car == person.car) 
				{
					(_personClips[link.pos] as MovieClip).gotoAndStop("selected");
					(_personClips[link.pos] as MovieClip).alpha = 0.7;
				}
			}
			
			var selected : MovieClip = _personClips[person.pos] as MovieClip;
			selected.alpha = 1;
			selected.gotoAndStop("selected");
		}

		public function hideLinks() : void
		{
			// Bring everyone back

			for(var i : uint = 0;i < _personClips.length; i++) 
			{
				(_personClips[i] as MovieClip).gotoAndStop("deselected");
				(_personClips[i] as MovieClip).alpha = 1;
			}
		}

		public function applyFilter(filter : uint) : void
		{
			for(var i : uint = 0;i < _personViews.length; i++) 
			{
				(_personViews[i] as PersonView).showIcon(filter);
			}
		}

		public function hideFilter() : void
		{
			for(var i : uint = 0;i < _personViews.length; i++) 
			{
				(_personViews[i] as PersonView).hideIcon();
			}
		}

		public function addPerson(personView : PersonView) : void 
		{
			(_guides[personView.person.pos] as DisplayObjectContainer).removeChildAt(0);
			(_guides[personView.person.pos] as DisplayObjectContainer).addChild(personView.view);
			_personViews[personView.person.pos] = personView;	
			_personClips[personView.person.pos] = personView.view;		
		}

		public function get personViews() : Array
		{
			return _personViews;
		}

		public function get personClips() : Array
		{
			return _personClips;
		}
	}
}
