<?php

require 'fourstore-php/Store.php';
require 'fourstore-php/Namespace.php';

require '/usr/share/php/libzend-framework-php/Zend/Loader/Autoloader.php';
spl_autoload_register(array('Zend_Loader_Autoloader', 'autoload'));

//print_r($_POST);
//print("<br /><br />");

$changes = explode("|", $_POST['alteredData']);

$graphUser = 'http://contextus.net/resource/midsum_night_dream/' . $_POST['idhash'] .  '/';

$s = new FourStore_Store('http://contextus.net:7000/sparql/');

$results = array();

foreach ($changes as $change)
{
	$parts = split("=", $change);

	if (count($parts) != 2) continue;

	$subject = $graphUser . 'character/' . $parts[0];
	$object = $parts[1];

	//print('Asking: &lt;' . $subject . '&gt; &lt;http://xmlns.com/foaf/0.1/name&gt; ?o <br />');
	$r = $s->ask('ASK WHERE {<' . $subject . '> <http://xmlns.com/foaf/0.1/name> ?o }');

	if ($r == true)
	{
		$r = $s->set($graphUser, '<' . $subject . '> <http://xmlns.com/foaf/0.1/name> "' . $object . '" .');
		$r = $s->add($graphUser, '<' . $subject . '> a <http://purl.org/ontomedia/ext/common/being#Character> .');
		$results['Setting: &lt;' . $subject . '&gt; &lt;http://xmlns.com/foaf/0.1/name&gt; "' . $object . '"'] = $r;
	}
	else
	{
		$r = $s->add($graphUser, '<' . $subject . '> a <http://purl.org/ontomedia/ext/common/being#Character> .');
		$r = $s->add($graphUser, '<' . $subject . '> <http://xmlns.com/foaf/0.1/name> "' . $object . '" .');
		$results['Adding: &lt;' . $subject . '&gt; &lt;http://xmlns.com/foaf/0.1/name&gt; "' . $object . '"'] = $r;
	}
}

//header('Location: characteredit.php?idhash=' . $_POST['idhash']);
print('<' . '?xml version="1.1" encoding="iso-8859-1"?>' . "\n");
print('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n");
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