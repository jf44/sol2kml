<?php

echo "<html><head><title>Test3</title></head><body>";
$CordonneesGeographiques= array("16&deg;06'05 W","216&deg;W 06'05","W 0&deg;06'05", "16&deg;06'05 S","16&deg;E 06'05","N 016&deg;06'05", "16&deg;06'05 O","16&deg;O 06'05","O 016&deg;06'05", "26&deg;06'05N","26&deg;N 06'05","N 090&deg;06'05", "26&deg;06'05S","26&deg;S 06'05","S045&deg;06'05");

$t_geocode=array();

foreach ($CordonneesGeographiques as $Coord){
	$a_geocode=position_geographique($Coord);
    echo " ".$Coord." --> ";
    print_r ($a_geocode);
	echo "<br>\n";
    $t_geocode[]=$a_geocode;
}

foreach ($t_geocode as $a_geocode){

	echo "<pre>\n";
	echo "IN: <br />";
	echo "<pre>";
	print_r($a_geocode);
    echo "\n";
    echo "OUT: \n";
	echo "STR : ".geocode2str($a_geocode, true)."\n";
    echo "DEC : ".geocode2dec($a_geocode, false)."\n";
	echo "DEC + TYPE : ".geocode2dec($a_geocode, true)."\n";
	echo "</pre>\n";

}

echo "</body></html>";

/*
Longitude au format <i>016&deg;06'05W</i> ou bien <i>016&deg;W 06'05</i> ou encore <i>W 016&deg;06'05</i>)
Latitude au format <i>26&deg;06'05N</i> ou bien au format  <i>26&deg;N 06'05</i>  ou encore <i>N 016&deg;06'05)
je converti au format Google Earth par commodité

*/

/*
Renvoie un objet geocode
	$geocode->type='E|O|N|S';
	$geocode->deg='';
	$geocode->minute='';
	$geocode->seconde='';

$CordonneesGeographiques= ["16&deg;06'05 W","16&deg;W 06'05","W 016&deg;06'05", "16&deg;06'05 E","16&deg;E 06'05","E 016&deg;06'05", "16&deg;06'05 O","16&deg;O 06'05","O 016&deg;06'05", "26&deg;06'05N","26&deg;N 06'05","N 016&deg;06'05", "26&deg;06'05S","26&deg;S 06'05","S016&deg;06'05"];


foreach ($CordonneesGeographiques as $Coord){
    echo " ".$Coord." --> ";
	print_r(position_geographique($Coord));
	echo "<br>\n";
}

*/

//----------
function position_geographique($str_position){

// Determiner Longitude (E|W|O)
// Latitude (N|S)
	// DEBUG
    $str_position_out='';

	$geocode = new stdClass();
	$geocode->type='';
	$geocode->degre='';
	$geocode->minute='';
	$geocode->seconde='';

if (!empty($str_position)){
	$search  = array(' ', '"', 'W');
	$replace = array('', '', 'O');
	$str_position=str_replace($search,$replace,$str_position); // Supprimer les espace et autres caractères indésirables

        if (strpos($str_position,'E') !== false){
			//echo "Traitement Est<br />\n";
            $str_position_out = "E";
            $geocode->type='E';
            $str_position=str_replace('E','',$str_position);
		}
		elseif (strpos($str_position,'O') !== false){
            //echo "Traitement Ouest<br />\n";
            $str_position_out = "O";
            $geocode->type='O';
			$str_position=str_replace('W','',$str_position);   // W -> O
            $str_position=str_replace('O','',$str_position);
		}
		elseif (strpos($str_position,'S') !== false){
        	//echo "Traitement Sud<br />\n";
            $str_position_out = "S";
            $geocode->type='S';
            $str_position=str_replace('S','',$str_position);
		}
		elseif (strpos($str_position,'N') !== false){
   			//echo "Traitement Nord<br />\n";
            $str_position_out = "N";
            $geocode->type='N';
            $str_position=str_replace('N','',$str_position);
		}
		else{
			return null;
		}

		// Recombiner  en utilisant la librairie des fonctions multi bytes
	    // $degres =  mb_strstr($str_position, "°", true); Non supporte chez FREE
		$degres ='';
	    $minutes_secondes = '';
		$minutes = '';
		$secondes = '';

    	$len_degch=strlen("&deg;");
	    $degrepos = strpos($str_position, "&deg;",0);
		if ($degrepos!==false){
        	$degres = substr($str_position, 0, $degrepos);
	        $minutes_secondes = substr($str_position, $degrepos+$len_degch, strlen($str_position));
 		}
    	$len_minch=strlen( "'");
	    $minutepos = strpos($minutes_secondes, "'",0);
		if ($minutepos!==false){
        	$minutes = substr($minutes_secondes, 0, $minutepos);
	        $secondes = substr($minutes_secondes, $minutepos+$len_minch, strlen($minutes_secondes));
		}

    	//echo '<br \>ELEMENTS :: Degrés: "'.$degres.'" Minutes_secondes: "'.$minutes_secondes.'" Minutes: "'.$minutes.'" Secondes: "'.$secondes.'" <br />'."\n";

		//$str_position_out.=$degres.'°'.$minutes."'".$secondes;
 		//echo ' --> "'.$str_position_out.'" <br />'."\n";

    	$geocode->degre=$degres;
		$geocode->minute=$minutes;
		$geocode->seconde=$secondes;
	}
     return  $geocode;
}


//----------
function position_geographique_mb($str_position){

// Determiner Longitude (E|W|O)
// Latitude (N|S)
	// DEBUG
    $str_position_out='';

	$geocode = new stdClass();
	$geocode->type='';
	$geocode->degre='';
	$geocode->minute='';
	$geocode->seconde='';

mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");

if (!empty($str_position)){
	$search  = array(' ', '"', 'W');
	$replace = array('', '', 'O');
	$str_position=str_replace($search,$replace,$str_position); // Supprimer les espace et autres caractères indésirables

        if (strpos($str_position,'E') !== false){
			//echo "Traitement Est<br />\n";
            $str_position_out = "E";
            $geocode->type='E';
            $str_position=str_replace('E','',$str_position);
		}
		elseif (strpos($str_position,'O') !== false){
            //echo "Traitement Ouest<br />\n";
            $str_position_out = "O";
            $geocode->type='O';
			$str_position=str_replace('W','',$str_position);   // W -> O
            $str_position=str_replace('O','',$str_position);
		}
		elseif (strpos($str_position,'S') !== false){
        	//echo "Traitement Sud<br />\n";
            $str_position_out = "S";
            $geocode->type='S';
            $str_position=str_replace('S','',$str_position);
		}
		elseif (strpos($str_position,'N') !== false){
   			//echo "Traitement Nord<br />\n";
            $str_position_out = "N";
            $geocode->type='N';
            $str_position=str_replace('N','',$str_position);
		}
		else{
			return null;
		}

	// Recombiner  en utilisant la librairie des fonctions multi bytes
    // $degres =  mb_strstr($str_position, "&deg;", true); Non supporte chez FREE
	$degres ='';
    $minutes_secondes = '';
	$minutes = '';
	$secondes = '';

	//if ($degrepos = mb_strpos($str_position, "&deg;")!==false){
		//echo "<br \>DEGRE à la position $degrepos\n";
        if ($t_degre=mb_split("&deg;", $str_position)){
			$degres=$t_degre[0];
            $minutes_secondes=$t_degre[1];
		}
	//}

	//if ($minutepos = mb_strpos($minutes_secondes, "'")!==false){
		//echo "<br \>MINUTE à la position $minutepos\n";
        if ($t_minute=mb_split("'", $minutes_secondes)){
			$minutes=$t_minute[0];
            $secondes=$t_minute[1];
		}
	//}

    //echo ' ELEMENTS :: Degrés: "'.$degres.'" Minutes_secondes: "'.$minutes_secondes.'" Minutes: "'.$minutes.'" Secondes: "'.$secondes.'" <br />'."\n";

	//$str_position_out.=$degres.'&deg;'.$minutes."'".$secondes;
 	//echo ' --> "'.$str_position_out.'" <br />'."\n";

    	$geocode->degre=$degres;
		$geocode->minute=$minutes;
		$geocode->seconde=$secondes;
	}
     return  $geocode;
}


//------------------------------
function geocode2str($geocode, $fin=false){
	$str='';
	if ($fin){
    	return $geocode->degre."&deg;".$geocode->minute."'".$geocode->seconde." ".$geocode->type;
	}
	else{
		return $geocode->type." ".$geocode->degre."&deg;".$geocode->minute."'".$geocode->seconde;
	}
}

//------------------------------
function geocode2dec($geocode, $type=false){
	if (!empty($geocode)){
        $coord_decimal = $geocode->degre + ($geocode->minute / 60) + ($geocode->seconde / 3600.0);
		if (($geocode->type == 'S') || ($geocode->type == 'O') || ($geocode->type == 'W')){
            $coord_decimal = -$coord_decimal;
		}
		if ($type      ){
			switch ($geocode->type) {
				case "N" :
				case "S" :
					$coord_decimal .= " Latitude";
					break;
				default :
                	$coord_decimal .= " Longitude";
				break;
			}
		}
	}
	return $coord_decimal;
}

?>