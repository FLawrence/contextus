<?php

/*
Contains:

getUserID ( )
printXMLHeaders ( )
printNavigationList ( $current, $userID )
loadProperties ( )
addTripleToGraph ( &$graph, $triple )
makeTriple ( $subject, $predicate, $object )
armourItem ( $item )

*/

function getUserID ( )
{
    if (file_exists('debug_user_id'))
    {
    	$userFile = file('debug_user_id');
    	return (trim($userFile[0]));
    }
    
    return $_GET['idhash'];
}

function printXMLHeaders ( )
{
	print('<' . '?xml version="1.0" encoding="iso-8859-1"?>' . "\n");
	print('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n");
}



function printNavigationList ( $current, $userID )
{
	$navigationArray = array('exp_characteredit.php' => 'Character Editor',
							 'entityviewer.php' => 'Entity Viewer',
							 'eventviewer.php' => 'Event Viewer',
							 'exp_locationedit.php' => 'Location Editor');

	print('<ul id="navigationList">' . "\n");
	foreach ($navigationArray as $url => $label)
	{
		if ($current == $url)
			print('   <li id="selectedNav">' . $label . '</li>' . "\n");
		else
			print('   <li><a href="' . $url .  '?idhash=' . $userID . '">' . $label . '</a></li>' . "\n");
	}
	print('   <li><a href="index.php">Logout</a></li>' . "\n");
	print('</ul>' . "\n");
}



function loadProperties ( )
{
	$propertyDetails = array();

	$propertyFile = fopen("Properties.csv", "r");

	$headers = fgetcsv($propertyFile);

    while (($data = fgetcsv($propertyFile)) !== FALSE)
    {
    	$dataHash = array();

    	for ($i = 0; $i < count($headers); $i++)
    		$dataHash[$headers[$i]] = $data[$i];

    	$propertyDetails[] = $dataHash;
    }

	fclose($propertyFile);

	return $propertyDetails;
}

function addTripleToGraph ( &$graph, $triple )
{
	$graph[md5($triple['s'] . $triple['p'] . $triple['o'])] = $triple;
}

function makeTriple ( $subject, $predicate, $object )
{
	$triple = array( 's' => $subject, 'p' => $predicate, 'o' => $object);
	return $triple;
}

function armourItem ( $item )
{
//	if (substr($item, 0 , 7) == 'http://')
//		$item = '<' . $item . '>';
//	else
		$item = str_replace("'", "\\'", $item);

	return $item;
}

function writeEntityControl ( $controlTitle )
{
	$controlName = str_replace(' ', '', $controlTitle);
	$controlName[0] = strtolower($controlName[0]); 

	echo <<< END_FORM
	<div class="control" id="{$controlName}">
		<p class="controlTitle">{$controlTitle}</p>
		<form name="{$controlName}Form" onSubmit="return false;">
			<select class="entitySelect" name="{$controlName}AddList">
				<option value="Please wait..." />
			</select>

			<button id="{$controlName}AddButton" name="{$controlName}AddButton" onClick="addEntity('{$controlName}');">Add</button>
       
		<table class="entityList" id="{$controlName}List">
			<tr><th>Name</th><th>&nbsp;</th></tr>
		</table>
		</form>
	</div>
END_FORM;
}

?>