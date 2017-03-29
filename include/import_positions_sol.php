<?php
// JF

// Importation des positions des bateaux depuis la base de données dédiee

// Les données de latitude et longitude sont en système décimal comme dans Google Maps. et Google Earth.

// gestion des groupes
/*
// Récupéré sur l'Internet
On entend souvent ce type de récrimination : "En saisissant les coordonnées GPS d'un point sur une carte Google Maps,
je me retrouve au beau milieu de l'océan atlantique !"
Les périphériques GPS transmettent par défaut les coordonnées en sexagésimal (
système de numération utilisant la base 60) alors que les cartes Google Maps utilisent le système décimal.
L'unité du sexagésimal est le degré (360 degrés), puis la minute (60 minutes = 1 degré)
puis la seconde (60 secondes = 1 minute).
Une solution possible consiste alors à convertir les degrés sexagésimaux en degrés décimaux.
Prenons un exemple :
Soit une latitude de 46°10'28" (46 degrés, 10 minutes et 28 secondes).
Exprimée en degrés décimaux, la latitude sera égale à : 46 + (10 / 60) + (28 / 3600) soit 46.1744444.
On peut donc écrire cette formule : latitude (degrés décimaux) = degrés + (minutes / 60) + (secondes / 3600).
En sens inverse, voici le déroulement des opérations :
46.1744444 - 0.1744444 = 46 ;
0.174444 * 60 = 10.46664 ;
10.46664 - 0.46664 = 10 ;
0.46664 * 60 = 27.984.
On obtient alors ce résultat : 46° 10' 27.984".
*/


//--------------
function get_trajectoire_boat_id($connexion, $periode=null, $batoid){
global $correction_geodesique;
	$trajectoire = '';
	if (!empty($connexion) && !empty($batoid)){
		if (empty($periode)){
			$requete = 'SELECT * FROM `sol_position` WHERE `refbato`='.$batoid.'
  ORDER BY `date` ASC';
		}
		else{
 			$requete = 'SELECT * FROM `sol_position` WHERE `refbato`='.$batoid.'
  AND `date`<="'.$periode->datemax.'"  ORDER BY `date` ASC';
		}
		if (verifie_requete_select($connexion, $requete)){
			if ($res=execute_requete($connexion, $requete)){
				while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
     				$trajectoire .= geocode2dec(position_geographique($row['longitude']), false)."!". $correction_geodesique * geocode2dec(position_geographique($row['latitude']), false).";";
				}
			}
		}
	}
	return $trajectoire;
}


/**
 * input : connexion BD
 * input : periode objet  datemin , datemax
 * output : tableau de Voiliers
 **/

//--------------
function get_bato_last_position_bd($connexion, $periode=null, $nomboat,  $oktrajectoire=false){
	$t_voiliers = array();
	if (!empty($connexion) && !empty($nomboat)){
		if ($boat = get_boat_by_nom_bd($connexion, $nomboat)){
			if (empty($periode)){
				$requete = 'SELECT * FROM `sol_position` WHERE `refbato`='.$boat->id.'
 ORDER BY `date` DESC, `rangvr` ASC;';
			}
			else{
				$requete = 'SELECT * FROM `sol_position` WHERE `refbato`='.$boat->id.'
 AND `date`>"'.$periode->datemin.'" AND `date`<="'.$periode->datemax.'"  ORDER BY `date` DESC, `rangvr` ASC;';
			}


			if (verifie_requete_select($connexion, $requete)){
				if ($res=execute_requete($connexion, $requete)){
					if ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {    // une seule position chargee
						// $boat=get_boat_bd($connexion, $row['refbato']);    // deja charge
						$CouleurVoilier= SetCouleurBd2Voilier($boat->couleur);
						$un_voilier= new Voilier();
						$un_voilier->SetPosition(
                            $boat->mmsi,
							$boat->nomboat,
                            $boat->syc,
                            $boat->idsol,
							$row['date'],
							geocode2dec(position_geographique($row['latitude']), false),
							geocode2dec(position_geographique($row['longitude']), false),
							$row['cog'],
							$row['sog'],
                            $row['navstatus'],
							SetVoileBd2Voilier($row['voile']),	// $re->voile,
							$row['tws'],
							$row['twa'],
							$CouleurVoilier->couleur_coque,   // RRR;VVV;BBB
							$CouleurVoilier->couleur_vav,
							$CouleurVoilier->couleur_gv,
							$CouleurVoilier->couleur_spi,
							$CouleurVoilier->couleur_spi2);

						// $un_voilier->SetRang($row['rangrkn']);
						// Trajectoire
						if ($oktrajectoire==true){
                            // par defaut on n'importe pas la trajectoire.
							// 	<trajectoire>120.45!35.7402;121.513!35.6521;121.723!35.7717;</trajectoire>
							$un_voilier->SetTrajectoire(get_trajectoire_boat_id($connexion, $periode, $boat->id));
						}

						$t_voiliers[] = $un_voilier;
					}
				}
	 		}
		}
	}
    return ($t_voiliers);
}



/**
 * input : connexion BD
 * input : periode objet  datemin , datemax
 * output : tableau de Voiliers
 **/

//--------------
function get_bato_all_positions_bd($connexion, $periode=null, $nomboat, $oktrajectoire=false){
	$t_voiliers = array();
	$t_voilier_affiche = array();
	if (!empty($connexion) && !empty($nomboat)){
    	if ($boat = get_boat_by_nom_bd($connexion, $nomboat)){
			if (empty($periode)){
				$requete = 'SELECT * FROM `sol_position` WHERE `refbato`='.$boat->id.'
 ORDER BY `date` DESC;';
			}
			else{
				$requete = 'SELECT * FROM `sol_position` WHERE `refbato`='.$boat->id.'
 AND `date`>"'.$periode->datemin.'" AND `date`<="'.$periode->datemax.'"  ORDER BY `date` DESC;';
			}

			$t_voilier_affiche[$boat->id]=false;
			if (verifie_requete_select($connexion, $requete)){
				if ($res=execute_requete($connexion, $requete)){
					while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
						// $boat=get_boat_bd($connexion, $row['refbato']);    // deja charge
						$CouleurVoilier= SetCouleurBd2Voilier($boat->couleur);
						$un_voilier= new Voilier();
						$un_voilier->SetPosition(
                            $boat->mmsi,
							$boat->nomboat,
                            $boat->syc,
                            $boat->idsol,
							$row['date'],
							geocode2dec(position_geographique($row['latitude']), false),
							geocode2dec(position_geographique($row['longitude']), false),
							$row['cog'],
							$row['sog'],
                            $row['navstatus'],
							SetVoileBd2Voilier($row['voile']),	// $re->voile,
							$row['tws'],
							$row['twa'],
							$CouleurVoilier->couleur_coque,   // RRR;VVV;BBB
							$CouleurVoilier->couleur_vav,
							$CouleurVoilier->couleur_gv,
							$CouleurVoilier->couleur_spi,
							$CouleurVoilier->couleur_spi2);

						// $un_voilier->SetRang($row['rangrkn']);
						// Trajectoire
						if (($oktrajectoire==true) && ($t_voilier_affiche[$boat->id]==false)){
                            $t_voilier_affiche[$boat->id]=true;
                            // par defaut on n'importe pas la trajectoire.
							// 	<trajectoire>120.45!35.7402;121.513!35.6521;121.723!35.7717;</trajectoire>
							$un_voilier->SetTrajectoire(get_trajectoire_boat_id($connexion, $periode, $boat->id));
						}

						$t_voiliers[] = $un_voilier;
					}
				}
	 		}
		}
	}
    return ($t_voiliers);
}



//--------------
function get_all_boats_last_position_bd($connexion, $group=null, $periode=null, $oktrajectoire=false){
    $t_voiliers = array();
    $t_voilier_affiche = array();

    if (!empty($connexion)){
		if (empty($group)){
	        if (!empty($periode)){
				$requete = 'SELECT * FROM `sol_position` WHERE `date`>"'.$periode->datemin.'" AND `date`<="'.$periode->datemax.'" ORDER BY `date` DESC;';
			}
			else{
				$requete = 'SELECT * FROM `sol_position` ORDER BY `date` DESC;';
			}
		}
		else{
	        if (!empty($periode)){
				$requete = 'SELECT * FROM `sol_group_boat` AS g, `sol_position` AS p
 WHERE g.`ref_group`='.$group.' AND g.`ref_boat`=p.`refbato`
 AND  p.`date`>"'.$periode->datemin.'" AND p.`date`<="'.$periode->datemax.'"
 ORDER BY p.`date` DESC;';
			}
			else{
				$requete = 'SELECT * FROM `sol_group_boat` AS g, `sol_position` AS p
 WHERE g.`ref_group`='.$group.' AND g.`ref_boat`=p.`refbato`
 ORDER BY p.`date` DESC;';
			}
		}
		if (verifie_requete_select($connexion, $requete)){
			if ($res=execute_requete($connexion, $requete)){
				while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
					$boat=get_boat_bd($connexion, $row['refbato']);
                    if (!isset($t_voilier_affiche[$boat->id])){
						$t_voilier_affiche[$boat->id]=false;
					}
					if (($t_voilier_affiche[$boat->id]==false)){ // Afficher ce bateau
                    	$t_voilier_affiche[$boat->id]=true;
                    	$CouleurVoilier= SetCouleurBd2Voilier($boat->couleur);
						$un_voilier= new Voilier();
						$un_voilier->SetPosition(
                            $boat->mmsi,
							$boat->nomboat,
                            $boat->syc,
                            $boat->idsol,
							$row['date'],
							geocode2dec(position_geographique($row['latitude']), false),
							geocode2dec(position_geographique($row['longitude']), false),
							$row['cog'],
							$row['sog'],
                            $row['navstatus'],
							SetVoileBd2Voilier($row['voile']),	// $re->voile,
							$row['tws'],
							$row['twa'],
							$CouleurVoilier->couleur_coque,   // RRR;VVV;BBB
							$CouleurVoilier->couleur_vav,
							$CouleurVoilier->couleur_gv,
							$CouleurVoilier->couleur_spi,
							$CouleurVoilier->couleur_spi2);

						//$un_voilier->SetRang($row['rangrkn']);
                    	// Trajectoire

                        // $un_voilier->SetSymbole(false);  // afficher la coque et les voiles
                        if ($oktrajectoire==true){
							// 	<trajectoire>120.45!35.7402;121.513!35.6521;121.723!35.7717;</trajectoire>
							$la_trajectoire = get_trajectoire_boat_id($connexion, $periode, $boat->id);
							//echo "DEBUG import_positions_bd.php :: 253 <br/>Trajectoire:<br />$la_trajectoire<br/>\n";
							$un_voilier->SetTrajectoire($la_trajectoire);

						}
                        $t_voiliers[] = $un_voilier;
					}
				}
			}
	 	}
	}
    return ($t_voiliers);
}


//--------------
function get_all_boats_all_positions_bd($connexion, $group=null, $periode=null, $oktrajectoire=false){
    $t_voiliers = array();
    $t_voilier_affiche = array();

    if (!empty($connexion)){
		if (empty($group)){
	        if (!empty($periode)){
				$requete = 'SELECT * FROM `sol_position` WHERE `date`>"'.$periode->datemin.'" AND `date`<="'.$periode->datemax.'"  ORDER BY `date` DESC;';
			}
			else{
				$requete = 'SELECT * FROM `sol_position` ORDER BY `date` DESC;';
			}
		}
		else{
	        if (!empty($periode)){
				$requete = 'SELECT * FROM `sol_group_boat` AS g, `sol_position` AS p
 WHERE g.`ref_group`='.$group.' AND g.`ref_boat`=p.`refbato`
 AND  p.`date`>"'.$periode->datemin.'" AND p.`date`<="'.$periode->datemax.'"
 ORDER BY p.`date` DESC;';
			}
			else{
				$requete = 'SELECT * FROM `sol_group_boat` AS g, `sol_position` AS p
 WHERE g.`ref_group`='.$group.' AND g.`ref_boat`=p.`refbato`
 ORDER BY p.`date` DESC;';
			}
		}
		if (verifie_requete_select($connexion, $requete)){
			if ($res=execute_requete($connexion, $requete)){
				while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
					$boat=get_boat_bd($connexion, $row['refbato']);
					/*
					echo "<br />DEBUG : Bateaux récupérés<br />\n";
	                echo '<pre>'."\n";
					print_r ($boat);
					echo '</pre>'."\n";
                	echo "<br />DEBUG : Positions<br />\n";
	                echo '<pre>'."\n";
					print_r ($row);
					echo '</pre>'."\n";
					*/
                    if (!isset($t_voilier_affiche[$boat->id])){
						$t_voilier_affiche[$boat->id]=false;
					}

                    $CouleurVoilier= SetCouleurBd2Voilier($boat->couleur);
					$un_voilier= new Voilier();
						$un_voilier->SetPosition(
                            $boat->mmsi,
							$boat->nomboat,
                            $boat->syc,
                            $boat->idsol,
							$row['date'],
							geocode2dec(position_geographique($row['latitude']), false),
							geocode2dec(position_geographique($row['longitude']), false),
							$row['cog'],
							$row['sog'],
                            $row['navstatus'],
							SetVoileBd2Voilier($row['voile']),	// $re->voile,
							$row['tws'],
							$row['twa'],
							$CouleurVoilier->couleur_coque,   // RRR;VVV;BBB
							$CouleurVoilier->couleur_vav,
							$CouleurVoilier->couleur_gv,
							$CouleurVoilier->couleur_spi,
							$CouleurVoilier->couleur_spi2);

                    /*
					echo "<br />DEBUG : Voilier créé<br />\n";
	                echo '<pre>'."\n";
					print_r ($un_voilier);
					echo '</pre>'."\n";
					flush();
					exit;
					*/

					//$un_voilier->SetRang($row['rangrkn']);
                    // Trajectoire
					if (($t_voilier_affiche[$boat->id]==false)){ // par defaut on n'importe pas la trajectoire.
                        $t_voilier_affiche[$boat->id]=true;
                        if ($oktrajectoire==true){
							// 	<trajectoire>120.45!35.7402;121.513!35.6521;121.723!35.7717;</trajectoire>
							$la_trajectoire = get_trajectoire_boat_id($connexion, $periode, $boat->id);
							//echo "DEBUG import_positions_bd.php :: 253 <br/>Trajectoire:<br />$la_trajectoire<br/>\n";
							$un_voilier->SetTrajectoire($la_trajectoire);
						}
					}

					/*
					echo "<br />DEBUG : Voilier créé<br />\n";
	                echo '<pre>'."\n";
					print_r ($un_voilier);
					echo '</pre>'."\n";
					flush();
					exit;
					*/

					$t_voiliers[] = $un_voilier;
				}
			}
	 	}
	}
    return ($t_voiliers);
}



// Inversion de la fonction Voilier->GetVoile()
/*
    	function GetVoile(){
		switch ($this->voile){
			case 1 : return 'foc'; break;
			case 2 : return 'spi'; break;
			case 4 : return 'foc2'; break;
			case 8 : return 'genois'; break;
			case 16 : return 'code zero'; break;
			case 32 : return 'spi leger'; break;
			case 64 : return 'gennaker'; break;
			default : return 'foc'; break;
		}
	}
*/
function SetVoileBd2Voilier($voile_avant){
	if (empty($voile_avant) || ($voile_avant=='Spi')){ return 2;} else { return 1;}
}



// Conversion du shéma de couleur de la BD
// ffffaa,33ff33,ff3333,3333ff
// en schema de la classe Voilier
// stdClass Object
//(
//    [couleur_coque] => 255;255;170
//    [couleur_vav] => 255;51;51
//    [couleur_gv] => 51;255;51
//    [couleur_spi] => 51;51;255
//    [couleur_spi2] => 51;51;255
//)


//-----------------------------------
function SetCouleurBd2Voilier($bcouleur){
	// $boat->couleur  	= "coque,voile,foc,spi" coque:ffffff,voile:ffff33,foc:ffffff,spi:ee33ef
	// $voilier->une_couleur_par_element
	$tcol_v = new stdClass();
	$tcol_v->couleur_coque=255; // coque    RRR;VVV;BBB
	$tcol_v->couleur_vav=255; // GV
	$tcol_v->couleur_gv=255; // VoileAvant
	$tcol_v->couleur_spi=255; // Spi
	$tcol_v->couleur_spi2=255; // Spi 2

    if ($bcouleur){
		list($ccoque, $cvoile, $cfoc, $cspi) = explode(',', $bcouleur);
           $tcol_v->couleur_coque = hexa2_3dec($ccoque);
           $tcol_v->couleur_vav = hexa2_3dec($cfoc);
           $tcol_v->couleur_gv = hexa2_3dec($cvoile);
           $tcol_v->couleur_spi = hexa2_3dec($cspi);
           $tcol_v->couleur_spi2 = hexa2_3dec($cspi);
	}

	return ($tcol_v);
}

//-------------------------
function hexa2_3dec($hexa){
	// rrvvbb -> hexdec(rr);hexdec(vv);hexdec(bb)
    if (list($rr, $vv, $bb) = explode(';', chunk_split ($hexa,2,';'))){
		return (hexdec($rr).';'.hexdec($vv).';'.hexdec($bb));
	}
	return false;
}


/*
Longitude au format <i>016°06'05W</i> ou bien <i>016°W 06'05</i> ou encore <i>W 016°06'05</i>)
Latitude au format <i>26°06'05N</i> ou bien au format  <i>26°N 06'05</i>  ou encore <i>N 016°06'05)
je converti au format Google Earth par commodité

*/

/*
Renvoie un objet geocode
	$geocode->type='E|O|N|S';
	$geocode->deg='';
	$geocode->minute='';
	$geocode->seconde='';

$CordonneesGeographiques= ["16°06'05 W","16°W 06'05","W 016°06'05", "16°06'05 E","16°E 06'05","E 016°06'05", "16°06'05 O","16°O 06'05","O 016°06'05", "26°06'05N","26°N 06'05","N 016°06'05", "26°06'05S","26°S 06'05","S016°06'05"];


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
	$search  = array("’", ' ', '"', 'W');
	$replace = array("'", '', '', 'O');
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
// problèmes à répétition sur Free
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
	$search  = array("’", ' ', '"', 'W');
	$replace = array("'", '', '', 'O');
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

	//if ($degrepos = mb_strpos($str_position, "°")!==false){
		//echo "<br \>DEGRE à la position $degrepos\n";
        if ($t_degre=mb_split("°", $str_position)){
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

	//$str_position_out.=$degres.'°'.$minutes."'".$secondes;
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
        return $coord_decimal;
	}
	return false;
}

?>