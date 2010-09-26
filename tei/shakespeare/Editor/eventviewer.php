<?php

if (isset($_GET['idhash']))
{
	$userID = $_GET['idhash'];
}
else
{
	$userID = $_POST['idhash'];
}

require 'bc-fourstore-php/FourStore/FourStore_StorePlus.php';
require 'bc-fourstore-php/FourStore/Namespace.php';
require('shakespeare_utilities.php');

FourStore_Namespace::addW3CNamespace();
FourStore_Namespace::add('omb','http://purl.org/ontomedia/ext/common/being#');
FourStore_Namespace::add('ome','http://purl.org/ontomedia/core/expression#');
FourStore_Namespace::add('omj','http://purl.org/ontomedia/ext/events/travel#');
FourStore_Namespace::add('foaf','http://xmlns.com/foaf/0.1/');
$query = FourStore_Namespace::to_sparql();

$graphAuto = 'http://contextus.net/resource/midsum_night_dream/auto/';
$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $userID .  '/';

$s = new FourStore_StorePlus('http://contextus.net:7000/sparql/');

$event = array();

if(isset($_POST['previous']))
	$eventNum = $_POST['previousid'];
else if(isset($_POST['next']))
	$eventNum = $_POST['nextid'];
else if(isset($_POST['goto']) && $_POST['eventNum'] != "")
	$eventNum = $_POST['eventNum'];
else
{
	$queryAuto1 = $query . "\n" . 'SELECT DISTINCT ?id WHERE { { GRAPH ?g {?id a ome:Event}} {GRAPH <' . $graphAuto . '> { ?id ?p ?o } } } ORDER BY ?id LIMIT 1' . "\n";

	$rAuto = $s->query($queryAuto1);

	$eventNum = array_pop(explode("/",$rAuto['result']['rows'][0]['id']));
}


$queryAuto2 = $query . "\n" . 'SELECT ?p ?o WHERE { GRAPH <' . $graphAuto . '> { <' . $graphAuto . 'event/' . $eventNum . '> ?p ?o } }' . "\n";
$rAuto = $s->query($queryAuto2);

$involves_count = 0;
$refers_count = 0;

$event['involves'] = array();
$event['subject'] = array();
$event['refers'] = array();

$event['follows']['label'] = '-';
$event['follows']['id'] = '';

$event['precedes']['label'] = '-';
$event['precedes']['id'] = '';

foreach ($rAuto['result']['rows'] as $result)
{
	switch ($result['p'])
	{
		case "http://www.w3.org/2000/01/rdf-schema#label":
			$event['label']['auto'] = $result['o'];			
			break;

		case "http://www.w3.org/2000/01/rdf-schema#seeAlso":
			$event['text']['auto'] = $result['o'];
			break;

		case "http://purl.org/ontomedia/core/expression#refers-to":

			$queryAuto3 = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name } }' . "\n";

			$result3 = $s->query($queryAuto3);
			
			$event['refers'][$result['o']] ['auto'] = $result3['result']['rows'][0]['name'];
			
			if(isset($userID))
			{
				$queryAuto3u = $query . "\n" . 'SELECT ?name FROM <' . $graphUser . '> WHERE  {?id  ome:is-shadow-of <' . $result['o'] . '> ; foaf:name ?name }' . "\n";

				//print("<p>Refers Query: " . armourQuery($queryAuto3u) . "</p>");

				$result3u = $s->query($queryAuto3u);
				
				$event['refers'][$result['o']]['user'] = $result3u['result']['rows'][0]['name'];			
				
				//print("<p>Refers Result: " . $result3u['result']['rows'][0]['name'] . "</p>"); 
			}			

			break;

		case "http://purl.org/ontomedia/core/expression#involves":

			// NB. In theory this could be a person (foaf:name) or a location (rdf:label) but I don't think there are any locations as values currently so I am ignoring that possibility for now

			$queryAuto4b = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name } }' . "\n";

			$result4b = $s->query($queryAuto4b);

			$event['involves'][$result['o']]['auto'] = $result4b['result']['rows'][0]['name'];


			if(isset($userID))
			{
				$queryAuto4u = $query . "\n" . 'SELECT ?name FROM <' . $graphUser . '> WHERE  {?id  ome:is-shadow-of <' . $result['o'] . '> ; foaf:name ?name}' . "\n";

				//print("<p>Involves Query: " . armourQuery($queryAuto4u) . "</p>");

				$result4u = $s->query($queryAuto4u);
				
				$event['involves'][$result['o']]['user'] = $result4u['result']['rows'][0]['name'];			
				
				//print("<p>Involves Result: " . $result4u['result']['rows'][0]['label'] . "</p>"); 
			}				
			
			break;

		case "http://www.w3.org/1999/02/22-rdf-syntax-ns#type":
			$event['type']['auto'] = array_pop(explode("#",$result['o']));
			break;

		case "http://purl.org/ontomedia/core/expression#has-subject-entity":

			// Find if subject is Character or Group
			$queryAuto5 = $query . "\n" . 'SELECT ?type WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> rdf:type ?type } }' . "\n";

			$result5 = $s->query($queryAuto5);

			if ($result5['result']['rows'][0]['type'] == "http://purl.org/ontomedia/ext/common/being#Character")
			{
				$queryAuto5a = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name } }' . "\n";

				$result5a = $s->query($queryAuto5a);

				$event['subject'][$result['o']]['auto'] = $result5a['result']['rows'][0]['name'];
				
				if(isset($userID))
				{
					$queryAuto5au = $query . "\n" . 'SELECT ?name FROM <' . $graphUser . '> WHERE  {?id  ome:is-shadow-of <' . $result['o'] . '> ; foaf:name ?name}' . "\n";
	
					//print("<p>Subject Query: " . armourQuery($queryAuto5au) . "</p>");
	
					$result5au = $s->query($queryAuto5au);
					
					$event['subject'][$result['o']]['user'] = $result5au['result']['rows'][0]['name'];				
					
					//print("<p>Subject Result: " . $result5au['result']['rows'][0]['label'] . "</p>"); 
				}				
				
			}
			else if ($result5['result']['rows'][0]['type'] == "http://purl.org/ontomedia/ext/common/being#Group")
			{
				$queryAuto5b = $query . "\n" . 'SELECT ?o ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ome:contains ?o. ?o foaf:name ?name } }' . "\n";

				$result5b = $s->query($queryAuto5b);

				foreach($result5b['result']['rows'] as $name)
				{
					$event['subject'][$name['o']]['auto'] = $name['name'];
	
					if(isset($userID))
					{
						$queryAuto5bu = $query . "\n" . 'SELECT ?name FROM <' . $graphUser . '> WHERE  {?id  ome:is-shadow-of <' . $name['id'] . '> ; foaf:name ?name}' . "\n";
		
						//print("<p>Subject Query: " . armourQuery($queryAuto5bu) . "</p>");
		
						$result5bu = $s->query($queryAuto5bu);
						
						$event['subject'][$name['o']]['user'] = $result5bu['result']['rows'][0]['name'];				
						
						//print("<p>Subject Result: " . $result5bu['result']['rows'][0]['label'] . "</p>"); 
					}				
					
				}								
			}			

			break;

		case "http://signage.ecs.soton.ac.uk/ontologies/location#is-located-in":

			$queryAuto6 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";

			$result6 = $s->query($queryAuto6);

			$event['location']['auto'] = $result6['result']['rows'][0]['label'];

			if(isset($userID))
			{
				$queryAuto6u = $query . "\n" . 'SELECT ?label FROM <' . $graphUser . '> WHERE  {?id  ome:is-shadow-of <' . $result['o'] . '> ; rdfs:label ?label }' . "\n";

				//print("<p>Location Query: " . armourQuery($queryAuto6u) . "</p>");

				$result6u = $s->query($queryAuto6u);

				$event['location']['user'] = $result6u['result']['rows'][0]['label'];
				
				//print("<p>Location Result: " . $result6u['result']['rows'][0]['label'] . "</p>"); 
			}


			break;

		case "http://purl.org/ontomedia/core/expression#to":

			$queryAuto7 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";

			$result7 = $s->query($queryAuto7);

			$event['to']['auto'] = $result7['result']['rows'][0]['label'];
			
			if(isset($userID))
			{
				$queryAuto7u = $query . "\n" . 'SELECT ?label FROM <' . $graphUser . '> WHERE  {?id  ome:is-shadow-of <' . $result['o'] . '> ; rdfs:label ?label }' . "\n";

				//print("<p>To Query: " . armourQuery($queryAuto7u) . "</p>");

				$result7u = $s->query($queryAuto7u);

				$event['to']['user'] = $result7u['result']['rows'][0]['label'];
				
				//print("<p>To Result: " . $result7u['result']['rows'][0]['label'] . "</p>"); 
			}			

			break;

		case "http://purl.org/ontomedia/core/expression#from":

			$queryAuto8 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";

			$result8 = $s->query($queryAuto8);

			$event['from']['auto'] = $result8['result']['rows'][0]['label'];
			
			if(isset($userID))
			{
				$queryAuto8u = $query . "\n" . 'SELECT ?label FROM <' . $graphUser . '> WHERE  {?id  ome:is-shadow-of <' . $result['o'] . '> ; rdfs:label ?label }' . "\n";

				//print("<p>From Query: " . armourQuery($queryAuto8u) . "</p>");

				$result8u = $s->query($queryAuto8u);

				$event['from']['user'] = $result8u['result']['rows'][0]['label'];
				
				//print("From Result: " . $result8u['result']['rows'][0]['label'] . "</p>"); 
			}			

			break;

		case "http://purl.org/ontomedia/core/expression#precedes":

			$queryAuto9 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";

			$result9 = $s->query($queryAuto9);

			$event['precedes']['auto']['label'] = $result9['result']['rows'][0]['label'];
			$event['precedes']['auto']['id'] = array_pop(explode("/",$result['o']));
			break;

		case "http://purl.org/ontomedia/core/expression#follows":

			$queryAuto10 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";

			$result10 = $s->query($queryAuto10);

			$event['follows']['auto']['label'] = $result10['result']['rows'][0]['label'];
			$event['follows']['auto']['id'] = array_pop(explode("/",$result['o']));
			break;
	}

}

if(isset($event['text']['user']))
	$stage = retrieveStage($event['text']['user']);
else
	$stage = retrieveStage($event['text']['auto']);

printXMLHeaders();

function armourQuery ( $query )
{
	$p1 = preg_replace('/\</i','&lt;',$query);
	return preg_replace('/\>/i','&gt;',$p1);
}

function retrieveStage ( $xpointer )
{
	if($xpointer != "")
	{
		$sections = explode('#xpointer', $xpointer);

		$data = array();

		$url = $sections[0];
		$xpathQuery = substr($sections[1], 1, -1);

		if (preg_match('/ancestor::sp\/\/lb\[(.+)\]/i', $xpathQuery))
		{
			$xpathQuery = preg_replace('/ancestor::sp\/\/lb\[(.+)\]/i', '//lb[\1]/ancestor::sp', $xpathQuery);
		}

		$document = new DOMDocument();
		$document->load($url);

		$xpath = new DOMXPath($document);
		$nodelist = $xpath->query($xpathQuery);


		foreach ($nodelist as $node)
		{
			$data['stage'] = $node->nodeValue;
		}

		return $data;
	}
	else
		return "";
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>A Midsummer Night's Dream: Event</title>
	<link rel="stylesheet" href="fourstore_editor.css" type="text/css" media="all" title="Default styles" />
</head>
<body>

<?php printNavigationList('eventviewer.php', $userID) ?>

<?php //print_r($_POST); // print("<p>Query 1: " . armourQuery($queryAuto1) . "</p>"); ?>
<form name="navigateForm" method="post" action="eventviewer.php">

<input name="idhash" type="hidden" value="<?php print($userID); ?>" />
<input name="previousid" type="hidden" value="<?php print($event['follows']['auto']['id']); ?>" />
<input name="nextid" type="hidden" value="<?php print($event['precedes']['auto']['id']); ?>" />

<p><button name="previous" <?php if($event['follows']['auto']['id'] ==""){print ('disabled="true"');}?>>Previous</button><button name="next" <?php if($event['precedes']['auto']['id'] ==""){print ('disabled="true"');}?>>Next</button></p>

<p>Go To Event: <input name="eventNum" type="text"><button name="goto">Go</button></p>
</form>
<hr />
<table>
<tr><td>Event Number</td><td><?php print($eventNum);?></td></tr>
<tr><td>Event Type</td><td><?php
if(isset($entity['type']['user']))
	print($event['type']['user'] . " <span class='old'>[" . $event['type']['auto'] . "]</span>");
else
	print($event['type']['auto']);

?></td></tr>
<tr><td>Description</td><td><?php
if(isset($entity['label']['user']))
	print($event['label']['user'] . " <span class='old'>[" . $event['label']['auto'] . "]<span>");
else
	print($event['label']['auto']);

?></td></tr>
<tr><td valign='top'>Subject</td><td><?php

if(count($event['subject']) == 1)
{
/*	if(isset($event['subject'][0]['user']))
		print($event['subject'][0]['user'] . " <span class='old'>[" . $event['subject'][0]['auto'] . "]<span>");
	else
		print($event['subject'][0]['auto']);*/
		
	foreach ($event['subject'] as $id => $value)
	{
		if(isset($value['user']))
			print($value['user'] . " <span class='old'>[" . $value['auto'] . "]<span>");
		else
			print($value['auto']);
	}		
}
else
{
	print("<ul>");
	foreach ($event['subject'] as $id => $value)
	{
		if(isset($value['user']))
			print('<li>' . $value['user'] . " <span class='old'>[" . $value['auto'] . "]<span></li>");
		else
			print('<li>' . $value['auto'] . '</li>');
	}
	print("</ul>");
}

?></td></tr>
<?php
if(count($event['involves']) > 0)
{
	print("<tr><td valign='top'>Also Involves</td>\n<td>\n<ul>");

	foreach ($event['involves'] as $id => $value)
	{
		if(isset($value['user']))
			print('<li>' . $value['user'] . " <span class='old'>[" . $value['auto'] . "]<span></li>");
		else
			print('<li>' . $value['auto'] . '</li>');
	}
	
	print("</ul>\n</td>\n</tr>");
	
}
?>
<tr>
<?php

	if (isset($event['to']['auto']))
	{
		if(isset($event['to']['user']))
			print("<td>Arrive in</td><td>" . $event['to']['user'] . " <span class='old'>[" . $event['to']['auto'] . "]</span></td>");
		else
			print("<td>Arrive in</td><td>" . $event['to']['auto'] . "</td>");
	}
	else if (isset($event['from']['auto']))
	{
		if(isset($event['from']['user']))
			print("<td>Leave</td><td>" . $event['from']['user'] . " <span class='old'>[" . $event['from']['auto'] . "]</span></td>");
		else
			print("<td>Leave </td><td>" . $event['from']['auto'] . "</td>");
	}
	else
	{
		if(isset($event['location']['user']))
			print("<td>Location</td><td>" . $event['location']['user'] . " <span class='old'>[" . $event['location']['auto'] . "]</span></td>");
		else
			print("<td>Location</td><td>" . $event['location']['auto'] . "</td>");
	}
?>
</tr>

<?php

if(count($event['refers']) > 0)
{
	print("<tr><td valign='top'>Refers To</td>\n<td>\n<ul>");
	
	foreach ($event['refers'] as $id => $value)
	{
		if(isset($value['user']))
			print('<li>' . $value['user'] . " <span class='old'>[" . $value['auto'] . "]<span></li>");
		else
			print('<li>' . $value['auto'] . '</li>');
			
		//print("id: " . $id);
		//print_r($value);
	}
	print("</ul>\n</td>\n</tr>");
}
?>
<tr><td td valign='top'>See Text</td><td><span style="font-style: italic"><?php print($stage['stage']);?><span> (<a href="<?php print($event['text']['auto']);?>">Text</a>)<br />

<?php

$uri = $event['text']['auto'];

list($http, $web, $text, $doc, $act, $scene) = explode(":", $uri);

$actNum = array_pop(explode("=", $act));

$cutScene = array_shift(explode("#", $scene));

$sceneNum = array_pop(explode("=", $cutScene));

print("Act " . $actNum . ": Scene " . $sceneNum);

?>

</td></tr>
<tr><td></td><td><hr /></td></tr>
<tr><td>Previous Event</td><td><?php print($event['follows']['auto']['label']);?></td></tr>
<tr><td>Next Event</td><td><?php print($event['precedes']['auto']['label']);?></td></tr>

</table>

</body>
</html>
