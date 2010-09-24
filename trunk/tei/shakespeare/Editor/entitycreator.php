<?php

if (isset($_GET['idhash']))
{
	$userID = $_GET['idhash'];
}
else
{
	$userID = $_POST['idhash'];
}

require 'bc-fourstore-php/FourStore/FourStore_StorePlus.php';
require 'bc-fourstore-php/FourStore/Namespace.php';

FourStore_Namespace::addW3CNamespace();
FourStore_Namespace::add('omb','http://purl.org/ontomedia/ext/common/being#');
FourStore_Namespace::add('ome','http://purl.org/ontomedia/core/expression#');
FourStore_Namespace::add('loc','http://signage.ecs.soton.ac.uk/ontologies/location#');
FourStore_Namespace::add('foaf','http://xmlns.com/foaf/0.1/');
$query = FourStore_Namespace::to_sparql();

$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $userID .  '/';

$s = new FourStore_StorePlus('http://contextus.net:7000/sparql/');
$namedEntities = array();

if(isset($_POST['createNew']))
{
	$type = $_POST['entityType'];
	$label = $_POST['entityName'];
	
	if($type == "Character")
	{
		// Add new entity to user graph of type omb:$type and with foaf:name $label
	}
	else if($type == "Space")
	{
		//Add new entity to user graph of type loc:$type and with rdfs:label $label
	}

}


?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>A Midsummer Night's Dream: New Entity Creator</title>
	<link rel="stylesheet" href="fourstore_editor.css" type="text/css" media="all" title="Default styles" />
</head>
<body>

<p class="navbar"><a href="characteredit.php?idhash=<?php print($userID); ?>">Character Editor</a> &nbsp; <a href="entityviewer.php?idhash=<?php print($userID); ?>">Entity Viewer</a> &nbsp; <a href="eventviewer.php?idhash=<?php print($userID); ?>">Event Viewer</a> &nbsp; <a href="locationedit.php?idhash=<?php print($userID); ?>">Location Editor</a>&nbsp; <span class="selectedNav">Entity Creator</span></p>

<form name="createForm" method="post" action="entitycreator.php">
<input name="idhash" type="hidden" value="<?php print($userID); ?>" />

<select name="entityType">
<option value="Character">Character</option>
<option value="Space">Location</option>
</select>

<input name="entityName" type="text"> <button name="createNew">Create</button>

</form>
