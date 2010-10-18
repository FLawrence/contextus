<?php
require('bc-fourstore-php/FourStore/FourStore_StorePlus.php');
require('bc-fourstore-php/FourStore/Namespace.php');
require('shakespeare_utilities.php');

$userID = getUserID();

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

function writeLocationControl ( $controlTitle )
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

	   <button id="{$controlName}AddButton" name="{$controlName}AddButton" onClick="addLocation('{$controlName}');">Add</button>
       
       <table class="locationList" id="{$controlName}List">
	      <tr><th>Name</th><th>&nbsp;</th></tr>
       </table>
   </form>
</div>
END_FORM;
}

header('Cache-Control: no-store');
printXMLHeaders();
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>A Midsummer Night's Dream: Location Editor (Expanded)</title>
	<link rel="stylesheet" href="fourstore_editor.css" type="text/css" media="all" title="Default styles" />
	<script type="text/javascript" src="triples.js"></script>
	<script type="text/javascript">
	   var controlsToSetup = [];
	   controlsToSetup[0] = 'locatedWithin';
	   controlsToSetup[1] = 'locatedAdjacentTo';
	   controlsToSetup[2] = 'is';
<?php	   
	print("\tvar store = new TripleStore();\n");
	print("\tvar nameLabel = 'http://www.w3.org/2000/01/rdf-schema#label';\n");
	print("\tvar pageType = 'location';\n");
	print("\tvar nonLabelTriples = '';\n");
	print("\tvar properties = [];\n");
	print("\tvar userID = '" . $userID . "';\n");

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
<body onload="setupPage();">

<?php printNavigationList('exp_locationedit.php', $userID) ?>

<div id="entityChooser">
   <form name="entityChooserForm" method="POST" action="savedata.php">
	<select class="chooseLocation" name="entityChooserSelect" onchange="entityChanged();"><option value="Please wait..." /></select>
	<button id="saveChanges" name="saveButton">Save Changes</button><br />

	<input name="idhash" type="hidden" value="<?php print($userID); ?>" />
	<input name="saveType" type="hidden" value="location" />
	<input name="source" type="hidden" value="exp_locationedit.php" />
	
	<input name="addedTriples" type="hidden" value="" />
	<input name="changedTriples" type="hidden" value="" />
	<input name="deletedTriples" type="hidden" value="" />		
	</form>
</div>

<div class="control" id="generalInformation">
   <p class="controlTitle">General Information</p>
   <form name="generalInformationForm">
      <p>Name: <input name="entityName" value="" onkeyup="mainLabelChanged();" /></p>
   </form>
</div>

<?php
writeLocationControl('Located Within');
writeLocationControl('Located Adjacent To');
writeLocationControl('Is');
?>

<p id="namedEntityID">Please wait...</p>

</body>
</html>
