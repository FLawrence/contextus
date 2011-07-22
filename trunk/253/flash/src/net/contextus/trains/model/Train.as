package net.contextus.trains.model 
{

	/**
	 * @author moj
	 */
	public class Train 
	{
		private var _carOffsets : Array = [1, 38, 74, 110, 146, 182, 218];
		private var _carLefts : Array = [38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55];
		private var _carRights : Array = [56, 57, 58, 59, 60, 61, 62, 63, 64, 65, 66, 67, 69, 60, 70, 71, 72, 73];


		private var _peopleDict : Object;
		private var _cars : Array;

		public function Train(people : Object) 
		{
			this._peopleDict = {};
			this._cars = [];
			
			var car : uint = 0;
			var currentCar : Array = [];
			for(var key : String in people)
			{
				var obj : Object = people[key];
				var person : Person = new Person();
				person.car = car;
				person.name = obj.name;
				person.inside = obj.inside;
				person.appearance = obj.appearance;
				person.thinking = obj.thinking;
				person.number = parseInt(obj.number);
				person.gender = (obj["Gender"] == "Female" ? Person.GENDER_FEMALE : Person.GENDER_MALE);
			
				if(person.number == _carOffsets[car + 1]) 
				{
					_cars.push(currentCar);
					currentCar = [];
					car++;
				}
				
				person.side = (_carLefts.indexOf(person.number) != -1 ? Person.SIDE_LEFT : Person.SIDE_RIGHT);	
				person.pos = (person.number - _carOffsets[car]);
				
				currentCar.push(person);
				
				_peopleDict[parseInt(obj.number)] = person;
			}
			
			_cars.push(currentCar);
		}
		
		public function get cars() : Array
		{
			return _cars;
		}
	}
}
