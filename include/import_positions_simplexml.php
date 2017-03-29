<?php
// JF
// Utilise la classe SimpleXML pour récupérer les données
// NON supporte par de nombreux serveurs
// preferer l'autre include
// Importation des positions des bateaux depuis MP
// Les données de latitude et longitude sont en système décimal comme dans Google Maps. et Google Earth.

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
function recupere_profil_xml($tab_xml){
// le fichier contient trop d'information pour notre usage
// on tronque apres la balise </position>
// retourne une ligne 
/*
xml version="1.0" encoding="UTF-8" 
// obtenu avec http://edhec.virtualregatta.com/get_user.php?pseudo=RKN-Iroise&clef=287405ee1e7c7926a64544adb959b87c
<user id_user="635813" date='2009-04-01 19:48:11'>
	<pseudo><![CDATA[RKN-Iroise]]></pseudo>
	<chat_allowed>1</chat_allowed>
	<is_moderateur>0</is_moderateur>
	<is_admin>0</is_admin>
	<type>1</type>
	<metric>1</metric>

	<country></country>
	<quizz_to_respond></quizz_to_respond>
	<full_option>0</full_option>
	<option_repair_kits>0</option_repair_kits>
	<is_star>0</is_star>
	<is_winner>0</is_winner>
	<is_sponsor>0</is_sponsor>

	<link_sponsor></link_sponsor>
	<pict_sponsor></pict_sponsor>
	<couleur1>229;0;0</couleur1>
	<couleur2>254;254;254</couleur2>
	<couleur3>254;254;254</couleur3>
	<couleur4>254;152;254</couleur4>
	<couleur5>229;0;180</couleur5>

	<clef_valide>1</clef_valide>
	<position date='2009-04-01 19:42:01'>
		<latitude>40.1446</latitude>
		<longitude>-73.3656</longitude>
		<spi>0</spi>
		<voile>1</voile>

		<voiles_cassees>0</voiles_cassees>
		<vitesse>9.5</vitesse>
		<wind_speed>15.1188</wind_speed>
		<wind_angle>41</wind_angle>
		<cap>167</cap>
		<distancerestante>3067.9</distancerestante>

		<distanceparcourue>47.4442</distanceparcourue>
		<option_assistance>0</option_assistance>
		<option_vigilance>0</option_vigilance>
		<option_voile_auto>0</option_voile_auto>
		<option_voile_auto_activated>0</option_voile_auto_activated>
		<option_voiles_pro>0</option_voiles_pro>

		<option_program>0</option_program>
		<option_program_setdate>0000-00-00 00:00:00</option_program_setdate>
		<option_program_autohour>0</option_program_autohour>
		<option_program_autominutes>0</option_program_autominutes>
		<option_program_autocap>361</option_program_autocap>
		<option_waypoints>0</option_waypoints>

		<classement>56803</classement>
		<IsArrived>0</IsArrived>
		<checkpoint>0</checkpoint>
		<id_parcours>1</id_parcours>
		<id_parcours_xml>e0c89feb-5460-4c82-99f7-6bbfc674f900</id_parcours_xml>
		<temps_etape>0</temps_etape>

		<RegimeMoteur>70</RegimeMoteur>
		<Carburant>0</Carburant>
		<event>0</event>
		<temps_event>0</temps_event>
		<option_regulateur>0</option_regulateur>
		<option_regulateur_cap>361</option_regulateur_cap>

		<diffClassement>-162</diffClassement>
	</position>
	<trajectoire>-73.9113!40.5007;-73.779!40.3251;</trajectoire>

*/
/*
http://vendeeglobe.virtualregatta.com/get_user.php?pseudo=swan
Ce fichier est tres gros on va le tronquer pour en accelerer le traitement.


xml version="1.0" encoding="UTF-8" 
<user id_user="60022" date='2009-03-03 02:41:52'>
	<pseudo><![CDATA[LadyJane]]></pseudo>
	<chat_allowed>1</chat_allowed>
	<is_moderateur>0</is_moderateur>
	<is_admin>0</is_admin>
	<type>1</type>
	<metric>1</metric>

	<country>fr</country>
	<quizz_to_respond></quizz_to_respond>
	<full_option>0</full_option>
	<option_repair_kits>1</option_repair_kits>
	<is_star>0</is_star>
	<is_winner>0</is_winner>

	<is_sponsor>0</is_sponsor>
	<link_sponsor></link_sponsor>
	<pict_sponsor></pict_sponsor>
	<couleur1>0;0;229</couleur1>
	<couleur2>254;221;152</couleur2>
	<couleur3>169;169;169</couleur3>
	<couleur4>254;178;0</couleur4>
	<couleur5>229;0;0</couleur5>
	<clef_valide>0</clef_valide>
	<position date='2009-03-03 02:40:01'>
		<latitude>-20.2949</latitude>
		<longitude>170.332</longitude>
		<spi>0</spi>
		<voile>16</voile>
		<voiles_cassees>0</voiles_cassees>
		<vitesse>12.28</vitesse>
		<wind_speed>9.17927</wind_speed>
		<wind_angle>85</wind_angle>
		<cap>179</cap>

		<distancerestante>8261.18</distancerestante>
		<distanceparcourue>4780.61</distanceparcourue>
		<option_assistance>0</option_assistance>
		<option_vigilance>0</option_vigilance>
		<option_voile_auto>0</option_voile_auto>
		<option_voile_auto_activated>0</option_voile_auto_activated>

		<option_voiles_pro>1</option_voiles_pro>
		<option_program>0</option_program>
		<option_program_setdate>0000-00-00 00:00:00</option_program_setdate>
		<option_program_autohour>0</option_program_autohour>
		<option_program_autominutes>0</option_program_autominutes>
		<option_program_autocap>361</option_program_autocap>

		<classement>19641</classement>
		<IsArrived>0</IsArrived>
		<checkpoint>0</checkpoint>
		<id_parcours>5</id_parcours>
		<id_parcours_xml>e0c89feb-5460-4c82-99f7-6bbfc674f904</id_parcours_xml>
		<temps_etape>0</temps_etape>

		<RegimeMoteur>70</RegimeMoteur>
		<Carburant>0</Carburant>
		<event>0</event>
		<temps_event>0</temps_event>
		<option_regulateur>0</option_regulateur>
		<option_regulateur_cap>361</option_regulateur_cap>

		<diffClassement>241</diffClassement>
	</position>
	<trajectoire>120.45!35.7402;121.513!35.6521;121.723!35.7717;122.005!35.9095;122.929!35.4879;123.407!34.2182;123.777!33.2043;124.302!32.0607;125.059!31.7046;126.559!30.3949;128.73!28.5023;129.363!27.6401;131.83!27.5401;133.809!27.5401;134.338!27.0157;136.038!25.989;137.363!25.2493;138.922!23.7126;139.214!23.2367;139.298!22.5751;141.3!19.3556;141.773!18.7157;142.985!18.4076;143.268!17.7516;144.166!16.7804;145.742!15.6018;146.808!14.453;148.112!12.8392;148.916!11.8915;149.232!11.5815;150.275!10.9631;152.009!9.52024;153.004!8.7869;153.716!8.00948;155.069!6.19258;155.349!5.89654;156.229!4.73727;156.62!4.18257;156.921!3.71216;157.6!1.69251;157.768!1.41695;158.285!0.535084;158.599!-0.228893;158.902!-0.567375;159.482!-2.7696;160.396!-3.9546;160.644!-4.76668;160.902!-5.62809;161.925!-6.87726;162.185!-7.27779;162.358!-7.7477;163.825!-9.02144;164.478!-11.064;164.767!-11.5391;165.559!-12.4896;166.208!-13.6263;166.507!-15.251;166.422!-16.4251;166.498!-16.9328;166.729!-17.1236;167.617!-17.7865;167.909!-18.6209;168.687!-18.1922;168.793!-18.6472;168.909!-19.5911;169.259!-19.5687;170.199!-19.6453;</trajectoire>
	<amis>
		<boat id_user="71">
			<pseudo><![CDATA[Hurricane]]></pseudo>
			<is_moderateur>0</is_moderateur>

*/
	$s='';
	if (($tab_xml) && is_array($tab_xml) && (count($tab_xml)>0)){
		$nbl=count($tab_xml);
		$i=0;
		$ok=true;
		while (($i<$nbl) && $ok){
			if (!eregi("</position>",$tab_xml[$i])){
				$s.=$tab_xml[$i];
			}
			else{
				$s.="</position></user>";
				$ok=false;
			}
			$i++;
		}
		/*
		$s=str_replace("\t", " ", $s);
		$s=str_replace("\r", " ", $s);
		$s=str_replace("\n", " ", $s);
		*/
		$order   = array("\r\n", "\n", "\r", "\t");
		$replace = ' ';
		$s = str_replace($order, $replace, $s);
	}
	return $s;
}


//--------------
function get_position_reduite_xml($pseudo_user, $drapeau=''){
// recupere la position du bateau sans trajectoire ni amis
global $utiliser_cache;
global $course;
global $t_url_course;
global $t_url_data;
global $LE_GRUMEAU;
global $rang;
$id_user=0;
if ($pseudo_user!=""){
	/// COMMENCER par verifier si la donnee est dans le cache
	if ($utiliser_cache==1){
		$un_voilier=recupere_cache_data_voilier($pseudo_user);
	}

	$un_voilier=recupere_cache_data_voilier($pseudo_user);
	if (!$un_voilier){
		$url_user_info=$t_url_data[$course]."get_user.php?pseudo=";
		$url_user_info.=urlencode($pseudo_user);
		// DEBUG
		// echo "<br /><b>URL Info utilisateur</b> :  $url_user_info\n";
		$handle0 = fopen($url_user_info, "r");
		if ($handle0){
			// $profil =  file_get_contents($url_user_info);
			$profil = recupere_profil_xml(file($url_user_info));
			if ($profil!=""){
				// DEBUG
				// echo "<br /><b>Profil</b><br />\n";
				// echo htmlspecialchars($profil) . "<br />\n";
				// XML
				$profil_xml = new SimpleXMLElement($profil);
				if ($profil_xml){
					// DEBUG
					/*
					echo '<br /><b>id_user</b>: <i>'.$profil_xml['id_user'].'</i>'."\n";
					echo '<br /><b>pseudo</b>: <i>'.$profil_xml->pseudo.'</i>'."\n";
					echo '<br /><b>Date</b>: <i>'.$profil_xml->position['date'].'</i>'."\n";
					echo '<br /><b>latitude</b>: <i>'.$profil_xml->position->latitude.'</i>'."\n";
					echo '<br /><b>longitude</b>: <i>'.$profil_xml->position->longitude.'</i>'."\n";
					echo '<br /><b>vitesse</b>: <i>'.$profil_xml->position->vitesse.'</i>'."\n";
					echo '<br /><b>cap</b>: <i>'.$profil_xml->position->cap.'</i>'."\n";
					echo '<br /><b>classement</b>: <i>'.$profil_xml->position->classement.'</i>'."\n";
					*/
					// echo '<br /><b>couleur1</b>: <i>'.$profil_xml->couleur1.'</i>'."\n";
					$un_voilier= new Voilier();
					
					// Drapeau national ?
					if ($drapeau==''){ // on peut forcer le drapeau
						if (isset($profil_xml->country) && ($profil_xml->country!='')){
							$drapeau=$profil_xml->country;  // sinon on recupere l'info stockee
						}
					}
					
					if ($profil_xml->pseudo!=$LE_GRUMEAU){
						$un_voilier->SetPosition($profil_xml['id_user'], 
							$profil_xml->pseudo,
							$profil_xml['date'], 
							$profil_xml->position->latitude, 
							$profil_xml->position->longitude, 
							$profil_xml->position->cap,
							$profil_xml->position->vitesse,
							$profil_xml->position->classement,
							$profil_xml->position->spi,
							$profil_xml->position->voile,
							$profil_xml->position->voiles_cassees,
							$profil_xml->position->wind_speed,
							$profil_xml->position->wind_angle,
							$profil_xml->couleur1,
							$profil_xml->couleur2,
							$profil_xml->couleur3,
							$profil_xml->couleur4, 
							$profil_xml->couleur5, 
							$drapeau);
						$un_voilier->SetRang($rang);
						$rang++;
					}
					else{ // le Grumeau est un Ka
						$un_voilier->SetPosition($profil_xml['id_user'], 
							$profil_xml->pseudo,
							$profil_xml['date'], 
							$profil_xml->position->latitude, 
							$profil_xml->position->longitude, 
							$profil_xml->position->cap,
							$profil_xml->position->vitesse,
							$profil_xml->position->classement,
							$profil_xml->position->spi,
							$profil_xml->position->voile,
							$profil_xml->position->voiles_cassees,
							$profil_xml->position->wind_speed,
							$profil_xml->position->wind_angle,
							$profil_xml->couleur1,
							$profil_xml->couleur2,
							$profil_xml->couleur3,
							$profil_xml->couleur4,
							$profil_xml->couleur5, 
							$drapeau);
						$un_voilier->SetRang(0);
					}
				}
			}
			fclose($handle0);
		}
	}
	return $un_voilier;
}
return false;
}


//--------------
function get_position_xml($pseudo_user, $oktrajectoire=false, $okamis=false, $drapeau=''){
// recupere la position du bateau + trajectoire et amis
global $utiliser_cache;
global $course;
global $t_url_course;
global $t_url_data;
global $LE_GRUMEAU;
global $rang;
$id_user=0;
if ($pseudo_user!=""){
	/// COMMENCER par verifier si la donnee est dans le cache
	if ($utiliser_cache==1){
		$un_voilier=recupere_cache_data_voilier($pseudo_user);
	}

	if (!$un_voilier){
		$url_user_info=$t_url_data[$course]."get_user.php?pseudo=";
		$url_user_info.=urlencode($pseudo_user);
		// DEBUG
		// echo "<p><b>URL Info utilisateur</b> :  $url_user_info\n";
		$handle0 = fopen($url_user_info, "r");
		if ($handle0){
			$profil =  file_get_contents($url_user_info);
			if ($profil!=""){
				// DEBUG
				// echo "<br />DEBUG :: import_positions.php :: 263 :  <b>Profil</b><br />\n";
				// echo htmlspecialchars($profil) . "<br />\n";
				// XML
				$profil_xml = new SimpleXMLElement($profil);
				if ($profil_xml){
					// DEBUG
					// echo '<br /><b>id_user</b>: <i>'.$profil_xml['id_user'].'</i>'."\n";
					// echo '<br /><b>pseudo</b>: <i>'.$profil_xml->pseudo.'</i>'."\n";
					// echo '<br /><b>Date</b>: <i>'.$profil_xml->position['date'].'</i>'."\n";
					// echo '<br /><b>latitude</b>: <i>'.$profil_xml->position->latitude.'</i>'."\n";
					// echo '<br /><b>longitude</b>: <i>'.$profil_xml->position->longitude.'</i>'."\n";
					// echo '<br /><b>vitesse</b>: <i>'.$profil_xml->position->vitesse.'</i>'."\n";
					// echo '<br /><b>cap</b>: <i>'.$profil_xml->position->cap.'</i>'."\n";
					// echo '<br /><b>classement</b>: <i>'.$profil_xml->position->classement.'</i>'."\n";
					$un_voilier= new Voilier();
					
					// Drapeau national ?
					if ($drapeau==''){ // on ne force pas le drapeau
						if (isset($profil_xml->country) && ($profil_xml->country!='')){ // on recupere l'info stockee
							$drapeau=$profil_xml->country;  
						}
					}
					
					if ($profil_xml->pseudo!=$LE_GRUMEAU){
						$un_voilier->SetPosition($profil_xml['id_user'], $profil_xml->pseudo, 
							$profil_xml['date'], $profil_xml->position->latitude, 
							$profil_xml->position->longitude, 
							$profil_xml->position->cap,
							$profil_xml->position->vitesse,
							$profil_xml->position->classement,
							$profil_xml->position->spi,
							$profil_xml->position->voile,
							$profil_xml->position->voiles_cassees,
							$profil_xml->position->wind_speed,
							$profil_xml->position->wind_angle,
							$profil_xml->couleur1,
							$profil_xml->couleur2,
							$profil_xml->couleur3,
							$profil_xml->couleur4,
							$profil_xml->couleur5, 
							$drapeau);	
						$un_voilier->SetRang($rang);
						$rang++;
					}
					
					else{ // le Grumeau est un Ka
						$un_voilier->SetPosition($profil_xml['id_user'], 
							$profil_xml->pseudo,
							$profil_xml['date'], 
							$profil_xml->position->latitude, 
							$profil_xml->position->longitude, 
							$profil_xml->position->cap,
							$profil_xml->position->vitesse,
							$profil_xml->position->classement,
							$profil_xml->position->spi,
							$profil_xml->position->voile,
							$profil_xml->position->voiles_cassees,
							$profil_xml->position->wind_speed,
							$profil_xml->position->wind_angle,
							$profil_xml->couleur1,
							$profil_xml->couleur2,
							$profil_xml->couleur3,
							$profil_xml->couleur4,
							$profil_xml->couleur5, 
							$drapeau);
						$un_voilier->SetRang(0);
					}
					// Trajectoire
					if ($oktrajectoire==true){ // par defaut on n'importe pas la trajectoire.
						// DEBUG
						// echo "<br />DEBUG :: import_positions.php :: Ligne 333 : Trajectoire :".$profil_xml->trajectoire."\n";
						// 	<trajectoire>120.45!35.7402;121.513!35.6521;121.723!35.7717;</trajectoire>
						$un_voilier->SetTrajectoire($profil_xml->trajectoire);
					}
					// Amis
					if ($okamis==true){ // par defaut on n'importe pas la liste d'amis.
						$amis=$profil_xml->xpath('amis/boat');
						// DEBUG
						// echo "<br />:: import_positions.php :: Ligne 341 : AMIS : ".$amis[$i]->pseudo."\n";
						$t_liste_amis[]=trim($amis[$i]->pseudo);
						
						for ($i=0; $i<count($amis); $i++){
							// DEBUG
							// echo $amis[$i]->pseudo.", \n";
							$t_liste_amis[]=trim($amis[$i]->pseudo);
						}
						$un_voilier->SetListeAmis($t_liste_amis);
					}
				}
			}
			fclose($handle0);
		}
	}
	return $un_voilier;
}
return false;
}


?>