<?php

require 'fourstore-php/Store.php';
require 'fourstore-php/Namespace.php';

require '/usr/share/php/libzend-framework-php/Zend/Loader/Autoloader.php';
spl_autoload_register(array('Zend_Loader_Autoloader', 'autoload'));

$prefixes = array();
$prefixes[] = 'PREFIX rdf:  <http://www.w3.org/1999/02/22-rdf-syntax-ns#>';
$prefixes[] = 'PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>';
$prefixes[] = 'PREFIX omb:  <http://purl.org/ontomedia/ext/common/being#>';
$prefixes[] = 'PREFIX ome:  <http://purl.org/ontomedia/core/expression#>';
$prefixes[] = 'PREFIX omj:  <http://purl.org/ontomedia/ext/events/travel#>';
$prefixes[] = 'PREFIX loc:  <http://signage.ecs.soton.ac.uk/ontologies/location#>';
$prefixes[] = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>';

$graphAuto = 'http://contextus.net/resource/midsum_night_dream/auto/';
$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $_GET['idhash'] .  '/';

$query = implode("\n", $prefixes);

$s = new FourStore_Store('http://contextus.net:7000/sparql/');

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

	//print("<p>Query 1: " . $queryAuto1 . "</p>");

	$rAuto = $s->select($queryAuto1);

	//print("<p>Result: '" . $rAuto[0]['id'] . "'</p>");

	$entityID = "character/" . array_pop(explode("/",$rAuto[0]['id']));

	//print("<p>Result: '" . $entityID . "'</p>");
}

$queryAuto2 = $query . "\n" . 'SELECT ?p, ?o WHERE { GRAPH <' . $graphAuto . '> { <' . $graphAuto . $entityID . '> ?p ?o } }' . "\n";

//print("<p>Query 2: " . $queryAuto2 . "</p>");

$rAuto = $s->select($queryAuto2);

$involved_count = 0;
$aka_count = 0;
$shadow_count = 0;
$loc_in_count = 0;
$part_of_count = 0;
$adj_to_count = 0;

foreach ($rAuto as $result)
{
	//print("<p>Checking property: " . $result['p'] . "</p>");
	switch ($result['p'])
	{
		case "http://xmlns.com/foaf/0.1/name":
			$entity['name'] = $result['o'];
			break;

		case "http://www.w3.org/2000/01/rdf-schema#label":
			$entity['name'] = $result['o'];
			break;

		case "http://purl.org/ontomedia/core/expression#involved-in":

			$queryAuto3 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

			$result3 = $s->select($queryAuto3);

			$entity['involved'][$involved_count] = $result3[0]['label'];
			$involved_count ++;
			break;

		case "http://www.w3.org/1999/02/22-rdf-syntax-ns#type":
			$entity['type'] = array_pop(explode("#",$result['o']));
			$type = $event['type'];
			break;

		case "http://purl.org/ontomedia/core/expression#is":

			if($type = "Character")
			{
				$queryAuto4a = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name . } }' . "\n";

				$result4a = $s->select($queryAuto4a);

				$entity['aka'][$aka_count] = $result4a[0]['name'];
			}
			else
			{
				$queryAuto4b = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

				$result4b = $s->select($queryAuto4b);

				$entity['aka'][$aka_count] = $result4b[0]['label'];
			}

			$aka_count ++;
			break;

		case "http://purl.org/ontomedia/core/expression#is-shadow-of":

			if($type = "Character")
			{
				$queryAuto5a = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name . } }' . "\n";

				$result5a = $s->select($queryAuto5a);

				$entity['shadows'][$shadow_count] = $result5a[0]['name'];
			}
			else
			{
				$queryAuto5b = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

				$result5b = $s->select($queryAuto5b);

				$entity['shadows'][$shadow_count] = $result5b[0]['label'];
			}
			$shadow_count++;
			break;

		case "http://signage.ecs.soton.ac.uk/ontologies/location#is-located-in":

			$queryAuto6 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

			$result6 = $s->select($queryAuto6);

			$entity['located-in'][$loc_in_count] = $result6[0]['label'];
			$loc_in_count++;
			break;

		case "http://signage.ecs.soton.ac.uk/ontologies/location#is-part-of":

			$queryAuto7 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

			$result7 = $s->select($queryAuto7);

			$entity['part-of'][$part_of_count] = $result7[0]['label'];
			$part_of_count++;
			break;

		case "http://signage.ecs.soton.ac.uk/ontologies/location#adjacent-to":

			$queryAuto8 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label . } }' . "\n";

			$result8 = $s->select($queryAuto8);

			$entity['adjacent-to'][$adj_to_count] = $result8[0]['label'];
			$adj_to_count++;
			break;

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
	<title>A Midsummer Night's Dream: Event</title>
	<link rel="stylesheet" href="fourstore_editor.css" type="text/css" media="all" title="Default styles" />
</head>
<body>

<?php //print_r($_POST); // print("<p>Query 1: " . armourQuery($queryAuto1) . "</p>"); ?>

<table>
<tr><td>Entity Number</td><td><?php print($entityID);?></td></tr>
<tr><td>Entity Name</td><td><?php print($entity['name']);?></td></tr>
<tr><td>Entity Type</td><td><?php print($entity['type']);?></td></tr>

<?php

if($entity['aka'] != null)
{
	print("<tr><td valign='top'>Is</td><td>");

	if(count($entity['aka']) == 1)
		print($entity['aka'][0]);
	else
	{
		print("<ul>");
		foreach ($entity['aka'] as $value)
		{
			print('<li>' . $value . '</li>');
		}
		print("</ul>");
	}

	print("</td></tr>");
}

if($entity['type'] == "Character")
{
	print("<tr><td valign='top'>Involved In</td>\n<td>\n<ul>");
	foreach ($entity['involved'] as $value)
	{
		print('<li>' . $value . '</li>');
	}
	print("</ul>\n</td>");
}
else
{
	if($entity['located-in'] != null)
	{
		print("<tr><td valign='top'>Located Within</td><td>");

		if(count($entity['located-in']) == 1)
			print($entity['located-in'][0]);
		else
		{
			print("<ul>");
			foreach ($entity['located-in'] as $value)
			{
				print('<li>' . $value . '</li>');
			}
			print("</ul>");
		}
	}

	if($entity['part-of'] != null)
	{
		print("<tr><td valign='top'>Is Part Of</td><td>");

		if(count($entity['part-of']) == 1)
			print($entity['part-of'][0]);
		else
		{
			print("<ul>");
			foreach ($entity['part-of'] as $value)
			{
				print('<li>' . $value . '</li>');
			}
			print("</ul>");
		}
	}

	if($entity['adjacent-to'] != null)
	{
		print("<tr><td valign='top'>Located Next To</td><td>");

		if(count($entity['adjacent-to']) == 1)
			print($entity['adjacent-to'][0]);
		else
		{
			print("<ul>");
			foreach ($entity['adjacent-to'] as $value)
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

<p>Go To Character:
<select name="charNum">
<?php

	$charNames = $query . "\n" . 'SELECT ?id, ?name WHERE { GRAPH <' . $graphAuto . '> { ?id ?p omb:Character; foaf:name ?name . } }' . "\n";

	$names = $s->select($charNames);

	foreach($names as $char)
	{
		print('<option value="' . array_pop(explode("/",$char['id'])) . '">' . $char['name'] . '</option>');
	}
?>
</select>
<button name="gotoChar">Go</button></p>
<p>Go To Location:
<select name="locNum">
<?php

	$locNames = $query . "\n" . 'SELECT ?id, ?label WHERE { GRAPH <' . $graphAuto . '> { ?id ?p loc:Space; rdfs:label ?label . } }' . "\n";

	$places = $s->select($locNames);

	foreach($places as $loc)
	{
		print('<option value="' . array_pop(explode("/",$loc['id'])) . '">' . $loc['label'] . '</option>');
	}
?>
</select>
<button name="gotoLoc">Go</button></p>
</form>
</body>
</html>
