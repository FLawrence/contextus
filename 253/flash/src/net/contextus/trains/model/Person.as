package net.contextus.trains.model 
{

	/**
	 * @author moj
	 */
	public class Person 
	{
		public static const GENDER_MALE: uint = 0;
		public static const GENDER_FEMALE : uint = 1;
		
		public static const SIDE_LEFT : uint = 0;
		public static const SIDE_RIGHT : uint = 1;
		
		private var _number : uint;
		private var _pos : uint;
		private var _car : uint;
		
		private var _gender : uint;
		private var _side : uint;
		private var _nationality : String;
		private var _mortality : Boolean;
		private var _inside : String;
		private var _appearance : String;
		private var _thinking : String;
		
		
		private var _age : uint;
		private var _name : String;
		private var _title : String;
		private var _job : String;
		
		private var _links : Array;
		
		public function Person() {
			_links = [];
		}
		
		public function get gender() : uint
		{
			return _gender;
		}
		
		public function set gender(gender : uint) : void
		{
			_gender = gender;
		}
		
		public function get nationality() : String
		{
			return _nationality;
		}
		
		public function set nationality(nationality : String) : void
		{
			_nationality = nationality;
		}
		
		public function get mortality() : Boolean
		{
			return _mortality;
		}
		
		public function set mortality(mortality : Boolean) : void
		{
			_mortality = mortality;
		}
		
		public function get age() : uint
		{
			return _age;
		}
		
		public function set age(age : uint) : void
		{
			_age = age;
		}
		
		public function get name() : String
		{
			return _name;
		}
		
		public function set name(name : String) : void
		{
			_name = name;
		}
		
		public function get title() : String
		{
			return _title;
		}
		
		public function set title(title : String) : void
		{
			_title = title;
		}
		
		public function get job() : String
		{
			return _job;
		}
		
		public function set job(job : String) : void
		{
			_job = job;
		}
		
		public function get inside() : String
		{
			return _inside;
		}
		
		public function set inside(inside : String) : void
		{
			_inside = inside;
		}
		
		public function get appearance() : String
		{
			return _appearance;
		}
		
		public function set appearance(appearance : String) : void
		{
			_appearance = appearance;
		}
		
		public function get thinking() : String
		{
			return _thinking;
		}
		
		public function set thinking(thinking : String) : void
		{
			_thinking = thinking;
		}
		
		public function toString() : String
		{
			return "[number="+_number+" "+"name="+name+"]";
		}
		
		public function get side() : uint
		{
			return _side;
		}
		
		public function set side(side : uint) : void
		{
			_side = side;
		}
		
		public function get number() : uint
		{
			return _number;
		}
		
		public function set number(number : uint) : void
		{
			_number = number;
		}
		
		public function get pos() : uint
		{
			return _pos;
		}
		
		public function set pos(pos : uint) : void
		{
			_pos = pos;
		}
		
		public function get links() : Array
		{
			return _links;
		}
		
		public function set links(links : Array) : void
		{
			_links = links;
		}
		
		public function get car() : uint
		{
			return _car;
		}
		
		public function set car(car : uint) : void
		{
			_car = car;
		}
	}
}
