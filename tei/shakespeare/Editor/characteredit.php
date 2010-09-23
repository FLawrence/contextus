<?php

$userID = 'd7294d00acc853a07ef3b2ad94bc2dc490f299a3';
//$userID = $_GET['idhash'];

require 'bc-fourstore-php/FourStore/FourStore_StorePlus.php';
require 'bc-fourstore-php/FourStore/Namespace.php';

FourStore_Namespace::addW3CNamespace();
FourStore_Namespace::add('omb','http://purl.org/ontomedia/ext/common/being#');
FourStore_Namespace::add('foaf','http://xmlns.com/foaf/0.1/');
$query = FourStore_Namespace::to_sparql();

$graphAuto = 'http://contextus.net/resource/midsum_night_dream/auto/';
$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $userID .  '/';

$queryAuto = $query . "\n" . 'SELECT ?name ?id WHERE { GRAPH <' . $graphAuto . '> { ?id ?p omb:Character ; foaf:name ?name } }' . "\n";
$queryUser = $query . "\n" . 'SELECT ?name ?id WHERE { GRAPH <' . $graphUser . '> { ?id ?p omb:Character ; foaf:name ?name } }' . "\n";

$s = new FourStore_StorePlus('http://contextus.net:7000/sparql/');
$namedEntities = array();

$rAuto = $s->query($queryAuto);

$err = $s->getErrors();
if ($err) {
	print_r($err);
	throw new Exception(print_r($err,true));
}

foreach ($rAuto['result']['rows'] as $result)
{
	$entityNum = array_pop(explode("/",$result['id']));

	$namedEntities[$entityNum]['id'] = $result['id'];
	$namedEntities[$entityNum]['name'] = $result['name'];
}

$rUser = $s->query($queryUser);
foreach ($rUser['result']['rows'] as $result)
{
	$entityNum = array_pop(explode("/",$result['id']));

	$namedEntities[$entityNum]['id'] = $result['id'];
	$namedEntities[$entityNum]['name'] = $result['name'];
}

print('<' . '?xml version="1.1" encoding="iso-8859-1"?>' . "\n");
print('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n");
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>A Midsummer Night's Dream: Character Editor</title>
	<link rel="stylesheet" href="fourstore_editor.css" type="text/css" media="all" title="Default styles" />
	<script type="text/javascript">
<?php

	print("   var namedEntities = " . count($namedEntities) . ";\n");
	print("   var namedEntitiesNumArray = [];\n");
	print("   var namedEntitiesIDArray = [];\n");
	print("   var namedEntitiesNameArray = [];\n");

	$index = 0;
	foreach($namedEntities as $key => $namedEntity)
	{
		print("   namedEntityNumArray[" . $index . "] = '" . $key . "';\n");
		print("   namedEntityIDArray[" . $index . "] = '" . $namedEntity['id'] . "';\n");
		print("   namedEntityNameArray[" . $index . "] = '" . $namedEntity['name'] . "';\n");
		$index++;
	}
?>
	</script>
	<script type="text/javascript" src="functions.js"></script>
</head>
<body onload="setupChooser();">

<p class="selectedNav">Character Editor</a></p>
<p><a href="entityviewer.php?idhash=<?php print($userID); ?>">Entity Viewer</a></p>
<p><a href="eventviewer.php?idhash=<?php print($userID); ?>">Event Viewer</a></p>
<p><a href="characteredit.php?idhash=<?php print($userID); ?>">Location Editor</a></p>


<form name="editForm" method="post" action="savedata.php">
	<select name="namedEntityList" onchange="updateFields();"><option value="Please wait..." /></select> <br />

	<input name="namedEntityName" type="text" onkeyup="updateName();"> <button name="saveButton">save</button><br />

	<span id="namedEntityID" style="font-style: italic">Please Wait...</span>

	<input name="idhash" type="hidden" value="<?php print($userID); ?>" />
	<input name="saveType" type="hidden" value="character" />
	<input name="alteredData" type="hidden" value="" />


</form>

</body>
</html>
