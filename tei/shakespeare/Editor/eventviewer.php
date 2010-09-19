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
$queryAuto = $query . "\n" . 'SELECT ?id WHERE { GRAPH <' . $graphAuto . '> { ?id ?p ome:Event . } } ORDER BY ?id LIMIT 1' . "\n";

$s = new FourStore_Store('http://contextus.net:7000/sparql/');
$events = array();

$rAuto = $s->select($queryAuto);

$eventNum = array_pop(explode("/",$rAuto[0]['id']));

$queryAuto = $query . "\n" . 'SELECT ?p, ?o WHERE { GRAPH <' . $graphAuto . '> { <' . $graphAuto . 'event/' . $eventNum . '> ?p ?o . } }' . "\n";

$rAuto = $s->select($queryAuto);

$count = 0;
foreach ($rAuto as $result)
{
	$events[$count]['link'] = $result['p'];
	$events[$count]['value'] = $result['o'];
	$count++;
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

<p>Your ID is: <?php print($_GET['idhash']); ?></p>

<table>
<?php

foreach ($events as $count => $values)
{
	print('<tr><td>' . $values['link'] . '</td><td>' . $values['value'] . '</td></tr>');
}

?>
</table>

</body>
</html>
