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

$rUser = null;

if(isset($userID))
{
	$queryUser2 = $query . "\n" . 'SELECT ?p ?o WHERE { GRAPH <' . $graphUser . '> { <' . $graphUser . 'event/' . $eventNum . '> ?p ?o } }' . "\n";
	$rUser = $s->query($queryUser2);


	$err = $s->getErrors();
	if ($err) 
	{
		print_r($err);
		throw new Exception(print_r($err,true));
	}

}

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

			$event['refers']['auto'][$refers_count] = $result3['result']['rows'][0]['name'];
			$refers_count ++;
			
			break;

		case "http://purl.org/ontomedia/core/expression#involves":

			// NB. In theory this could be a person (foaf:name) or a location (rdf:label) but I don't think there are any locations as values currently so I am ignoring that possibility for now

			$queryAuto4b = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name } }' . "\n";

			$result4b = $s->query($queryAuto4b);

			$event['involves']['auto'][$involves_count] = $result4b['result']['rows'][0]['name'];
			$involves_count ++;
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

				$event['subject']['auto'][0] = $result5a['result']['rows'][0]['name'];
			}
			else if ($result5['result']['rows'][0]['type'] == "http://purl.org/ontomedia/ext/common/being#Group")
			{
				$queryAuto5b = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ome:contains ?o. ?o foaf:name ?name } }' . "\n";

				$result5b = $s->query($queryAuto5b);

				$person_count = 0;

				foreach($result5b['result']['rows'] as $name)
				{
					$event['subject']['auto'][$person_count] = $name['name'];
					$person_count++;
				}
			}

			break;

		case "http://signage.ecs.soton.ac.uk/ontologies/location#is-located-in":

			$queryAuto6 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";

			$result6 = $s->query($queryAuto6);

			$event['location']['auto'] = $result6['result']['rows'][0]['label'];
			break;

		case "http://purl.org/ontomedia/core/expression#to":

			$queryAuto7 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";

			$result7 = $s->query($queryAuto7);

			$event['to']['auto'] = $result7['result']['rows'][0]['label'];

			break;

		case "http://purl.org/ontomedia/core/expression#from":

			$queryAuto8 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";

			$result8 = $s->query($queryAuto8);

			$event['from']['auto'] = $result8['result']['rows'][0]['label'];

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

if($rUser != null)
{
	foreach ($rUser['result']['rows'] as $result)
	{
		switch ($result['p'])
		{
			case "http://www.w3.org/2000/01/rdf-schema#label":
				$event['label']['user'] = $result['o'];
				break;
	
			case "http://www.w3.org/2000/01/rdf-schema#seeAlso":
				$event['text']['user'] = $result['o'];
				break;
	
			/*case "http://purl.org/ontomedia/core/expression#refers-to":
	
				$queryAuto3 = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name } }' . "\n";
	
				$result3 = $s->query($queryAuto3);
	
				$event['refers']['user'][$refers_count] = $result3['result']['rows'][0]['name'];
				$refers_count ++;
				break;
	
			case "http://purl.org/ontomedia/core/expression#involves":
	
				// NB. In theory this could be a person (foaf:name) or a location (rdf:label) but I don't think there are any locations as values currently so I am ignoring that possibility for now
	
				$queryAuto4b = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name } }' . "\n";
	
				$result4b = $s->query($queryAuto4b);
	
				$event['involves']['user'][$involves_count] = $result4b['result']['rows'][0]['name'];
				$involves_count ++;
				break;
			*/
	
			case "http://www.w3.org/1999/02/22-rdf-syntax-ns#type":
				$event['type']['user'] = array_pop(explode("#",$result['o']));
				break;
	
			/*
			case "http://purl.org/ontomedia/core/expression#has-subject-entity":
	
				// Find if subject is Character or Group
				$queryAuto5 = $query . "\n" . 'SELECT ?type WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> rdf:type ?type } }' . "\n";
	
				$result5 = $s->query($queryAuto5);
	
				if ($result5['result']['rows'][0]['type'] == "http://purl.org/ontomedia/ext/common/being#Character")
				{
					$queryAuto5a = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; foaf:name ?name } }' . "\n";
	
					$result5a = $s->query($queryAuto5a);
	
					$event['subject']['user'][0] = $result5a['result']['rows'][0]['name'];
				}
				else if ($result5['result']['rows'][0]['type'] == "http://purl.org/ontomedia/ext/common/being#Group")
				{
					$queryAuto5b = $query . "\n" . 'SELECT ?name WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ome:contains ?o. ?o foaf:name ?name } }' . "\n";
	
					$result5b = $s->query($queryAuto5b);
	
					$person_count = 0;
	
					foreach($result5b['result']['rows'] as $name)
					{
						$event['subject']['user'][$person_count] = $name['name'];
						$person_count++;
					}
				}
	
				break;
			*/
	
			case "http://signage.ecs.soton.ac.uk/ontologies/location#is-located-in":
	
				$queryAuto6 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";
	
				$result6 = $s->query($queryAuto6);
	
				$event['location']['user'] = $result6['result']['rows'][0]['label'];
				break;
	
			case "http://purl.org/ontomedia/core/expression#to":
	
				$queryAuto7 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";
	
				$result7 = $s->query($queryAuto7);
	
				$event['to']['user'] = $result7['result']['rows'][0]['label'];
	
				break;
	
			case "http://purl.org/ontomedia/core/expression#from":
	
				$queryAuto8 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";
	
				$result8 = $s->query($queryAuto8);
	
				$event['from']['user'] = $result8['result']['rows'][0]['label'];
	
				break;
	
			case "http://purl.org/ontomedia/core/expression#precedes":
	
				$queryAuto9 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";
	
				$result9 = $s->query($queryAuto9);
	
				$event['precedes']['user']['label'] = $result9['result']['rows'][0]['label'];
				$event['precedes']['user']['id'] = array_pop(explode("/",$result['o']));
				break;
	
			case "http://purl.org/ontomedia/core/expression#follows":
	
				$queryAuto10 = $query . "\n" . 'SELECT ?label WHERE { GRAPH <' . $graphAuto . '> { <' . $result['o'] . '> ?p ?o; rdfs:label ?label } }' . "\n";
	
				$result10 = $s->query($queryAuto10);
	
				$event['follows']['user']['label'] = $result10['result']['rows'][0]['label'];
				$event['follows']['user']['id'] = array_pop(explode("/",$result['o']));
				break;
		}	
	}
	
}

if(isset($event['text']['user']))
	$stage = retrieveStage($event['text']['user']);
else
	$stage = retrieveStage($event['text']['auto']);

print('<' . '?xml version="1.1" encoding="iso-8859-1"?>' . "\n");
print('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n");


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

<p class="navbar"><a href="characteredit.php?idhash=<?php print($userID); ?>">Character Editor</a> &nbsp; <a href="entityviewer.php?idhash=<?php print($userID); ?>">Entity Viewer</a> &nbsp; <span class="selectedNav">Event Viewer</span> &nbsp; <a href="locationedit.php?idhash=<?php print($userID); ?>">Location Editor</a></p>

<?php //print_r($_POST); // print("<p>Query 1: " . armourQuery($queryAuto1) . "</p>"); ?>
<form name="navigateForm" method="post" action="eventviewer.php">

<input name="idhash" type="hidden" value="<?php print($userID); ?>" />
<input name="previousid" type="hidden" value="<?php print($event['follows']['id']); ?>" />
<input name="nextid" type="hidden" value="<?php print($event['precedes']['id']); ?>" />

<p><button name="previous" <?php if($event['follows']['id'] ==""){print ('disabled="true"');}?>>Previous</button><button name="next" <?php if($event['precedes']['id'] ==""){print ('disabled="true"');}?>>Next</button></p>

<p>Go To Event: <input name="eventNum" type="text"><button name="goto">Go</button></p>
</form>

<table>
<tr><td>Event Number</td><td><?php print($eventNum);?></td></tr>
<tr><td>Event Type</td><td><?php
if(isset($entity['type']['user']))
	print($entity['type']['user'] . " <span class='old'>[" . $entity['type']['auto'] . "]</span>");
else
	print($entity['type']['auto']);

?></td></tr>
<tr><td>Description</td><td><?php
if(isset($entity['label']['user']))
	print($entity['label']['user'] . " <span class='old'>[" . $entity['label']['auto'] . "]<span>");
else
	print($entity['label']['auto']);

?></td></tr>
<tr><td valign='top'>Subject</td><td><?php

if(count($event['subject']['auto']) == 1)
	print($event['subject']['auto'][0]);
else
{
	print("<ul>");
	foreach ($event['subject']['auto'] as $value)
	{
		print('<li>' . $value . '</li>');
	}
	print("</ul>");
}

?></td></tr>
<tr><td valign='top'>Also Involves</td>
<td>
<ul>
<?php
foreach ($event['involves']['auto'] as $value)
{
	print('<li>' . $value . '</li>');
}
?>
</ul>
</td>
<tr>
<?php
	if ($event['to']['auto'] != "")
	{
		if(isset($event['to']['user']))
			print("<td>Arrive in</td><td>" . $event['to']['user'] . " <span class='old'>[" . $entity['to']['auto'] . "]</span></td>");
		else
			print("<td>Arrive in</td><td>" . $event['to']['auto'] . "</td>");
	}
	else if ($event['from']['auto'] != "")
	{
		if(isset($event['from']['user']))
			print("<td>Arrive in</td><td>" . $event['from']['user'] . " <span class='old'>[" . $entity['from']['auto'] . "]</span></td>");
		else	
			print("<td>Leave </td><td>" . $event['from']['auto'] . "</td>");
	}
	else
	{
		if(isset($event['location']['user']))
			print("<td>Arrive in</td><td>" . $event['location']['user'] . " <span class='old'>[" . $entity['location']['auto'] . "]</span></td>");
		else	
			print("<td>Location</td><td>" . $event['location']['auto'] . "</td>");
	}
?>
</tr>
<tr><td valign='top'>Refers To</td>
<td>
<ul>
<?php
if(isset($event['refers']['user']))
{
}
else
{
	foreach ($event['refers']['auto'] as $value)
	{
		print('<li>' . $value . '</li>');
	}
}
?>
</ul>
</td>
<tr><td>Previous Event</td><td><?php print($event['follows']['auto']['label']);?></td></tr>
<tr><td>Next Event</td><td><?php print($event['precedes']['auto']['label']);?></td></tr>
<tr><td>See Text</td><td><span style="font-style: italic"><?php print($stage['stage']);?><span> (<a href="<?php print($event['text']);?>">Text</a>)</td></tr>
</table>

</body>
</html>
