<?php
require 'bc-fourstore-php/FourStore/FourStore_Store.php';
require 'bc-fourstore-php/FourStore/FourStore_StorePlus.php';

$adds = explode("\n", $_POST['addedTriples']);
$changes = explode("\n", $_POST['changedTriples']);
$deletes = explode("\n", $_POST['deletedTriples']);

$baseURL = 'http://contextus.net/resource/midsum_night_dream/';
$autoGraphURL = $baseURL . 'auto/';
$userGraphURL = $baseURL . $_POST['idhash'] .  '/';

$queryUser = 'SELECT ?s ?p ?o FROM <' . $userGraphURL . '> WHERE { ?s ?p ?o }' . "\n";

$s = new FourStore_StorePlus('http://contextus.net:7000/sparql/');
$sWrite = new FourStore_Store('http://contextus.net:7000/sparql/');

$userGraph = array();

$results = $s->query($queryUser);

$err = $s->getErrors();
if ($err) {
	print_r($err);
	throw new Exception(print_r($err,true));
}

foreach ($results['result']['rows'] as $result)
{
	addTripleToGraph($userGraph, $result);
}

$results = array();

if ($_POST['saveType'] == 'character')
	$continueURL = 'exp_characteredit.php?idhash=' . $_POST['idhash'];
else if ($_POST['saveType'] == 'location')
{
	$continueURL = 'exp_locationedit.php?idhash=' . $_POST['idhash'];
}



foreach ($deletes as $delete)
{
	$delete = trim($delete);
	if ($delete == '') continue;

	list($s, $p, $o) = explode('|', $delete, 3);
	deleteTripleFromGraph($userGraph, makeTriple($s, $p, $o));
}

foreach ($adds as $add)
{
	$add = trim($add);
	if ($add == '') continue;

	list($s, $p, $o) = explode('|', $add, 3);
	addTripleToGraph($userGraph, makeTriple($s, $p, $o));
}

foreach ($changes as $change)
{
	$change = trim($change);
	if ($change == '') continue;

	list($s, $p, $o, $originalO) = explode('|', $change, 4);
	deleteTripleFromGraph($userGraph, makeTriple($s, $p, $originalO));
	addTripleToGraph($userGraph, makeTriple($s, $p, $o));

//	if ($_POST['saveType'] == 'character')
//	{
//		$originalS = $s;
//		$s = str_replace($autoGraphURL, $userGraphURL, $s);
//
//		if ($s != $originalS)
//		{
//			addTripleToGraph($userGraph, makeTriple($s, 'a' , 'http://purl.org/ontomedia/ext/common/being#Character'));
//			addTripleToGraph($userGraph, makeTriple($s, 'http://purl.org/ontomedia/core/expression#is-shadow-of' , $originalS));
//		}
//
//		deleteTripleFromGraph($userGraph, makeTriple($s, $p, $originalO));
//		addTripleToGraph($userGraph, makeTriple($s, $p, $o));
//	}
//	else if ($_POST['saveType'] == 'location')
//	{
//		deleteTripleFromGraph($userGraph, makeTriple($s, $p, $originalO));
//		addTripleToGraph($userGraph, makeTriple($s, $p, $o));
//		if (substr($s, 0, strlen($autoGraphURL)) == $autoGraphURL)
//		{
//			$newS = str_replace($autoGraphURL, $userGraphURL, $s);
//			addTripleToGraph($userGraph, makeTriple($newS, 'a' , 'http://signage.ecs.soton.ac.uk/ontologies/location#Space'));
//			addTripleToGraph($userGraph, makeTriple($newS, 'http://purl.org/ontomedia/core/expression#is-shadow-of' , $s));
//			addTripleToGraph($userGraph, makeTriple($newS, 'http://www.w3.org/2000/01/rdf-schema#label' ,$o));
//
//		}
//		else
//		{
//			addTripleToGraph($userGraph, makeTriple($s, $p, $o));
//		}
//	}
}

$results['Deleting Graph'] = $sWrite->delete($userGraphURL);

$allTriples = "";

foreach ($userGraph as $triple)
{
	$allTriples .= makeTurtleFromTriple($triple) . "\n";
}
$results['Adding All Triples'] = $sWrite->add($userGraphURL, $allTriples);

header('Location: ' . $continueURL);
exit(0);

print('<' . '?xml version="1.1" encoding="iso-8859-1"?>' . "\n");
print('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n");

function addTripleToGraph ( &$graph, $triple )
{
	$graph[md5($triple['s'] . $triple['p']) . $triple['o']] = $triple;
}

function deleteTripleFromGraph ( &$graph, $triple )
{
	unset($graph[md5($triple['s'] . $triple['p']) . $triple['o']]);
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

<p>Continue to <a href="<?php print($continueURL); ?>">Editor</a></p>

<?php
	foreach ($results as $title => $r)
	{
		print("<h1>" . $title . "</h1>");
		print("<p>");
		print_r($r);
		print("</p>");
	}

	print ('<p>' . $allTriples . '</p>');

	print ('<p style="color: red;">' . $_POST['addedTriples'] . '</p>');
	print ('<p style="color: green;">' . $_POST['changedTriples'] . '</p>');
	print ('<p style="color: blue;">' . $_POST['deletedTriples'] . '</p>');

?>

</body>
</html>