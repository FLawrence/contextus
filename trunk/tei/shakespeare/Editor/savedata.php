<?php

require 'fourstore-php/Store.php';
require 'fourstore-php/Namespace.php';

require '/usr/share/php/libzend-framework-php/Zend/Loader/Autoloader.php';
spl_autoload_register(array('Zend_Loader_Autoloader', 'autoload'));

$changes = explode("|", $_POST['alteredData']);

$userGraphURL = 'http://contextus.net/resource/midsum_night_dream/' . $_POST['idhash'] .  '/';
$autoGraphURL = 'http://contextus.net/resource/midsum_night_dream/data/';

$queryUser = 'SELECT ?s, ?p, ?o WHERE { GRAPH <' . $userGraphURL . '> { ?s ?p ?o } }' . "\n";

$s = new FourStore_Store('http://contextus.net:7000/sparql/');

$userGraph = array();

$results = $s->select($queryUser);

foreach ($results as $result)
{
	addTripleToGraph($userGraph, $result);
}

$results = array();

foreach ($changes as $change)
{
	$parts = split("=", $change);

	if (count($parts) != 2) continue;

	$originalSubject = $autoGraphURL . 'character/' . $parts[0];
	$subject = $userGraphURL . 'character/' . $parts[0];
	$object = $parts[1];

	addTripleToGraph($userGraph, makeTriple($subject, 'a' , 'http://purl.org/ontomedia/ext/common/being#Character'));
	addTripleToGraph($userGraph, makeTriple($subject, 'http://xmlns.com/foaf/0.1/name' ,$object));
	addTripleToGraph($userGraph, makeTriple($subject, 'http://purl.org/ontomedia/core/expression#is-shadow-of' , $originalSubject));
}

$results['Deleting Graph'] = $s->delete($userGraphURL);

$allTriples = "";

foreach ($userGraph as $triple)
{
	$allTriples .= makeTurtleFromTriple($triple) . "\n";
}
$results['Adding All Triples'] = $s->add($userGraphURL, $allTriples);

header('Location: characteredit.php?idhash=' . $_POST['idhash']);
exit(0);

print('<' . '?xml version="1.1" encoding="iso-8859-1"?>' . "\n");
print('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n");

function addTripleToGraph ( &$graph, $triple )
{
	$graph[md5($triple['s'] . $triple['p'])] = $triple;
}

function makeTriple ( $subject, $predicate, $object )
{
	$triple = array( 's' => $subject, 'p' => $predicate, 'o' => $object);
	return $triple;
}

function makeTurtleFromTriple ( $triple )
{
	$turtle = "";

	if (substr($triple['s'], 0 , 7) == 'http://')
		$turtle .= '<' . $triple['s'] . '> ';
	else
		$turtle .= $triple['s'] . ' ';

	if (substr($triple['p'], 0 , 7) == 'http://')
		$turtle .= '<' . $triple['p'] . '> ';
	else
		$turtle .= $triple['p'] . ' ';

	if (substr($triple['o'], 0 , 7) == 'http://')
		$turtle .= '<' . $triple['o'] . '> .';
	else
		$turtle .= '"' . $triple['o'] . '" .';

	return $turtle;
}

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<title>Save Results</title>
</head>
<body>

<p>Continue to <a href="<?php print('characteredit.php?idhash=' . $_POST['idhash']); ?>">Editor</a></p>

<?php
	foreach ($results as $title => $r)
	{
		print("<h1>" . $title . "</h1>");
		print("<p>");
		print_r($r);
		print("</p>");
	}
?>

</body>
</html>