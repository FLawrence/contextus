<?php

ini_set('auto_detect_line_endings', true);

$files[] = "TitusAdronicus.xml";
$files[] = "Hamlet.xml";
$files[] = "MidsummerNightsDream.xml";
$files[] = "Romeo&Juliet.xml";
$files[] = "MuchAdoAboutNothing.xml";
$files[] = "TheTempest.xml";
$files[] = "HenryV.xml";

$text = "";

foreach($files as $filename)
{
	
	$text .= substr($filename, 0, -4) . "\n\n";
	
	$exits = "";
	$entrances = "";
	$place = "";
	$other = "";
	
	$f = fopen( $filename, 'r' );
	
	while(!feof($f))
	{
		$line = fgets($f);	
		
		if(strpos($line, '<stage type="entrance"'))
			$entrances .= trim($line) . "\n";		
		elseif(strpos($line, '<stage type="exit"'))
			$exits .= trim($line) . "\n";			
		elseif(strpos($line, '<stage type="location"'))
			$place .= trim($line) . "\n";	
		elseif(strpos($line, '<stage'))
			$other .= trim($line) . "\n";			
	}

	$text .= "Entrances\n" . $entrances;
	$text .= "\nExits\n" . $exits;	
	$text .= "\nLocations\n" . $place;
	$text .= "\nOther\n" . $other;
	
	$text .= "\n";
	
	fclose($f);
}

$output =  "stage_directions.txt";

$out = fopen($output, 'w' );
fputs($out, $text);
fclose($out);

?>