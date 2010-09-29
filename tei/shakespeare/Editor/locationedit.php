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

$queryAuto = $query . "\nSELECT ?s ?p ?o\nFROM <" . $graphAuto . ">\n" . 'WHERE { ?s a loc:Space ; ?p ?o . FILTER (?p = rdfs:label || ?p = rdf:type) }' . "\n";
$queryUser = $query . "\nSELECT ?s ?p ?o\nFROM <" . $graphUser . ">\n" . 'WHERE { ?s a loc:Space ; ?p ?o . FILTER (?p = rdfs:label || ?p = rdf:type) }' . "\n";

$s = new FourStore_StorePlus('http://contextus.net:7000/sparql/');
$graph = array();

$autoLocsToBeIgnored = array();

$rUser = $s->query($queryUser);
foreach ($rUser['result']['rows'] as $result)
{
	addTripleToGraph($graph, makeTriple($result['s'], $result['p'], $result['o']));

	if ($result['p'] == 'http://purl.org/ontomedia/core/expression#is-shadow-of')
	{
		$autoLocsToBeIgnored[] = $result['o'];
	}
}

$rAuto = $s->query($queryAuto);

foreach ($rAuto['result']['rows'] as $result)
{
	if (!in_array($result['s'], $autoLocsToBeIgnored))
	{
		addTripleToGraph($graph, makeTriple($result['s'], $result['p'], $result['o']));
	}
}

function addTripleToGraph ( &$graph, $triple )
{
	$graph[md5($triple['s'] . $triple['p'])] = $triple;
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

printXMLHeaders();
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>A Midsummer Night's Dream: Location Editor</title>
	<link rel="stylesheet" href="fourstore_editor.css" type="text/css" media="all" title="Default styles" />
	<script type="text/javascript" src="triples.js"></script>
	<script type="text/javascript">
<?php
	print("\tvar store = new TripleStore();\n");
	print("\tvar originalStore = new TripleStore();\n");

	$index = 0;
	foreach($graph as $triple)
	{
		print("\tstore.set(new Triple('" . armourItem($triple['s']) . "', '" . armourItem($triple['p']) . "', '" . armourItem($triple['o']) . "'));\n");
		print("\toriginalStore.set(new Triple('" . armourItem($triple['s']) . "', '" . armourItem($triple['p']) . "', '" . armourItem($triple['o']) . "'));\n");
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

<div id="propertyTable">
</div>

</body>
</html>
