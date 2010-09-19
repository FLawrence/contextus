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
		//print('Setting: &lt;' . $subject . '&gt; &lt;http://xmlns.com/foaf/0.1/name&gt; "' . $object . '" . <br />');
		$s->set($graphUser, '<' . $subject . '> <http://xmlns.com/foaf/0.1/name> "' . $object . '" .');
	}
	else
	{
		//print('Adding: &lt;' . $subject . '&gt; &lt;http://xmlns.com/foaf/0.1/name&gt; "' . $object . '" . <br />');
		$s->add($graphUser, '<' . $subject . '> a <http://purl.org/ontomedia/ext/common/being#Character> .');
		$s->add($graphUser, '<' . $subject . '> <http://xmlns.com/foaf/0.1/name> "' . $object . '" .');
	}
}

header('Location: characteredit.php?idhash=' . $_POST['idhash']);
?>