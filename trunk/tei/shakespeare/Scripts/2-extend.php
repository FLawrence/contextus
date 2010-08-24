<?php

ini_set('auto_detect_line_endings', true);

//$filename = "TitusAdronicus.xml";
//$filename = "Hamlet.xml";
//$filename = "MidsummerNightsDream.xml";
//$filename = "Romeo&Juliet.xml";
//$filename = "MuchAdoAboutNothing.xml";
//$filename = "TheTempest.xml";
//$filename = "HenryV.xml";

$files["ta"] = "TitusAdronicus.xml";
$files["ham"] = "Hamlet.xml";
$files["mnd"] = "MidsummerNightsDream.xml";
$files["rj"] = "Romeo&Juliet.xml";
$files["maan"] = "MuchAdoAboutNothing.xml";
$files["tt"] = "TheTempest.xml";
$files["hal5"] = "HenryV.xml";

//$filename = "";
//$namespace = "XX";

/* Possible future refinement - look for any word(s) in all caps and check them against the names spotted by that point. Won't be able to work out the id automatically but will at least be able to tag them and the link the two ids afterwards */

foreach($files as $namespace => $filename)
{
	print("Generating " . $filename . "\n");
	
	$f = fopen( $filename, 'r' );
	
	//$headings = fgets($f);
	
	/* $text = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; 
	*/
	
	$text = "";
	
	$characters = array();
	$characters_alt = array();
	
	while(!feof($f))
	{
		$line = fgets($f);	
		$alt_line = $line;
		
		/* GET LIST OF NAMES */	
		
		if(substr_count($line, "</role>") == 1)
		{
			$startpos = strripos($line, "[" . $namespace . ":") + strlen($namespace) + 2;
			$endpos = stripos($line, "]");
			
			$length = $endpos - $startpos;
			
			$name = substr($line, $startpos, $length);		
			$name_with_space = str_replace("_", " ", $name);
			
			//print($name_with_space);
			
			//$pattern = '/xml:id=\"(tit\-[0-9]+)\"/'
			$pattern = '/xml:id=\"([^"]+)\"/';
			
			preg_match_all($pattern, $line, $matchArray, PREG_PATTERN_ORDER);
			
			$id = $matchArray[1][0];
			
			//print_r($id . "\n");
			
			$characters[trim($name_with_space)] = $id;
			
			$name_replace = strtolower($name);
			$name_replace = ucwords($name_replace);
			
			$name_with_space_replace = strtolower($name_with_space);
			$name_with_space_replace = ucwords($name_with_space_replace);
			
			$alt_line = str_replace($name, $name_replace, $line);
			$alt_line = str_replace($name_with_space, $name_with_space_replace, $line);
			
			//print($alt_line);
		}
		
		foreach($characters as $name => $id)
		{
			
			
			//IF ALL UPPER CASE
			$pattern = "/^[A-Z\s]+$/";
			if(preg_match($pattern, $name))
			{
				//print("Matching 1 with " . $name  . "\n");
			
				$parts = explode(" ", $name);
				
				$name_lower = strtolower($name);
				$name_lower = ucwords($name_lower);
				$characters_alt[trim($name_lower)] = $id;
				
				if($filename == "HenryV.xml")
				{
					if(count($parts) == 3)
					{
						if($parts[1] == "OF")
						{
							$name_lower = strtolower($parts[2]);
							$name_lower = ucfirst($name_lower);	
							
							$characters_alt[trim($parts[2])] = $id;
							$characters_alt[trim($name_lower)] = $id;						
						}
					}
					else
					{
						$last_pos = count($parts) - 1;
						
						$name_lower = strtolower($parts[$last_pos]);
						$name_lower = ucfirst($name_lower);	
						
						$characters_alt[trim($parts[$last_pos])] = $id;
						$characters_alt[trim($name_lower)] = $id;					
					}
				}
				elseif($filename == "MuchAdoAboutNothing.xml" && count($parts) > 1) //ADD SECOND NAME
				{
					$characters_alt[trim($parts[1])] = $id;
					
					$name_lower = strtolower($parts[1]);
					$name_lower = ucfirst($name_lower);
					$characters_alt[trim($name_lower)] = $id;				
				}
				elseif($filename != "Romeo&Juliet.xml") //ADD FIRST NAME
				{	
					//print("1a. Adding " . $name_lower . "\n");
					//$characters_alt[trim($parts[0])] = $id;
					
					$name_lower = strtolower($parts[0]);
					$name_lower = ucfirst($name_lower);
					$characters_alt[trim($name_lower)] = $id;
				}
				
				//print("1b. Adding " . $name_lower . "\n");
			}
			elseif(preg_match("/^[A-Z]{2,}(\s[A-Za-z]+)+$/", $name))
			{
				//IF FIRST WORD IN CAPS AND LONGER THAT ONE LETTER & REST NOT ALL CAPS
				
				//print("Matching 2 with " . $name  . "\n");
				
				$parts = explode(" ", $name);
				$num = count($parts);
				
				if($num == 2) //TITUS AD - ADD (LOWER CASE FIRST WORD TITLE CASE SECOND WORD)
				{
					$first_name_lower = strtolower($parts[0]);
					$second_name_lower = strtolower($parts[1]);
					$second_name_lower = ucfirst($second_name_lower);
					
					$converted_name = $first_name_lower . " " . $second_name_lower;
					
					$characters_alt[trim($converted_name)] = $id;
					
					//print("2. Adding " . $converted_name . "\n");
					
				}
				
				if($num == 3) //Henry V
				{
					$first_name_lower = strtolower($parts[0]);
					$first_name_lower = ucfirst($first_name_lower);	
					
					$characters_alt[trim($parts[0])] = $id;
					$characters_alt[trim($first_name_lower)] = $id;
					
					//print("Adding Charles");
				}
				
				if($num == 4) //Henry V
				{
					//SECOND PART - UPPER & TITLE CASES
					$second_name_lower = strtolower($parts[1]);
					$second_name_lower = ucfirst($second_name_lower);	
					
					$characters_alt[trim($parts[1])] = $id;
					$characters_alt[trim($second_name_lower)] = $id;
					
					
					//FIRST PART & SECOND PART - UPPER & TITLE CASES
					$first_name_lower = strtolower($parts[0]);
					$first_name_lower = ucfirst($first_name_lower);
					
					$converted_name = $first_name_lower . " " . $second_name_lower;
					
					$characters_alt[trim($converted_name)] = $id;
					
					$first_names = $parts[0] . " " . $parts[1];
					
					$characters_alt[trim($first_names)] = $id;
					
					//print("Adding Henry");
	
				}
				
				//$characters[$parts[$num-1]] = $id;
				
			}
			else
			{
				//print("Matching 3 with " . $name . "\n");
			
				$parts = explode(" ", $name);
				$num = count($parts);
				
				if($filename == "TitusAdronicus.xml")
				{
					if($num == 2)
					{
						$characters_alt[trim($parts[1])] = $id;
						
						//print("3a. Adding " . $parts[1] . " from " . $name . "\n");
					}
					
					if($num == 3)
					{
						$characters_alt[trim($parts[0])] = $id;
						
						//print("3b. Adding " . $parts[0] . "\n");
					}
				}
				
				if($filename == "Hamlet.xml")
				{
					if($num == 3 && strtolower($parts[1]) == "and")
					{
						$characters_alt[trim($parts[0])] = strtolower($parts[0]);
						$characters_alt[trim($parts[2])] = strtolower($parts[2]);
						
						unset($characters[$name]);
					}
				}
				
				if($filename == "MidsummerNightsDream.xml"){}
				
				if($filename == "TheTempest.xml")
				{
					if($num == 3 && strtolower($parts[1]) == "and")
					{
						//$characters_alt[$parts[0]] = strtolower($parts[0]);
						//$characters_alt[$parts[2]] = strtolower($parts[2]);
						
						unset($characters[$name]);
					}				
				}
				
				if($filename == "Romeo&Juliet.xml"){}
				
				if($filename == "MuchAdoAboutNothing.xml"){}
				
				if($filename == "HenryV.xml"){}
			}
			
		}
		
		
		//SORT NAMES INTO LENGTH ORDER
		uksort($characters, cmp_rlength);
		uksort($characters_alt, cmp_rlength);
		
		if((substr_count($line, "<lb") != 0) || (substr_count($line, "<stage") != 0))
		{	
			/* ADD LINKS TO REFERENCED PEOPLE */
			
			//print("Called: " . $line . "\n"); 
			$temp_line = $line;
		
			foreach($characters as $name => $id)
			{
				if(strpos($temp_line, $name))
				{
					//if($name == "Lucius")
					//	print("Name Match on " . $name . ": " . $temp_line . "\n");
						
					//$temp_line = str_replace(" " . $name, ' <rs type="person" about="[' . $id . ']">' . $name . '</rs>', $temp_line);
					//$temp_line = str_replace("/>" . $name, '/><rs type="person" about="[' . $id . ']">' . $name . '</rs>', $temp_line);
					//$temp_line = str_replace("<l>" . $name, '<l><rs type="person" about="[' . $id . ']">' . $name . '</rs>', $temp_line);
					
					$pattern = '/(?<!_|"|]">|:)' . $name . '(?!<\/rs>)/';
					
					$temp_line = preg_replace($pattern, '<rs type="person" about="[' . $id . ']">' . $name . '</rs>', $temp_line);
					
					//if($name == "Lucius")
					//	print("Changed to: " . $temp_line . "\n");
				}
			}
			
			foreach($characters_alt as $name => $id)
			{
				if(strpos($temp_line, $name))
				{				
					$pattern = '/(?<!_|"|]">|:)' . $name . '(?!<\/rs>)/';
					$temp_line = preg_replace($pattern, '<rs type="person" about="[' . $id . ']">' . $name . '</rs>', $temp_line);				
				}
			}
			
			$alt_line = $temp_line;
			//print("Altered: " . $alt_line . "\n"); 
		}
		
		
		$alt_line = str_replace('<sp who="', '<sp who="#', $alt_line);
		$alt_line = str_replace('<sp who="##', '<sp who="#', $alt_line);
		
		
		$text .= $alt_line;
	}
	
	print_r($characters);
	print_r($characters_alt);
	
	fclose($f);
	
	$output =  "extended/". $filename;
	
	$out = fopen($output, 'w' );
	fputs($out, $text);
	fclose($out);
}

function cmp_length($a, $b)
{
	$a_len = strlen($a);
	$b_len = strlen($b);
	
	if($a_len == $b_len)
		return strcmp($a, $b);
	elseif($a_len < $b_len)
		return -1;
	else
		return 1;
}

function cmp_rlength($a, $b)
{
	$a_len = strlen($a);
	$b_len = strlen($b);
	
	if($a_len == $b_len)
		return strcmp($a, $b);
	elseif($a_len < $b_len)
		return 1;
	else
		return -1;
}

?>