<?php

require 'fourstore-php/Store.php';
require 'fourstore-php/Namespace.php';

require '/usr/share/php/libzend-framework-php/Zend/Loader/Autoloader.php';
spl_autoload_register(array('Zend_Loader_Autoloader', 'autoload'));

$prefixes = array();
$prefixes[] = 'PREFIX rdf:  <http://www.w3.org/1999/02/22-rdf-syntax-ns#>';
$prefixes[] = 'PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>';
$prefixes[] = 'PREFIX ds:   <http://contextus.net/resource/dark_star/>';
$prefixes[] = 'PREFIX dse:  <http://contextus.net/resource/dark_star/event>';
$prefixes[] = 'PREFIX omb:  <http://purl.org/ontomedia/ext/common/being#>';
$prefixes[] = 'PREFIX ome:  <http://purl.org/ontomedia/core/expression#>';
$prefixes[] = 'PREFIX foaf: <http://xmlns.com/foaf/0.1/>';

$graphAuto = 'http://contextus.net/resource/midsum_night_dream/auto/';
$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $_GET['idhash'] .  '/';

$query = implode("\n", $prefixes);
$queryAuto = $query . "\n" . 'SELECT ?id WHERE { GRAPH <' . $graphAuto . '> { ?id ?p ome:Social . } } ORDER BY ?id LIMIT 1' . "\n";

$s = new FourStore_Store('http://contextus.net:7000/sparql/');
$event = array();

$rAuto = $s->select($queryAuto);

$eventNum = array_pop(explode("/",$rAuto[0]['id']));

$queryAuto2 = $query . "\n" . 'SELECT ?p, ?o WHERE { GRAPH <' . $graphAuto . '> { <' . $graphAuto . 'event/' . $eventNum . '> ?p ?o . } }' . "\n";

$rAuto = $s->select($queryAuto2);

$involves_count = 0;
$refers_count = 0;

foreach ($rAuto as $result)
{
	switch ($result['p'])
	{
		case "http://www.w3.org/2000/01/rdf-schema#label":
			$event['label'] = $result['o'];
			break;
		case "http://www.w3.org/2000/01/rdf-schema#seeAlso":
			$event['text'] = $result['o'];
			break;	
		case "http://purl.org/ontomedia/core/expression#refers-to":		
			$event['refers'][$refers_count] = $result['o'];
			$refers_count ++;
			break;
		case "http://purl.org/ontomedia/core/expression#involves":		
			$event['involves'][$involves_count] = $result['o'];
			$involves_count ++;
			break;		
		case "http://www.w3.org/1999/02/22-rdf-syntax-ns#type":
			$event['type'] = $result['o'];
			break;	
		case "http://purl.org/ontomedia/core/expression#has-subject-entity":
			$event['subject'] = $result['o'];
			break;	
		case "http://signage.ecs.soton.ac.uk/ontologies/location#is-located-in":
			$event['location'] = $result['o'];
			break;	
		case "http://purl.org/ontomedia/core/expression#precedes":
			$event['precedes'] = $result['o'];
			break;	
		case "http://purl.org/ontomedia/core/expression#follows":
			$event['follows'] = $result['o'];
			break;				
	}

}


print('<' . '?xml version="1.1" encoding="iso-8859-1"?>' . "\n");
print('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n");
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>A Midsummer Night's Dream: Event</title>
	<link rel="stylesheet" href="fourstore_editor.css" type="text/css" media="all" title="Default styles" />
</head>
<body>

<p>Your ID is: <?php print($_GET['idhash']); ?>, and your event was <?php print($eventNum); ?></p>

<table>
<tr><td>Event</td><td><?php print($event['type']);?></td></tr>
<tr><td>Description</td><td><?php print($event['label']);?></td></tr>
<tr><td>Subject</td><td><?php print($event['subject']);?></td></tr>
<tr><td valign='top'>Also Involves</td>
<td>
<ul>
<?php
foreach ($event['involves'] as $value)
{
	print('<li>' . $value['value'] . '</li>');
}
?>
</ul>
</td>
<tr><td>Location</td><td><?php print($event['location']);?></td></tr>
<tr><td valign='top'>Refers To</td>
<td>
<ul>
<?php
foreach ($event['refers'] as $value)
{
	print('<li>' . $value['value'] . '</li>');
}
?>
</ul>
</td>
<tr><td>Previous</td><td><?php print($event['follows']);?></td></tr>
<tr><td>Next</td><td><?php print($event['precedes']);?></td></tr>
<tr><td>See Text</td><td><?php print($event['text']);?></td></tr>
</table>
</body>
</html>
