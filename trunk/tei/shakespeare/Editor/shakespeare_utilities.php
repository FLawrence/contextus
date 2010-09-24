<?php

function printXMLHeaders ( )
{
	print('<' . '?xml version="1.1" encoding="iso-8859-1"?>' . "\n");
	print('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">' . "\n");
}


function printNavigationList ( $current, $userID )
{
	$navigationArray = array('characteredit.php' => 'Character Editor',
							 'entityviewer.php' => 'Entity Viewer',
							 'eventviewer.php' => 'Event Viewer',
							 'locationedit.php' => 'Location Editor');

	print('<ul id="navigationList">' . "\n");
	foreach ($navigationArray as $url => $label)
	{
		if ($current == $url)
			print('   <li id="selectedNav">' . $label . '</li>' . "\n");
		else
			print('   <li><a href="' . $url .  '?idhash=' . $userID . '">' . $label . '</a></li>' . "\n");
	}
	print('   <li><a href="index.php">Logout</a></li>
	print('</ul>' . "\n");
}

?>