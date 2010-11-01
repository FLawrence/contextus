<?php
require('bc-fourstore-php/FourStore/FourStore_StorePlus.php');
require('bc-fourstore-php/FourStore/Namespace.php');
require('shakespeare_utilities.php');

$userID = getUserID();

$propertyList = loadProperties();
$classList = loadClasses();

FourStore_Namespace::addW3CNamespace();
FourStore_Namespace::add('loc','http://signage.ecs.soton.ac.uk/ontologies/location#');
FourStore_Namespace::add('omb','http://purl.org/ontomedia/ext/common/being#');
FourStore_Namespace::add('omt','http://purl.org/ontomedia/ext/common/trait#');
FourStore_Namespace::add('ome','http://purl.org/ontomedia/core/expression#');
FourStore_Namespace::add('foaf','http://xmlns.com/foaf/0.1/');

$query = FourStore_Namespace::to_sparql();

$graphAuto = 'http://contextus.net/resource/midsum_night_dream/auto/';
$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $userID .  '/';

$queryCharAuto = $query . "\nSELECT ?s ?p \nFROM <" . $graphAuto . ">\n" . 'WHERE { ?s ?p omb:Character . }' . "\n";
$queryCharUser = $query . "\nSELECT ?s ?p \nFROM <" . $graphUser . ">\n" . 'WHERE { ?s ?p omb:Character . }' . "\n";
$queryLocAuto = $query . "\nSELECT ?s ?p \nFROM <" . $graphAuto . ">\n" . 'WHERE { ?s ?p loc:Space . }' . "\n";
$queryLocUser = $query . "\nSELECT ?s ?p \nFROM <" . $graphUser . ">\n" . 'WHERE { ?s ?p loc:Space . }' . "\n";


//print($queryUser);

$s = new FourStore_StorePlus('http://contextus.net:7000/sparql/');
$graph = array();
$autoCharsToBeIgnored = array();

$chars = array();

$rUser = $s->query($queryCharUser);
foreach ($rUser['result']['rows'] as $result)
{
	addTripleToGraph($graph, makeTriple($result['s'], $result['p'], $result['o']));
	
	array_push($chars, $result);

	if ($result['p'] == 'http://purl.org/ontomedia/core/expression#is-shadow-of')
	{
		$autoCharsToBeIgnored[] = $result['o'];
	}
}

$rAuto = $s->query($queryCharAuto);

foreach ($rAuto['result']['rows'] as $result)
{
	if (!in_array($result['s'], $autoCharsToBeIgnored))
	{
		addTripleToGraph($graph, makeTriple($result['s'], $result['p'], $result['o']));
		
		array_push($chars, $result);
	}
}

$autoLocsToBeIgnored = array();

$rUser = $s->query($queryLocUser);
foreach ($rUser['result']['rows'] as $result)
{
	addTripleToGraph($graph, makeTriple($result['s'], $result['p'], $result['o']));

	if ($result['p'] == 'http://purl.org/ontomedia/core/expression#is-shadow-of')
	{
		$autoLocsToBeIgnored[] = $result['o'];
	}
}

$rAuto = $s->query($queryLocAuto);

foreach ($rAuto['result']['rows'] as $result)
{
	if (!in_array($result['s'], $autoLocsToBeIgnored))
	{
		addTripleToGraph($graph, makeTriple($result['s'], $result['p'], $result['o']));
	}
}

$i = 1;
$potentialCharacterEntity = "";

while($potentialCharacterEntity == "")
{
	if(array_search($graphAuto . 'character/' . $i, $chars) !== false || array_search($graphUser . 'character/' . $i, $chars) !== false)
		$i++;
	else
		$potentialCharacterEntity = $graphUser . 'character/' . $i;
}

$i = 1;
$potentialLocationEntity = "";

while($potentialLocationEntity == "")
{
	if(array_search($graphAuto . 'location/' . $i, $chars) !== false || array_search($graphUser . 'location/' . $i, $chars) !== false)
		$i++;
	else
		$potentialLocationEntity = $graphUser . 'location/' . $i;
}


header('Cache-Control: no-store');
printXMLHeaders();
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>A Midsummer Night's Dream: Entity Creator</title>
	<link rel="stylesheet" href="fourstore_editor.css" type="text/css" media="all" title="Default styles" />
	<script type="text/javascript" src="triples.js"></script>
	<script type="text/javascript">
	   var controlsToSetup = [];
	   controlsToSetup[0] = 'is';
	   controlsToSetup[1] = 'isShadowOf';
	   var classControlsToSetup = [];
	   classControlsToSetup[0] = 'type';  
	   var entityType = '';
<?php
	print("\tvar store = new TripleStore();\n");
	print("\tvar nameLabel = 'http://www.w3.org/2000/01/rdf-schema#label';\n");
	print("\tvar pageType = 'location';\n");
	print("\tvar nonLabelTriples = '';\n");
	print("\tvar properties = [];\n");
	print("\tvar classes = [];\n");
	print("\tvar userID = '" . $userID . "';\n");

	foreach($graph as $triple)
	{
		print("\tstore.add(new Triple('" . armourItem($triple['s']) . "', '" . armourItem($triple['p']) . "', '" . armourItem($triple['o']) . "', '" . armourItem($triple['o']) . "'));\n");
	}

	$index = 0;
	foreach($propertyList as $property)
	{
		print("\tproperties[" . $index . "] = new Property('" . $property['property'] . "', '" . $property['module'] .
		      "', '" . $property['object restriction'] . "', '" . $property['subject restriction'] .
		      "', '" . $property['min'] . "', '" . $property['max'] . "', '" . $property['expected'] .
		      "', '" . $property['reciprocal'] . "');\n");
		$index++;
	}

	$index = 0;	
	foreach($classList as $class)
	{
		print("\tclasses[" . $index . "] = new Class('" . $class['Type'] . "', '" . $class['Value'] .
		      "', '" . $class['Display'] . "');\n");
		$index++;
	}	
?>
	</script>
	<script type="text/javascript" src="functions.js"></script>
</head>
<body onload="setupNewPage();">

<?php printNavigationList('entitycreator.php', $userID) ?>

<div class="control" id="generalInformation">
   <p class="controlTitle">General Information</p>
   <form name="generalInformationForm">
  	<select class="chooseType" name="newTypeSelect" onchange="getTypeChanged();">
  		<option value="<?php print $potentialCharacterEntity ?>">Character</option>
  		<option value="<?php print $potentialLocationEntity ?>" >Location</option>
  	</select>
      <p>Name: <input name="entityName" value="" onkeyup="mainLabelChanged();" /></p>
   </form>
</div>

<?php
writeEntityControl('Type', 'Class');
writeEntityControl('Is', '');
writeEntityControl('IsShadowOf', '');

?>

<div id="entityCreator">
	<p>
   <form name="entityCreatorForm" method="POST" onsubmit=";" action="">
	<button id="saveChanges" name="saveButton">Create Entity</button><br />

	<input name="idhash" type="hidden" value="<?php print($userID); ?>" />
	<input name="saveType" type="hidden" value="<?php print($type); ?>" />
	<input name="source" type="hidden" value="entitycreator.php" />

	<input name="addedTriples" type="hidden" value="" />
	<input name="changedTriples" type="hidden" value="" />
	<input name="deletedTriples" type="hidden" value="" />
	</form>
	</p>
</div>
</body>
</html>

