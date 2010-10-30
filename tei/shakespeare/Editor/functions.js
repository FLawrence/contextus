
var currentEntity;
var rdfTypeLabel = 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';
var shadowOfLabel = 'http://purl.org/ontomedia/core/expression#is-shadow-of';
/* var entityType = 'http://signage.ecs.soton.ac.uk/ontologies/location#Space'; */

function sortBySName ( a, b )
{
   var as = getDisplayName(a.getS());
   var bs = getDisplayName(b.getS());

   return ( ( as == bs ) ? 0 : ( ( as > bs ) ? 1 : -1 ) );

}

function sortByO ( a, b )
{
   var as = a.getO();
   var bs = b.getO();

   return ( ( as == bs ) ? 0 : ( ( as > bs ) ? 1 : -1 ) );

}

function sortClass ( a, b )
{
   var as = a.display;
   var bs = b.display;

   return ( ( as == bs ) ? 0 : ( ( as > bs ) ? 1 : -1 ) );

}


function setupPage ( )
{
   classes = classes.sort(sortClass);

   selectTriples = store.findTriples('*', rdfTypeLabel, entityType);
   selectTriples = selectTriples.sort(sortBySName);

   currentEntity = selectTriples[0].getS();
   
   //alert("CurrentEntity: " + currentEntity);
   
   document.entityChooserForm.entityChooserSelect.options.length = 0;
   var index = 0;
   for (i = 0; i < selectTriples.length; i++)
   {
      var option = new Option(getDisplayName(selectTriples[i].getS()), selectTriples[i].getS(), false, false);
      document.entityChooserForm.entityChooserSelect.options[index++] = option;
   }
   
   updateAllControls();
}

function addEntity ( controlName )
{
    var propertyName = getFullPropertyName(controlName);
    var index = document.forms[controlName + 'Form'].elements[controlName + 'AddList'].selectedIndex;
    var propertyValue = document.forms[controlName + 'Form'].elements[controlName + 'AddList'].options[index].value;

   mainLabelChanged();

   if (isAuto(propertyValue) == true)
   {
      var oldNameTriple = store.findTriple(propertyValue, nameLabel);
      var newPropertyValue = createEntityInNewGraph(propertyValue, oldNameTriple.getO());

      var option = new Option(oldNameTriple.getO(), newPropertyValue, false, false);
      for (i = 0; i < document.entityChooserForm.entityChooserSelect.options.length; i++)
      {
         if (document.entityChooserForm.entityChooserSelect.options[i].value == propertyValue)
	 {
	    document.entityChooserForm.entityChooserSelect.options[i] = option;
         }
      }

      propertyValue = newPropertyValue;
   }

   store.add(new Triple(currentEntity, propertyName, propertyValue, ''));

   // Add reciprocal, if any
   for(i = 0; i < properties.length; i++)
   {
      if (propertyName == (properties[i].module + properties[i].property))
      {
         if (properties[i].reciprocal != '')
         {
            store.add(new Triple(propertyValue, properties[i].reciprocal, currentEntity, ''));
         }
      }
   }

   updateControl(controlName);
   checkFields();
}

function addClassEntity ( controlName )
{
	var propertyName = getFullPropertyName(controlName);
	var newClass = document.forms[controlName + 'Form'].elements[controlName + 'AddList'].value;
	var newClassRef = "";
	
	/* for (j = 0; j < classes.length; j++)
   	{  
   		alert("Type: " + classes[j].type + " newClass: " + newClass)
   		if(classes[j].type == newClass)
   		{
   			newClassRef = classes[j].value;
   		}
   	}*/
	
	//if(newClassRef != "")
	//{
		store.add(new Triple(currentEntity, propertyName, newClass , ''));
	//}

    
	updateClassControl(controlName);
	checkFields();
}

function removeEntity ( controlName, entityToRemove )
{
   var propertyName = getFullPropertyName(controlName);
   var triplesToRemove = store.findTripleIndexes(currentEntity, propertyName, entityToRemove);
   store.deleteByIndex(triplesToRemove[0]);

   // Add reciprocal, if any
   for(i = 0; i < properties.length; i++)
   {
      if (propertyName == (properties[i].module + properties[i].property))
      {
         if (properties[i].reciprocal != '')
         {
            triplesToRemove = store.findTripleIndexes(entityToRemove, properties[i].reciprocal, currentEntity);
            store.deleteByIndex(triplesToRemove[0]);
         }
      }
   }

   
   updateControl(controlName);
   checkFields();
}

function removeClassEntity ( controlName, entityToRemove )
{

   
   updateClassControl(controlName);
   checkFields();
}

/*
function addLocation ( controlName )
{
    var propertyName = getFullPropertyName(controlName);
    var index = document.forms[controlName + 'Form'].elements[controlName + 'AddList'].selectedIndex;
    var propertyValue = document.forms[controlName + 'Form'].elements[controlName + 'AddList'].options[index].value;

   mainLabelChanged();
   
   store.add(new Triple(currentEntity, propertyName, propertyValue, ''));
   
   updateControl(controlName);
   checkFields();
}

function removeLocation ( controlName, entityToRemove )
{
   var propertyName = getFullPropertyName(controlName);
   var triplesToRemove = store.findTripleIndexes(currentEntity, propertyName, entityToRemove);
   store.deleteByIndex(triplesToRemove[0]);
   
   updateControl(controlName);
   checkFields();
}
*/

function entityChanged ( )
{
    currentEntity = document.entityChooserForm.entityChooserSelect.options[document.entityChooserForm.entityChooserSelect.selectedIndex].value;

   updateAllControls();
}

function createEntityInNewGraph ( entity, name )
{
   var oldTripleIndexes = store.findTripleIndexes(entity, '*', '*');
   
   for (i = 0; i < oldTripleIndexes.length; i++)
   {
      store.deleteByIndex(oldTripleIndexes[i]);
   }
   
   var newID = 'http://contextus.net/resource/midsum_night_dream/' + userID + '/' + entity.substring(54);

   store.add(new Triple(newID, nameLabel, name, ''));
   store.add(new Triple(newID, shadowOfLabel, entity, ''));
   store.add(new Triple(newID, rdfTypeLabel, entityType, ''));
   
   return newID;
}

function mainLabelChanged ( )
{
   if (isAuto(currentEntity) == true)
   {
      userEntity = createEntityInNewGraph(currentEntity, document.generalInformationForm.entityName.value);
      currentEntity = userEntity;
   }
   else
   {
      nameTriples = store.findTriples(currentEntity, nameLabel, '*');
      nameTriples[0].setO(document.generalInformationForm.entityName.value);
   }

   var option = new Option(document.generalInformationForm.entityName.value, currentEntity, false, false);   
   var changeIndex = document.entityChooserForm.entityChooserSelect.selectedIndex;
   
   document.entityChooserForm.entityChooserSelect.options[changeIndex] = option;
   document.entityChooserForm.entityChooserSelect.selectedIndex = changeIndex;

   checkFields();
}

function updateMainChooser (  )
{
   nameTriples = store.findTriples(currentEntity, nameLabel, '*');

   if (nameTriples.length > 0)
   {
      document.generalInformationForm.entityName.value = nameTriples[0].getO();
   }
   else
   {
      document.generalInformationForm.entityName.value = 'unnamed';
      mainLabelChanged();
   }
}

function updateAllControls ( )
{
   updateMainChooser();

   for (control = 0; control < controlsToSetup.length; control++)
   {
      updateControl(controlsToSetup[control]);
   }
   
   for (control = 0; control < classControlsToSetup.length; control++)
   {
      updateClassControl(classControlsToSetup[control]);
   }   
   
   document.getElementById('namedEntityID').innerHTML = currentEntity;
}

function updateControl ( controlName )
{
   propertyName = getFullPropertyName(controlName);

   listTriples = store.findTriples(currentEntity, propertyName, '*');
   selectTriples = store.findTriples('*', rdfTypeLabel, entityType);
   selectTriples = selectTriples.sort(sortBySName);

   document.forms[controlName + 'Form'].elements[controlName + 'AddList'].options.length = 0;
   var optionsIndex = 0;

   for (i = 0; i < selectTriples.length; i++)
   {
      if (selectTriples[i].getS() == currentEntity) continue;
      
      var skip = false;
      for (j = 0; j < listTriples.length; j++)
      {
          if (selectTriples[i].getS() == listTriples[j].getO()) skip = true;
      }
      if (skip == true) continue;
      
      var option = new Option(getDisplayName(selectTriples[i].getS()), selectTriples[i].getS(), false, false);
      document.forms[controlName + 'Form'].elements[controlName + 'AddList'].options[optionsIndex++] = option;
   }

   if (optionsIndex == 0)
   {
      document.forms[controlName + 'Form'].elements[controlName + 'AddList'].disabled = true;
      document.forms[controlName + 'Form'].elements[controlName + 'AddButton'].disabled = true;
 
      var option = new Option('All entities in list', 'null', false, false);
      document.forms[controlName + 'Form'].elements[controlName + 'AddList'].options[0] = option;
   }
   else
   {
      document.forms[controlName + 'Form'].elements[controlName + 'AddList'].disabled = false;
      document.forms[controlName + 'Form'].elements[controlName + 'AddButton'].disabled = false;
   }

   var newTableHTML = '<tr><th>&nbsp;</th><th>&nbsp;</th></tr>';

   for (i = 0; i < listTriples.length; i++)
   {
      name = getDisplayName(listTriples[i].getO());
      newTableHTML += '<tr><td>' + name + '</td><td><button onClick="removeEntity(\'' + controlName + '\', \'' + listTriples[i].getO() + '\');">Delete</button></td></tr>'
   }

	document.getElementById(controlName + 'List').innerHTML = newTableHTML;
}

function updateClassControl ( controlName )
{
   propertyName = getFullPropertyName(controlName);

   listTriples = store.findTriples(currentEntity, propertyName, '*');
   listTriples = listTriples.sort(sortByO);
   selectTriples = store.findTriples('*', rdfTypeLabel, entityType);
   selectTriples = selectTriples.sort(sortBySName);
   
   document.forms[controlName + 'Form'].elements[controlName + 'AddList'].options.length = 0;
   var optionsIndex = 0;
   
   for (j = 0; j < classes.length; j++)
   {   	
   	if(classes[j].type == "http://purl.org/ontomedia/core/space#Space")
   	{
      		var option = new Option(classes[j].display, classes[j].value, false, false);
      		document.forms[controlName + 'Form'].elements[controlName + 'AddList'].options[optionsIndex++] = option;   	
      	}
   }

   if (optionsIndex == 0)
   {
      document.forms[controlName + 'Form'].elements[controlName + 'AddList'].disabled = true;
      document.forms[controlName + 'Form'].elements[controlName + 'AddButton'].disabled = true;
 
      var option = new Option('All entities in list', 'null', false, false);
      document.forms[controlName + 'Form'].elements[controlName + 'AddList'].options[0] = option;
   }
   else
   {
      document.forms[controlName + 'Form'].elements[controlName + 'AddList'].disabled = false;
      document.forms[controlName + 'Form'].elements[controlName + 'AddButton'].disabled = false;
   }

   var newTableHTML = '<tr><th>&nbsp;</th><th>&nbsp;</th></tr>';
   
   var spaceMatch = false;
   var classCount = 0;

   for (i = 0; i < listTriples.length; i++)
   {   	   
  	 for (j = 0; j < classes.length; j++)
   	{    
   		//alert("Object: " +  listTriples[i].getO() + " Class: " + classes[j].value);
   			
   		if(classes[j].value == listTriples[i].getO())
   		{
      			spaceMatch = true;
      			classCount++;
      			
      			name = classes[j].display
      			
  			if(classCount == 1)
   			{
      				newTableHTML += '<tr><td>' + name + '</td><td><button name="first" onClick="removeClassEntity(\'' + controlName + '\', \'' + listTriples[i].getO() + '\');">Delete</button></td></tr>'
      			}
      			else
      			{
      				 newTableHTML += '<tr><td>' + name + '</td><td><button onClick="removeClassEntity(\'' + controlName + '\', \'' + listTriples[i].getO() + '\');">Delete</button></td></tr>'
      			}
      		}
      	}
      	
   
	if(spaceMatch == false)
	{
		classCount++;
		
  		if(classCount == 1)
   		{
      			newTableHTML += '<tr><td>' + listTriples[i].getO() + '</td><td><button name="first" onClick="removeClassEntity(\'' + controlName + '\', \'' + listTriples[i].getO() + '\');">Delete</button></td></tr>'
      		}
      		else
      		{
      			 newTableHTML += '<tr><td>' + listTriples[i].getO() + '</td><td><button onClick="removeClassEntity(\'' + controlName + '\', \'' + listTriples[i].getO() + '\');">Delete</button></td></tr>'
      		}			
	}
   
   }
   
  /* if(classCount == 1)
   {
   	document.forms[controlName + 'Form'].elements['first'].disabled = true;
   }
*/
	document.getElementById(controlName + 'List').innerHTML = newTableHTML;
   
}


function getDisplayName ( entityID )
{
   var nameTriples = store.findTriples(entityID, nameLabel, '*');
   var name = '[could not find name for ' + entityID + ']';
   
   if (nameTriples.length > 0) name = nameTriples[0].getO();
   
   
   if (isAuto(entityID) == true)
   {
      return name + ' (auto)';
   }

   return name;
}

function getFullPropertyName ( controlName )
{
    if (controlName == 'locatedWithin')
		return 'http://signage.ecs.soton.ac.uk/ontologies/location#is-part-of';

    if (controlName == 'locatedAdjacentTo')
		return 'http://signage.ecs.soton.ac.uk/ontologies/location#adjacent-to';

    if (controlName == 'locatedAbove')
		return 'http://signage.ecs.soton.ac.uk/ontologies/location#is-above';

    if (controlName == 'locatedUnder')
		return 'http://signage.ecs.soton.ac.uk/ontologies/location#is-below';

    if (controlName == 'is')
		return 'http://purl.org/ontomedia/core/expression#is';
		
    if (controlName == 'type')
		return 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type';		
}

function isAuto ( entityID )
{
   if (entityID.substring(0,54) == 'http://contextus.net/resource/midsum_night_dream/auto/') return true;

   return false;
}


function setupChooser ( )
{
	document.editForm.namedEntityList.options.length = 0;

	triples = store.getTriples();
	var index = 0;
	for (i = 0; i < triples.length; i++)
	{
		if (triples[i].getP() == nameLabel)
		{
			var option = new Option(triples[i].getO(), triples[i].getS(), false, false);
			document.editForm.namedEntityList.options[index] = option;
			index++;
		}
	}
	
	updateFields();
}



function updateFields ( )
{
	item = store.findTriple(document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value, nameLabel);

//	document.editForm.namedEntityName.value = item.getO();

	document.getElementById('namedEntityID').innerHTML = item.getS();

	createPropertyTable(store, item.getS());
	checkFields();
}


function updateName ( index )
{
	var triples = store.getTriples();

	item = triples[index];

	var indexName = 'editProperty' + index;

	item.setO(document.propertyTableForm[indexName].value);
	document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].text = item.getO();

	document.getElementById('namedEntityID').innerHTML = item.getS();

	createPropertyTable(store, item.getS());
	checkFields();

	document.propertyTableForm[indexName].focus()
}


function displayChanges ( )
{
	alert('ADDED\n' + document.entityChooserForm.addedTriples.value);
	alert('CHANGED\n' + document.entityChooserForm.changedTriples.value);
	alert('DELETED\n' + document.entityChooserForm.deletedTriples.value);
}

function checkFields ( )
{
	var addedString = "";
	var changedString = "";
	var deletedString = "";

	var triples = store.getTriples();

	for (i = 0; i < triples.length; i++)
	{
		if (triples[i].getState() == 'added')
		{
			addedString += triples[i].getS() + "|" + triples[i].getP() + "|" + triples[i].getO() + "\n";
		}

		if (triples[i].getState() == 'changed')
		{
			changedString += triples[i].getS() + "|" + triples[i].getP() + "|" + triples[i].getO() + "|" + triples[i].getOriginalO() + "\n";
		}

		if (triples[i].getState() == 'deleted')
		{
			deletedString += triples[i].getS() + "|" + triples[i].getP() + "|" + triples[i].getO() + "\n";
		}
	}

	document.entityChooserForm.addedTriples.value = addedString;
	document.entityChooserForm.changedTriples.value = changedString;
	document.entityChooserForm.deletedTriples.value = deletedString;

	document.entityChooserForm.saveButton.disabled = !store.isChanged();
}



function createPropertyTable ( store, subject )
{
	var triples = store.getTriples();

	var propertyNames = [];
	var propertyCounts = [];

	var table = '<form onsubmit="return false;" name="propertyTableForm"><table><tr><th>Property</th><th>Value</th><th></th></tr>'
	for (i = 0; i < triples.length; i++)
	{
		if ((triples[i].getS() == subject) && (triples[i].getState() != 'deleted'))
		{
//			foundTriple = originalStore.findTriple(triples[i].getS(), triples[i].getP());

			for (j = 0; j < properties.length; j++)
			{
				if ((properties[j].module + properties[j].property) == triples[i].getP())
				{
					var propertyFound = 0;
					for (k = 0; k < propertyNames.length; k++)
					{
						if (propertyNames[k] == triples[i].getP())
						{
							propertyFound = 1;
							propertyCounts[k]++;
						}
					}
					if (propertyFound == 0)
					{
						propertyNames[propertyNames.length] = triples[i].getP();
						propertyCounts[propertyNames.length] = 1;
					}


					var classAttribute = '';
					var edit = triples[i].getO();
					var button = '';

					if (triples[i].getState() == 'changed')
					{
						classAttribute = ' class="changed"';
					}

					if (properties[j].expected == 'L')
					{
						if (((properties[j].module + properties[j].property) == nameLabel) && (propertyFound == 0))
						{
							edit = '<input name="editProperty' + i + '" value="' + edit + '" onkeyup="updateName(' + i + ');"/>';
						}
						else
						{
							edit = '<input name="editProperty' + i + '" value="' + edit + '"/>';
						}
					}


					if ((properties[j].min == 0) || (propertyFound == 1))
					{
						button = '<button onclick="removeProperty(' + i + '); return false;">delete</button>';
					}
					else
					{
						button = '<button disabled="true">delete</button>';
					}
	
					table += '<tr><td>' + triples[i].getP() + '</td><td' + classAttribute + '>' + edit + '</td><td>' + button + '</td></tr>';
				}
			}	

		}	
	}

	table += '<tr><td><select name="propertyList" onchange="updateNewObjectField(\'property\');">';
	table += '<option value="please wait..." />';
	table += '</select></td><td><span id="propertyObjectField"><select name="propertyEntityList"><option value="" name="please wait..."/></select></span></td><td><button onclick="addProperty(\'property\');">add</button></td></tr>';
	
	if (pageType == "location")
	{
		table += '<tr><td><select name="geoLinkList" onchange="updateNewObjectField(\'geoLink\');">';
		table += '<option value="please wait..." />';
		table += '</select></td><td><span id="geoLinkObjectField"><select name="geoLinkEntityList"><option value="" name="please wait..."/></select></span></td><td><button onclick="addProperty(\'geoLink\');">add</button></td></tr>';
	}

	table += '</table></form>'

	document.getElementById('propertyTable').innerHTML = table;

	if(pageType == "character")
	{
		document.propertyTableForm.propertyList.options.length = 0;
		var property = new Option('is', 'http://purl.org/ontomedia/core/expression#is', false, false);
		document.propertyTableForm.propertyList.options[0] = property;
		var property = new Option('is-shadow-of', 'http://purl.org/ontomedia/core/expression#is-shadow-of', false, false);
		document.propertyTableForm.propertyList.options[1] = property;
		var property = new Option('name', nameLabel, false, false);
		document.propertyTableForm.propertyList.options[2] = property;
		
		document.propertyTableForm.propertyEntityList.options.length = 0;
		var index = 0;
		for (i = 0; i < triples.length; i++)
		{
			if (triples[i].getS() == document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value)
			{
				continue;
			}
	
			if (triples[i].getP() == nameLabel)
			{
				var option = new Option(triples[i].getO(), triples[i].getS(), false, false);
				document.propertyTableForm.propertyEntityList.options[index] = option;
				index++;
			}
		}		
		
	}
	else if (pageType == "location")
	{
		document.propertyTableForm.propertyList.options.length = 0;
		var property = new Option('is', 'http://purl.org/ontomedia/core/expression#is', false, false);
		document.propertyTableForm.propertyList.options[0] = property;
		var property = new Option('is-shadow-of', 'http://purl.org/ontomedia/core/expression#is-shadow-of', false, false);
		document.propertyTableForm.propertyList.options[1] = property;
		var property = new Option('label', nameLabel, false, false);
		document.propertyTableForm.propertyList.options[2] = property;
		
		var property = new Option('is-part-of', 'http://signage.ecs.soton.ac.uk/ontologies/location#is-part-of', false, false);
		document.propertyTableForm.geoLinkList.options[0] = property;	
		var property = new Option('adjacent-to', 'http://signage.ecs.soton.ac.uk/ontologies/location#adjacent-to', false, false);
		document.propertyTableForm.geoLinkList.options[1] = property;	
		
		document.propertyTableForm.propertyEntityList.options.length = 0;
		var index = 0;
		for (i = 0; i < triples.length; i++)
		{
			if (triples[i].getS() == document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value)
			{
				continue;
			}
	
			if (triples[i].getP() == nameLabel)
			{
				var option = new Option(triples[i].getO(), triples[i].getS(), false, false);
				document.propertyTableForm.propertyEntityList.options[index] = option;
				index++;
			}
		}
		
		document.propertyTableForm.geoLinkEntityList.options.length = 0;
		var index = 0;
		for (i = 0; i < triples.length; i++)
		{
			if (triples[i].getS() == document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value)
			{
				continue;
			}
	
			if (triples[i].getP() == nameLabel)
			{
				var option = new Option(triples[i].getO(), triples[i].getS(), false, false);
				document.propertyTableForm.geoLinkEntityList.options[index] = option;
				index++;
			}
		}		
	}

}


function updateNewObjectField ( field )
{
	var newProperty = document.propertyTableForm[field + 'List'].options[document.propertyTableForm[field + 'List'].selectedIndex].value;

	for (j = 0; j < properties.length; j++)
	{
		if ((properties[j].module + properties[j].property) == newProperty)
		{
			if (properties[j].expected == 'L')
			{
				document.getElementById(field + 'ObjectField').innerHTML = '<input name="' + field + 'ObjectText" />';
			}
			else
			{
				document.getElementById(field + 'ObjectField').innerHTML = '<select name="' + field + 'EntityList"><option value="" name="please wait..."/></select>';

				document.propertyTableForm[field + 'EntityList'].options.length = 0;
				var index = 0;
				for (i = 0; i < triples.length; i++)
				{
					if (triples[i].getS() == document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value)
					{
						continue;
					}
		
					if (triples[i].getP() == nameLabel)
					{
						var option = new Option(triples[i].getO(), triples[i].getS(), false, false);
						document.propertyTableForm[field + 'EntityList'].options[index] = option;
						index++;
					}
				}
			}
		}
	}
}


function addProperty ( fieldname )
{
	var newProperty = document.propertyTableForm[fieldname + 'List'].options[document.propertyTableForm[fieldname + 'List'].selectedIndex].value;
	var triple;

	for (j = 0; j < properties.length; j++)
	{
		if ((properties[j].module + properties[j].property) == newProperty)
		{
			if (properties[j].expected == 'L')
			{
				triple = new Triple(document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value,
						document.propertyTableForm[fieldname + 'List'].options[document.propertyTableForm[fieldname + 'List'].selectedIndex].value,
						document.propertyTableForm[fieldname + 'ObjectText'].value, '');
			}
			else
			{
				triple = new Triple(document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value,
						document.propertyTableForm[fieldname + 'List'].options[document.propertyTableForm[fieldname + 'List'].selectedIndex].value,
						document.propertyTableForm[fieldname + 'EntityList'].options[document.propertyTableForm[fieldname + 'EntityList'].selectedIndex].value, '');
			}
		}
	}
	store.add(triple);
	createPropertyTable(store, triple.getS());
	checkFields();
}


function removeProperty ( propertyIndex )
{
	store.deleteByIndex(propertyIndex);
	updateFields();
}


function alertStore ( store )
{
	triples = store.getTriples();
	var index = 0;
	for (i = 0; i < triples.length; i++)
	{
		alert(	"S=" + triples[i].getS() + "\n" +
			"P=" + triples[i].getP() + "\n" +
			"O=" + triples[i].getO())
	}
}