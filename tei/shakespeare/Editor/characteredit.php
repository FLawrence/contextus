<?php

$userID = 'dc77d0ceb755748698e53255b00fbe62ed0fccee';
//$userID = $_GET['idhash'];

require 'bc-fourstore-php/FourStore/FourStore_StorePlus.php';
require 'bc-fourstore-php/FourStore/Namespace.php';
require('shakespeare_utilities.php');

$propertyList = loadProperties();

FourStore_Namespace::addW3CNamespace();
FourStore_Namespace::add('omb','http://purl.org/ontomedia/ext/common/being#');
FourStore_Namespace::add('ome','http://purl.org/ontomedia/core/expression#');
FourStore_Namespace::add('foaf','http://xmlns.com/foaf/0.1/');

$query = FourStore_Namespace::to_sparql();

$graphAuto = 'http://contextus.net/resource/midsum_night_dream/auto/';
$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $userID .  '/';

$queryAuto = $query . "\nSELECT ?s ?p ?o\nFROM <" . $graphAuto . ">\n" . 'WHERE { ?s a omb:Character ; ?p ?o . FILTER (?p = foaf:name || ?p = ome:is-shadow-of || ?p = rdf:type || ?p = ome:is) }' . "\n";
$queryUser = $query . "\nSELECT ?s ?p ?o\nFROM <" . $graphUser . ">\n" . 'WHERE { ?s a omb:Character ; ?p ?o . FILTER (?p = foaf:name || ?p = ome:is-shadow-of || ?p = rdf:type || ?p = ome:is) }' . "\n";

$s = new FourStore_StorePlus('http://contextus.net:7000/sparql/');
$graph = array();

$autoCharsToBeIgnored = array();

$rUser = $s->query($queryUser);
foreach ($rUser['result']['rows'] as $result)
{
	addTripleToGraph($graph, makeTriple($result['s'], $result['p'], $result['o']));

	if ($result['p'] == 'http://purl.org/ontomedia/core/expression#is-shadow-of')
	{
		$autoCharsToBeIgnored[] = $result['o'];
	}
}

$rAuto = $s->query($queryAuto);

foreach ($rAuto['result']['rows'] as $result)
{
	if (!in_array($result['s'], $autoCharsToBeIgnored))
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
	<title>A Midsummer Night's Dream: Character Editor</title>
	<link rel="stylesheet" href="fourstore_editor.css" type="text/css" media="all" title="Default styles" />
	<script type="text/javascript" src="triples.js"></script>
	<script type="text/javascript">
<?php
	print("\tvar store = new TripleStore();\n");
	print("\tvar nameLabel = 'http://xmlns.com/foaf/0.1/name';\n");
	print("\tvar nonNameTriples = '';\n");
	print("\tvar properties = [];\n");

	foreach($graph as $triple)
	{
		print("\tstore.set(new Triple('" . armourItem($triple['s']) . "', '" . armourItem($triple['p']) . "', '" . armourItem($triple['o']) . "', '" . armourItem($triple['o']) . "'));\n");
	}

	$index = 0;
	foreach($propertyList as $property)
	{
		print("\tproperties[" . $index . "] = new Property('" . $property['property'] . "', '" . $property['module'] .
		      "', '" . $property['object restriction'] . "', '" . $property['subject restriction'] .
		      "', '" . $property['min'] . "', '" . $property['max'] . "', '" . $property['expected'] . "');\n");
		$index++;
	}
?>
	</script>
	<script type="text/javascript" src="functions.js"></script>
</head>
<body onload="setupChooser();">

<?php printNavigationList('characteredit.php', $userID) ?>

<div id="editForm">
<form name="editForm" method="post" action="savedata.php">
	<select class="chooseCharacter" name="namedEntityList" onchange="updateFields();"><option value="Please wait..." /></select>

	<button id="saveChanges" name="saveButton">Save Changes</button><br />

	<input name="namedEntityName" type="hidden" value="" /><!-- REMOVE -->

	<input name="idhash" type="hidden" value="<?php print($userID); ?>" />
	<input name="saveType" type="hidden" value="character" />
	<input name="addedTriples" type="hidden" value="" />
	<input name="changedTriples" type="hidden" value="" />
	<input name="deletedTriples" type="hidden" value="" />
</form>
</div>

<div id="propertyTable">
</div>

<p><span id="namedEntityID">Please Wait...</span></p>

</body>
</html>
