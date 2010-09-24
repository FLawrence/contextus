<?php

$userID = $_GET['idhash'];

require('bc-fourstore-php/FourStore/FourStore_StorePlus.php');
require('bc-fourstore-php/FourStore/Namespace.php');
require('shakespeare_utilities.php');

FourStore_Namespace::addW3CNamespace();
FourStore_Namespace::add('loc','http://signage.ecs.soton.ac.uk/ontologies/location#');

$query = FourStore_Namespace::to_sparql();

$graphAuto = 'http://contextus.net/resource/midsum_night_dream/auto/';
$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $userID .  '/';

$queryAuto = $query . "\n" . 'SELECT ?name ?id WHERE { GRAPH <' . $graphAuto . '> { ?id ?p loc:Space ; rdfs:label ?name } }' . "\n";
$queryUser = $query . "\n" . 'SELECT ?name ?id WHERE { GRAPH <' . $graphUser . '> { ?id ?p loc:Space ; rdfs:label ?name } }' . "\n";

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

printXMLHeaders();
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>A Midsummer Night's Dream: Location Editor</title>
	<link rel="stylesheet" href="fourstore_editor.css" type="text/css" media="all" title="Default styles" />
	<script type="text/javascript">
<?php

	print("   var namedEntities = " . count($namedEntities) . ";\n");
	print("   var namedEntityNumArray = [];\n");
	print("   var namedEntityIDArray = [];\n");
	print("   var namedEntityNameArray = [];\n");

	$index = 0;
	foreach($namedEntities as $key => $namedEntity)
	{
		print("   namedEntityNumArray[" . $index . "] = '" . $key . "';\n");
		print("   namedEntityIDArray[" . $index . "] = '" . $namedEntity['id'] . "';\n");
		print("   namedEntityNameArray[" . $index . "] = '" . str_replace("'", "\\'", $namedEntity['name']) . "';\n");
		$index++;
	}
?>
	</script>
	<script type="text/javascript" src="functions.js"></script>
</head>
<body onload="setupChooser();">

<?php printNavigationList('locationedit.php', $userID) ?>

<div id="editForm">
<form name="editForm" method="post" action="savedata.php">
	<select name="namedEntityList" onchange="updateFields();"><option value="Please wait..." /></select> <br />

	<input name="namedEntityName" type="text" onkeyup="updateName();"> <button name="saveButton">save</button><br />

	<span id="namedEntityID" style="font-style: italic">Please Wait...</span>

	<input name="idhash" type="hidden" value="<?php print($userID); ?>" />
	<input name="saveType" type="hidden" value="location" />
	<input name="alteredData" type="hidden" value="" />
</form>
</div>

</body>
</html>
