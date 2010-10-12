<?php

$userID = $_GET['idhash'];

require('bc-fourstore-php/FourStore/FourStore_StorePlus.php');
require('bc-fourstore-php/FourStore/Namespace.php');
require('shakespeare_utilities.php');

$propertyList = loadProperties();

FourStore_Namespace::addW3CNamespace();
FourStore_Namespace::add('loc','http://signage.ecs.soton.ac.uk/ontologies/location#');
FourStore_Namespace::add('ome','http://purl.org/ontomedia/core/expression#');


$query = FourStore_Namespace::to_sparql();

$graphAuto = 'http://contextus.net/resource/midsum_night_dream/auto/';
$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $userID .  '/';

$queryAuto = $query . "\nSELECT ?s ?p ?o\nFROM <" . $graphAuto . ">\n" . 'WHERE { ?s a loc:Space ; ?p ?o . FILTER (?p = rdfs:label || ?p = ome:is-shadow-of ||  ?p = rdf:type || ?p = ome:is || ?p = loc:is-part-of || ?p = loc:adjacent-to) }' . "\n";
$queryUser = $query . "\nSELECT ?s ?p ?o\nFROM <" . $graphUser . ">\n" . 'WHERE { ?s a loc:Space ; ?p ?o . FILTER (?p = rdfs:label || ?p = ome:is-shadow-of ||  ?p = rdf:type || ?p = ome:is || ?p = loc:is-part-of || ?p = loc:adjacent-to) }' . "\n";

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
	print("\tvar nameLabel = 'http://www.w3.org/2000/01/rdf-schema#label';\n");
	print("\tvar nonLabelTriples = '';\n");
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
	
	/*foreach($graph as $triple)
	{
		print("\tstore.set(new Triple('" . armourItem($triple['s']) . "', '" . armourItem($triple['p']) . "', '" . armourItem($triple['o']) . "'));\n");
		print("\toriginalStore.set(new Triple('" . armourItem($triple['s']) . "', '" . armourItem($triple['p']) . "', '" . armourItem($triple['o']) . "'));\n");
	}*/
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
	<!-- <input name="alteredData" type="hidden" value="" /> -->
	
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
