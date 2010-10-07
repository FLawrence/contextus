
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

	document.editForm.namedEntityName.value = item.getO();

	foundTriple = originalStore.findTriple(item.getS(), item.getP());

	if (item.getO() != foundTriple.getO())
	{
		document.getElementById('namedEntityID').innerHTML = '(changed, press save to update store)';
	}
	else
	{
		document.getElementById('namedEntityID').innerHTML = item.getS();
	}

	createPropertyTable(store, item.getS());
	checkFields();
}



function updateName ( index )
{
	var triples = store.getTriples();

	item = triples[index];
	foundTriple = originalStore.findTriple(item.getS(), item.getP());

	var indexName = 'editProperty' + index;

	item.setO(document.propertyTableForm[indexName].value);
	document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].text = item.getO();

	if (item.getO() != foundTriple.getO())
	{
		document.getElementById('namedEntityID').innerHTML = '(changed, press save to update store)';
	}
	else
	{
		document.getElementById('namedEntityID').innerHTML = item.getS();
	}

	createPropertyTable(store, item.getS());
	checkFields();
}



function checkFields ( )
{
	var dataString = "";

	var triples = store.getTriples();

	for (i = 0; i < triples.length; i++)
	{
		foundTriple = originalStore.findTriple(triples[i].getS(), triples[i].getP());

		if ((foundTriple == null) || (triples[i].getO() != foundTriple.getO()))
		{
			dataString += triples[i].getS() + " " + triples[i].getP() + " " + triples[i].getO() + "\n";
		}
	}

	document.editForm.alteredData.value = dataString;

	if (dataString == "")
	{
		document.editForm.saveButton.disabled = true;
	}
	else
	{
		document.editForm.saveButton.disabled = false;
	}
}



function createPropertyTable ( store, subject )
{
	var triples = store.getTriples();

	var table = '<form onsubmit="return false;" name="propertyTableForm"><table><tr><th>Property</th><th>Value</th><th></th></tr>'
	for (i = 0; i < triples.length; i++)
	{
		if (triples[i].getS() == subject)
		{
			foundTriple = originalStore.findTriple(triples[i].getS(), triples[i].getP());

			for (j = 0; j < properties.length; j++)
			{
				if ((properties[j].module + properties[j].property) == triples[i].getP())
				{
					var classAttribute = '';
					var edit = triples[i].getO();

					if ((foundTriple == null) || (triples[i].getO() != foundTriple.getO()))
					{
						classAttribute = ' class="changed"';
					}

					if (properties[j].subject == '*')
					{
						edit = '<input name="editProperty' + i + '" value="' + edit + '" onkeyup="updateName(' + i + ');"/>';
					}
	
					table += '<tr><td>' + triples[i].getP() + '</td><td' + classAttribute + '>' + edit + '</td><td><button onclick="removeProperty(' + i + '); return false;">delete</button></td></tr>';
				}
			}	

		}	
	}

	table += '<tr><td><select name="propertyList"><option value="http://purl.org/ontomedia/core/expression#is" name="http://purl.org/ontomedia/core/expression#is"/></select></td><td><select name="entityList"><option value="" name="please wait..."/></select></td><td><button onclick="addProperty();">add</button></td></tr>';

	table += '</table></form>'

	document.getElementById('propertyTable').innerHTML = table;

	document.propertyTableForm.propertyList.options.length = 0;
	var property = new Option('http://purl.org/ontomedia/core/expression#is', 'http://purl.org/ontomedia/core/expression#is', false, false);
	document.propertyTableForm.propertyList.options[0] = property;

	document.propertyTableForm.entityList.options.length = 0;
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
			document.propertyTableForm.entityList.options[index] = option;
			index++;
		}
	}
}



function addProperty ( )
{
	var triple = new Triple(document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value,
				document.propertyTableForm.propertyList.options[document.propertyTableForm.propertyList.selectedIndex].value,
				document.propertyTableForm.entityList.options[document.propertyTableForm.entityList.selectedIndex].value);
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