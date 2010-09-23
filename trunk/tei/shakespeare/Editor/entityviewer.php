<?php

if (isset($_GET['idhash']))
{
	$userID = $_GET['idhash'];
}
else
{
$userID = 'd7294d00acc853a07ef3b2ad94bc2dc490f299a3';
//	$userID = $_POST['idhash'];
}

require 'bc-fourstore-php/FourStore/FourStore_StorePlus.php';
require 'bc-fourstore-php/FourStore/Namespace.php';

FourStore_Namespace::addW3CNamespace();
FourStore_Namespace::add('omb','http://purl.org/ontomedia/ext/common/being#');
FourStore_Namespace::add('ome','http://purl.org/ontomedia/core/expression#');
FourStore_Namespace::add('omj','http://purl.org/ontomedia/ext/events/travel#');
FourStore_Namespace::add('loc','http://signage.ecs.soton.ac.uk/ontologies/location#');
FourStore_Namespace::add('foaf','http://xmlns.com/foaf/0.1/');
$query = FourStore_Namespace::to_sparql();

$graphAuto = 'http://contextus.net/resource/midsum_night_dream/auto/';
$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $userID .  '/';

$s = new FourStore_StorePlus('http://contextus.net:7000/sparql/');

$entity = array();
$type = "Character";

if(isset($_POST['gotoLoc']))
{
	$entityID = "location/" . $_POST['locNum'];
	$type = "Space";
}
else if(isset($_POST['gotoChar']))
	$entityID = "character/" . $_POST['charNum'];
else
{
	$queryAuto1 = $query . "\n" . 'SELECT DISTINCT ?id WHERE { { GRAPH ?g {?id a omb:Character}} {GRAPH <' . $graphAuto . '> { ?id ?p ?o } } } ORDER BY ?id LIMIT 1' . "\n";

	$rAuto = $s->query($queryAuto1);

	//print("<p>Result: '" . $rAuto[0]['id'] . "'</p>");

	$entityID = "character/" . array_pop(explode("/",$rAuto['result']['rows'][0]['id']));

	//print("<p>Result: '" . $entityID . "'</p>");
}


$queryAuto2 = $query . "\n" . 'SELECT ?p ?o WHERE { GRAPH <' . $graphAuto . '> { <' . $graphAuto . $entityID . '> ?p ?o } }' . "\n";
$rAuto = $s->query($queryAuto2);

$rUser = null;


if(isset($_GET['idhash']))
{
	$queryUser2 = $query . "\n" . 'SELECT ?p ?o WHERE { GRAPH <' . $graphAuto . '> { <' . $graphUser . $entityID . '> ?p ?o } }' . "\n";
	$rUser = $s->query($queryUser2);
}



$involved_count = 0;
$aka_count = 0;
$shadow_count = 0;
$loc_in_count = 0;
$part_of_count = 0;
$adj_to_count = 0;

foreach ($rAuto['result']['rows'] as $result)
{
	//print("<p>Checking property: " . $result['p'] . "</p>");
	switch ($result['p'])
	{
		case "http://xmlns.com/foaf/0.1/name":
			$entity['name']['auto'] = $result['o'];
			break;

		case "http://www.w3.org/2000/01/rdf-schema#label":
			$entity['name']['auto'] = $result['o'];
			break;

		case "http://purl.org/ontomedia/core/expression#involved-in":

			$queryAuto3 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

			$result3 = $s->query($queryAuto3);

			$entity['involved']['auto'][$involved_count] = $result3['result']['rows'][0]['label'];
			$involved_count ++;
			break;

		case "http://www.w3.org/1999/02/22-rdf-syntax-ns#type":
			$entity['type']['auto'] = array_pop(explode("#",$result['o']));
			$type = $entity['type']['auto'];
			break;

		case "http://purl.org/ontomedia/core/expression#is":

			if($type = "Character")
			{
				$queryAuto4a = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name . } }' . "\n";

				$result4a = $s->query($queryAuto4a);

				$entity['aka']['auto'][$aka_count] = $result4a['result']['rows'][0]['name'];
			}
			else
			{
				$queryAuto4b = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

				$result4b = $s->query($queryAuto4b);

				$entity['aka']['auto'][$aka_count] = $result4b['result']['rows'][0]['label'];
			}

			$aka_count ++;
			break;

		case "http://purl.org/ontomedia/core/expression#is-shadow-of":

			if($type = "Character")
			{
				$queryAuto5a = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name . } }' . "\n";

				$result5a = $s->query($queryAuto5a);

				$entity['shadows']['auto'][$shadow_count] = $result5a['result']['rows'][0]['name'];
			}
			else
			{
				$queryAuto5b = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

				$result5b = $s->query($queryAuto5b);

				$entity['shadows']['auto'][$shadow_count] = $result5b['result']['rows'][0]['label'];
			}
			$shadow_count++;
			break;

		case "http://signage.ecs.soton.ac.uk/ontologies/location#is-located-in":

			$queryAuto6 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

			$result6 = $s->query($queryAuto6);

			$entity['located-in']['auto'][$loc_in_count] = $result6['result']['rows'][0]['label'];
			$loc_in_count++;
			break;

		case "http://signage.ecs.soton.ac.uk/ontologies/location#is-part-of":

			$queryAuto7 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

			$result7 = $s->query($queryAuto7);

			$entity['part-of']['auto'][$part_of_count] = $result7['result']['rows'][0]['label'];
			$part_of_count++;
			break;

		case "http://signage.ecs.soton.ac.uk/ontologies/location#adjacent-to":

			$queryAuto8 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

			$result8 = $s->query($queryAuto8);

			$entity['adjacent-to']['auto'][$adj_to_count] = $result8['result']['rows'][0]['label'];
			$adj_to_count++;
			break;

	}

}

if($rUser != null)
{
	foreach($rUser['result']['rows'] as $result)
	{
		switch ($result['p'])
		{
			case "http://xmlns.com/foaf/0.1/name":
				$entity['name']['user'] = $result['o'];
				break;

			case "http://www.w3.org/2000/01/rdf-schema#label":
				$entity['name']['user'] = $result['o'];
				break;

			case "http://purl.org/ontomedia/core/expression#involved-in":

				$queryAuto3 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphUser . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

				$result3 = $s->query($queryAuto3);

				$entity['involved']['user'][$involves_count] = $result3['result']['rows'][0]['label'];
				$involved_count ++;
				break;

			case "http://www.w3.org/1999/02/22-rdf-syntax-ns#type":
				$event['type']['auto'] = $event['type'];
				$event['type']['user'] = array_pop(explode("#",$result['o']));
				$type = $event['type']['user'];
				break;

			case "http://purl.org/ontomedia/core/expression#is":

				if($type = "Character")
				{
					$queryAuto4a = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphUser . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name . } }' . "\n";

					$result4a = $s->query($queryAuto4a);

					$entity['aka']['user'][$aka_count] = $result4a['result']['rows'][0]['name'];
				}
				else
				{
					$queryAuto4b = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphUser . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

					$result4b = $s->query($queryAuto4b);

					$entity['aka']['user'][$aka_count] = $result4b['result']['rows'][0]['label'];
				}

				$aka_count ++;
				break;

			case "http://purl.org/ontomedia/core/expression#is-shadow-of":

				if($type = "Character")
				{
					$queryAuto5a = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphUser . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name . } }' . "\n";

					$result5a = $s->query($queryAuto5a);

					$entity['shadows']['user'][$shadow_count] = $result5a['result']['rows'][0]['name'];
				}
				else
				{
					$queryAuto5b = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphUser . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

					$result5b = $s->query($queryAuto5b);

					$entity['shadows']['user'][$shadow_count] = $result5b['result']['rows'][0]['label'];
				}
				$shadow_count++;
				break;

			case "http://signage.ecs.soton.ac.uk/ontologies/location#is-located-in":

				$queryAuto6 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphUser . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

				$result6 = $s->query($queryAuto6);

				$entity['located-in']['user'][$loc_in_count] = $result6['result']['rows'][0]['label'];
				$loc_in_count++;
				break;

			case "http://signage.ecs.soton.ac.uk/ontologies/location#is-part-of":

				$queryAuto7 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphUser . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

				$result7 = $s->query($queryAuto7);

				$entity['part-of']['user'][$part_of_count] = $result7['result']['rows'][0]['label'];
				$part_of_count++;
				break;

			case "http://signage.ecs.soton.ac.uk/ontologies/location#adjacent-to":

				$queryAuto8 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphUser . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

				$result8 = $s->query($queryAuto8);

				$entity['adjacent-to']['user'][$adj_to_count] = $result8['result']['rows'][0]['label'];
				$adj_to_count++;
				break;
		}
	}
}

print('<' . '?xml version="1.1" encoding="iso-8859-1"?>' . "\n");
print('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n");


function armourQuery ( $query )
{
	$p1 = preg_replace('/\</i','&lt;',$query);
	return preg_replace('/\>/i','&gt;',$p1);
}

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>A Midsummer Night's Dream: Entity Viewer</title>
	<link rel="stylesheet" href="fourstore_editor.css" type="text/css" media="all" title="Default styles" />
</head>
<body>

<?php //print_r($_POST); // print("<p>Query 1: " . armourQuery($queryAuto1) . "</p>"); ?>

<table>
<tr><td>Entity Number</td><td><?php print($entityID);?></td></tr>
<tr><td>Entity Name</td><td><?php
if(isset($entity['name']['user']))
	print($entity['name']['user'] . " [" . $entity['name']['auto'] . "]");
else
	print($entity['name']['auto']);

?></td></tr>
<tr><td>Entity Type</td><td><?php
if(isset($entity['type']['user']))
	print($entity['type']['user'] . " [" . $entity['type']['auto'] . "]");
else
	print($entity['type']['auto']);

?></td></tr>

<?php

if(isset($entity['aka']))
{
	print("<tr><td valign='top'>Is</td><td>");

	if(isset($entity['aka']['user']))
	{

		if(count($entity['aka']['user']) == 1)
			print($entity['aka']['user'][0]);
		else
		{
			print("<ul>");
			foreach ($entity['aka']['user'] as $value)
			{
				print('<li>' . $value . '</li>');
			}
			print("</ul>");
		}
	}

	print("</td></tr>");
}

if($entity['type']['user'] = "Character" || ($entity['type']['auto'] = "Character" && $entity['type']['user'] == null))
{
	print("<tr><td valign='top'>Involved In</td>\n<td>\n<ul>");

	if(isset($entity['involved']['user']))
	{
		foreach ($entity['involved']['user'] as $value)
		{
			print('<li>' . $value . '</li>');
		}
		print("</ul>\n</td>");
	}
	else
	{
		foreach ($entity['involved']['auto'] as $value)
		{
			print('<li>' . $value . '</li>');
		}
	}

	print("</ul>\n</td>");
}
else
{
	if(isset($entity['located-in']['user']))
	{
		print("<tr><td valign='top'>Located Within</td><td>");

		if(count($entity['located-in']['user']) == 1)
			print($entity['located-in']['user'][0]);
		else
		{
			print("<ul>");
			foreach ($entity['located-in']['user'] as $value)
			{
				print('<li>' . $value . '</li>');
			}
			print("</ul>");
		}
	}

	if(isset($entity['part-of']['user']))
	{
		print("<tr><td valign='top'>Is Part Of</td><td>");

		if(count($entity['part-of']['user']) == 1)
			print($entity['part-of']['user'][0]);
		else
		{
			print("<ul>");
			foreach ($entity['part-of']['user'] as $value)
			{
				print('<li>' . $value . '</li>');
			}
			print("</ul>");
		}
	}

	if(isset($entity['adjacent-to']))
	{
		print("<tr><td valign='top'>Located Next To</td><td>");

		if(count($entity['adjacent-to']['user']) == 1)
			print($entity['adjacent-to']['user'][0]);
		else
		{
			print("<ul>");
			foreach ($entity['adjacent-to']['user'] as $value)
			{
				print('<li>' . $value . '</li>');
			}
			print("</ul>");
		}
	}

}
?>
</tr>
</table>

<form name="navigateForm" method="post" action="entityviewer.php">
<input name="idhash" type="hidden" value="<?php print($userID); ?>" />
<p>Go To Character:
<select name="charNum">
<?php

	$charAutoNames = $query . "\n" . 'SELECT ?id ?name WHERE { GRAPH <' . $graphAuto . '> { ?id ?p omb:Character; foaf:name ?name } }' . "\n";

	$autoNames = $s->query($charAutoNames);

	$names = array();

	foreach($autoNames['result']['rows'] as $char)
	{
		$names[array_pop(explode("/",$char['id']))] = $char['name'];
	}

	$charUserNames = $query . "\n" . 'SELECT ?id ?name WHERE { GRAPH <' . $graphUser . '> { ?id ?p omb:Character; foaf:name ?name } }' . "\n";

	$userNames = $s->query($charUserNames);

	foreach($userNames['result']['rows'] as $char)
	{
		$names[array_pop(explode("/",$char['id']))] = $char['name'];
	}

	foreach($names as $id => $name)
	{
		print('<option value="' . $id . '">' . $name. '</option>');
	}
?>
</select>
<button name="gotoChar">Go</button></p>
<p>Go To Location:
<select name="locNum">
<?php

	$locAutoNames = $query . "\n" . 'SELECT ?id ?label WHERE { GRAPH <' . $graphAuto . '> { ?id ?p loc:Space; rdfs:label ?label } }' . "\n";

	$autoPlaces = $s->query($locAutoNames);

	$places = array();

	foreach($autoPlaces['result']['rows'] as $loc)
	{
		$names[array_pop(explode("/",$loc['id']))] = $loc['label'];
	}

	$locUserNames = $query . "\n" . 'SELECT ?id ?label WHERE { GRAPH <' . $graphUser . '> { ?id ?p loc:Space; rdfs:label ?label } }' . "\n";

	$userPlaces = $s->query($locUserNames);

	foreach($userPlaces['result']['rows'] as $loc)
	{
		$names[array_pop(explode("/",$loc['id']))] = $loc['label'];
	}

	foreach($places as $id => $label)
	{
		print('<option value="' . $id . '">' . $label . '</option>');
	}
?>
</select>
<button name="gotoLoc">Go</button></p>
</form>

<p><a href="characteredit.php?idhash=<?php print($userID); ?>">Character Editor</a></p>
<p class="selectedNav">Entity Viewer</p>
<p><a href="eventviewer.php?idhash=<?php print($userID); ?>">Event Viewer</a></p>

</body>
</html>
