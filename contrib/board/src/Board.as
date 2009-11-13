package
{
	import flash.display.Sprite;

	import net.contextus.board.model.Game;
	import net.contextus.board.view.BoardView;
	import net.contextus.board.controller.BoardController;

	public class Board extends Sprite
	{
		private var view : BoardView;
		private var model : Game;
		private var controller : BoardController;

		public function Board()
		{
			this.view = new BoardView();
			this.model = new Game();
			this.controller = new BoardController(view, model);
		}
	}
}
