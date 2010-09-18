
var originalNameArray = [];
var originalIDArray = [];

function setupChooser ( )
{
	var i;

	document.editForm.characterList.options.length = 0;
	
	for (i = 0; i < characters; i++)
	{
		originalNameArray[i] = characterNameArray[i];
		originalIDArray[i] = characterIDArray[i];

		var option = new Option(characterNameArray[i], characterIDArray[i], false, false);
		document.editForm.characterList.options[i] = option;
	}
	
	updateFields();
}

function updateFields ( )
{
	document.editForm.characterName.value = characterNameArray[document.editForm.characterList.selectedIndex];
	document.getElementById('characterID').innerHTML = characterIDArray[document.editForm.characterList.selectedIndex];

	checkFields();
}

function updateName ( )
{
	characterNameArray[document.editForm.characterList.selectedIndex] = document.editForm.characterName.value;
	document.editForm.characterList.options[document.editForm.characterList.selectedIndex].text = document.editForm.characterName.value;

	if (characterNameArray[document.editForm.characterList.selectedIndex] != originalNameArray[document.editForm.characterList.selectedIndex])
	{
		characterIDArray[document.editForm.characterList.selectedIndex] = '(changed, press save to update store)';
		document.getElementById('characterID').innerHTML = '(changed, press save to update store)';
	}
	else
	{
		characterIDArray[document.editForm.characterList.selectedIndex] = originalIDArray[document.editForm.characterList.selectedIndex];
		document.getElementById('characterID').innerHTML = originalIDArray[document.editForm.characterList.selectedIndex];
	}

	checkFields();
}

function checkFields ( )
{
	var dataString = "";

	for (i = 0; i < characters; i++)
	{
		if (originalNameArray[i] != characterNameArray[i])
		{
			if (dataString != "") dataString += "|";
			dataString += characterNumArray[i] + "=" + characterNameArray[i];
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