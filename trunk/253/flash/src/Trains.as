package  
{
	import net.contextus.trains.controller.AppController;
	import net.contextus.trains.view.AppView;

	import flash.display.Sprite;

	/**
	 * @author moj
	 */
	 [SWF(width="1024", height="768", backgroundColor="#ffffff",frameRate="30")]
	public class Trains extends Sprite 
	{
		private var _view : AppView;
		private var _controller : AppController;

		public function Trains()
		{
			this._view = new AppView(this);
			this._controller = new AppController(this._view);
			addChild(this._view.view);
		}
	}
}
