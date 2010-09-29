
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

function updateName ( )
{
	item = store.findTriple(document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].value, nameLabel);
	foundTriple = originalStore.findTriple(item.getS(), item.getP());

	item.setO(document.editForm.namedEntityName.value);
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

		if (triples[i].getO() != foundTriple.getO())
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

	var table = '<table><tr><th>Property</th><th>Value</th><th></th></tr>'

	for (i = 0; i < triples.length; i++)
	{
		if (triples[i].getS() == subject)
		{
			foundTriple = originalStore.findTriple(triples[i].getS(), triples[i].getP());

			if (triples[i].getO() != foundTriple.getO())
			{
				table += '<tr><td>' + triples[i].getP() + '</td><td clas="changed">' + triples[i].getO() + '</td><td><a href="">[X]</a></td></tr>';
			}
			else
			{
				table += '<tr><td>' + triples[i].getP() + '</td><td>' + triples[i].getO() + '</td><td><a href="">[X]</a></td></tr>';
			}
		}	
	}

	table += '</table>'

	document.getElementById('propertyTable').innerHTML = table;
}