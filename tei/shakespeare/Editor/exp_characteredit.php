<?php
require('bc-fourstore-php/FourStore/FourStore_StorePlus.php');
require('bc-fourstore-php/FourStore/Namespace.php');
require('shakespeare_utilities.php');

$userID = getUserID();

$propertyList = loadProperties();

FourStore_Namespace::addW3CNamespace();
FourStore_Namespace::add('omb','http://purl.org/ontomedia/ext/common/being#');
FourStore_Namespace::add('omt','http://purl.org/ontomedia/ext/common/trait#');
FourStore_Namespace::add('ome','http://purl.org/ontomedia/core/expression#');
FourStore_Namespace::add('foaf','http://xmlns.com/foaf/0.1/');

$query = FourStore_Namespace::to_sparql();

$graphAuto = 'http://contextus.net/resource/midsum_night_dream/auto/';
$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $userID .  '/';
$graphMeta = 'http://contextus.net/resource/meta/';

$queryAuto = $query . "\nSELECT ?s ?p ?o\nFROM <" . $graphAuto . ">\n" . 'WHERE { ?s a omb:Character ; ?p ?o . FILTER (?p = foaf:name || ?p = ome:is-shadow-of || ?p = rdf:type || ?p = ome:is) }' . "\n";
$queryUser = $query . "\nSELECT ?s ?p ?o\nFROM <" . $graphUser . ">\n" . 'WHERE { ?s a omb:Character ; ?p ?o . FILTER (?p = foaf:name || ?p = ome:is-shadow-of || ?p = rdf:type || ?p = ome:is) }' . "\n";

$stateQuery = $query . "\nSELECT DISTINCT ?s ?label\n" . 'WHERE { ?s a omt:State-Of-Being; rdfs:label ?label }' . "\n";

print($stateQuery);

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


header('Cache-Control: no-store');
printXMLHeaders();
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>A Midsummer Night's Dream: Character Editor</title>
	<link rel="stylesheet" href="fourstore_editor.css" type="text/css" media="all" title="Default styles" />
	<script type="text/javascript" src="triples.js"></script>
	<script type="text/javascript">
	  var controlsToSetup = [];
	   controlsToSetup[0] = 'is';
	   var entityType = 'http://purl.org/ontomedia/ext/common/being#Character';
<?php
	print("\tvar store = new TripleStore();\n");
	print("\tvar nameLabel = 'http://xmlns.com/foaf/0.1/name';\n");
	print("\tvar pageType = 'character';\n");
	print("\tvar nonLabelTriples = '';\n");
	print("\tvar properties = [];\n");
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
		      "', '" . $property['min'] . "', '" . $property['max'] . "', '" . $property['expected'] . "');\n");
		$index++;
	}
?>
	</script>
	<script type="text/javascript" src="functions.js"></script>
</head>
<body onload="setupPage();">

<?php printNavigationList('exp_characteredit.php', $userID) ?>

<div id="entityChooser">
   <form name="entityChooserForm" method="POST" onsubmit="displayChanges();" action="savedata.php">
	<select class="chooseEntity" name="entityChooserSelect" onchange="entityChanged();"><option value="Please wait..." /></select>
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

<div class="control" id="stateInformation">
   <p class="controlTitle">State Information at Start of Text</p>
   <form name="stateInformationForm">
      <p>State of Being: 
      <select class="chooseEntity" name="stateBeingSelect" onchange="stateBChanged();">
      <?
      		$rAuto = $s->query($stateQuery);
      		
     		foreach ($rAuto['result']['rows'] as $result)
		{ 	
      			print("<option value='" . $result['s'] . "'>" . $result['label'] . "</option>");
      		}
      ?>
      </select></p>
      <p>State of Form: <select class="chooseEntity" name="stateFormSelect" onchange="stateFChanged();"><option value="Please wait..." /></select></p>
   </form>
</div>

<div class="control" id="sexInformation">
   <p class="controlTitle">Gender/Sexuality Information at Start of Text</p>
   <form name="stateInformationForm">
      	<p>Gender: <select class="chooseEntity" name="genderSelect" onchange="genderChanged();"><option value="Please wait..." /></select></p>
      	<p>Projected Gender: <select class="chooseEntity" name="projGenderSelect" onchange="projGenderChanged();"><option value="Please wait..." /></select></p>
      	<p>Sexuality: <select class="chooseEntity" name="sexSelect" onchange="sexChanged();"><option value="Please wait..." /></select></p>
      	<p>Projected Sexuality: <select class="chooseEntity" name="projSexSelect" onchange="projSexChanged();"><option value="Please wait..." /></select></p>
   </form>
</div>

<?php
writeEntityControl('Is');
?>

<p><span id="namedEntityID">Please Wait...</span></p>

</body>
</html>
