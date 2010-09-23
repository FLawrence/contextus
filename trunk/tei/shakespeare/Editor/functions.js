
var originalNameArray = [];
var originalIDArray = [];

function setupChooser ( )
{
	var i;

	document.editForm.namedEntityList.options.length = 0;
	
	for (i = 0; i < namedEntities; i++)
	{
		originalNameArray[i] = namedEntityNameArray[i];
		originalIDArray[i] = namedEntityIDArray[i];

		var option = new Option(namedEntityNameArray[i], namedEntityIDArray[i], false, false);
		document.editForm.namedEntityList.options[i] = option;
	}
	
	updateFields();
}

function updateFields ( )
{
	document.editForm.namedEntityName.value = namedEntityNameArray[document.editForm.namedEntityList.selectedIndex];
	document.getElementById('namedEntityID').innerHTML = namedEntityIDArray[document.editForm.namedEntityList.selectedIndex];

	checkFields();
}

function updateName ( )
{
	namedEntityNameArray[document.editForm.namedEntityList.selectedIndex] = document.editForm.namedEntityName.value;
	document.editForm.namedEntityList.options[document.editForm.namedEntityList.selectedIndex].text = document.editForm.namedEntityName.value;

	if (namedEntityNameArray[document.editForm.namedEntityList.selectedIndex] != originalNameArray[document.editForm.namedEntityList.selectedIndex])
	{
		namedEntityIDArray[document.editForm.namedEntityList.selectedIndex] = '(changed, press save to update store)';
		document.getElementById('namedEntityID').innerHTML = '(changed, press save to update store)';
	}
	else
	{
		namedEntityIDArray[document.editForm.namedEntityList.selectedIndex] = originalIDArray[document.editForm.namedEntityList.selectedIndex];
		document.getElementById('namedEntityID').innerHTML = originalIDArray[document.editForm.namedEntityList.selectedIndex];
	}

	checkFields();
}

function checkFields ( )
{
	var dataString = "";

	for (i = 0; i < namedEntities; i++)
	{
		if (originalNameArray[i] != namedEntityNameArray[i])
		{
			if (dataString != "") dataString += "|";
			dataString += namedEntityNumArray[i] + "=" + namedEntityNameArray[i];
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