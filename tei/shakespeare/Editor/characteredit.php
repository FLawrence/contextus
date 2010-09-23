<?php

require 'bc-fourstore-php/FourStore/FourStore_StorePlus.php';

$graphAuto = 'http://contextus.net/resource/midsum_night_dream/auto/';
$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $_GET['idhash'] .  '/';

$queryAuto = 'SELECT ?name ?id WHERE { GRAPH <' . $graphAuto . '> { ?id ?p <http://purl.org/ontomedia/ext/common/being#Character> ; <http://xmlns.com/foaf/0.1/name> ?name } }' . "\n";
$queryUser = 'SELECT ?name ?id WHERE { GRAPH <' . $graphUser . '> { ?id ?p <http://purl.org/ontomedia/ext/common/being#Character> ; <http://xmlns.com/foaf/0.1/name> ?name . } }' . "\n";

$s = new FourStore_StorePlus('http://contextus.net:7000/sparql/');
$characters = array();

$rAuto = $s->query($queryAuto);

$err = $s->getErrors();
if ($err) {
	print_r($err);
	throw new Exception(print_r($err,true));
}

foreach ($rAuto['result']['rows'] as $result)
{
	$characterNum = array_pop(explode("/",$result['id']));

	$characters[$characterNum]['id'] = $result['id'];
	$characters[$characterNum]['name'] = $result['name'];
}

$rUser = $s->query($queryUser);
foreach ($rUser['result']['rows'] as $result)
{
	$characterNum = array_pop(explode("/",$result['id']));

	$characters[$characterNum]['id'] = $result['id'];
	$characters[$characterNum]['name'] = $result['name'];
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

	print("   var characters = " . count($characters) . ";\n");
	print("   var characterNumArray = [];\n");
	print("   var characterIDArray = [];\n");
	print("   var characterNameArray = [];\n");

	$index = 0;
	foreach($characters as $key => $character)
	{
		print("   characterNumArray[" . $index . "] = '" . $key . "';\n");
		print("   characterIDArray[" . $index . "] = '" . $character['id'] . "';\n");
		print("   characterNameArray[" . $index . "] = '" . $character['name'] . "';\n");
		$index++;
	}
?>
	</script>
	<script type="text/javascript" src="functions.js"></script>
</head>
<body onload="setupChooser();">

<p>Your ID is: <?php print($_GET['idhash']); ?></p>

<form name="editForm" method="post" action="savedata.php">
	<select name="characterList" onchange="updateFields();"><option value="Please wait..." /></select> <br />

	<input name="characterName" type="text" onkeyup="updateName();"> <button name="saveButton">save</button><br />

	<span id="characterID" style="font-style: italic">Please Wait...</span>

	<input name="idhash" type="hidden" value="<?php print($_GET['idhash']); ?>" />
	<input name="alteredData" type="hidden" value="" />


</form>


<p><a href="eventviewer.php?idhash=<?php print($_GET['idhash']); ?>">Go to Event Viewer</a></p>

</body>
</html>
