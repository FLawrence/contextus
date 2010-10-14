
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

	document.editForm.addedTriples.value = addedString;
	document.editForm.changedTriples.value = changedString;
	document.editForm.deletedTriples.value = deletedString;

	document.editForm.saveButton.disabled = !store.isChanged();
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
						document.propertyTableForm.newObjectText.value, '');
			}
			else
			{
				triple = new Triple(document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value,
						document.propertyTableForm[fieldname + 'List'].options[document.propertyTableForm[fieldname + 'List'].selectedIndex].value,
						document.propertyTableForm.entityList.options[document.propertyTableForm[fieldname + 'EntityList'].selectedIndex].value, '');
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