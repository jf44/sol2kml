<?php
// JF

// Importation des positions des bateaux depuis MP
// Les données de latitude et longitude sont en système décimal comme dans Google Maps. et Google Earth.
// Utilise xmlize car supporte par tout serveur. Si SimpleXML supporté par serveur préferer import_positions_simplexml.php car plus souple

require_once( "xmlize.php" );

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
// recupere la position du bateau sans stocker ni trajectoire ni amis
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
				$profil_xml = xmlize($profil, 0 ); // basique mais efficace
				/*
				echo "<br>DEBUG:: 396";
				traverse_xmlize($profil_xml, 'profil_xml_'); // affcihe la structure du fichier
 				print '<pre>' . implode("", $GLOBALS['traverse_array']) . '</pre>';
				exit;
				*/
				if ($profil_xml){
					
			        $re = new stdClass();
			        $re->id=$profil_xml['user']['@']['id_user']; // id user
					$re->date_enregistrement=$profil_xml['user']['@']['date']; // "2009-04-07 08:46:49"
					$re->pseudo=$profil_xml['user']['#']['pseudo']['0']['#']; // nom du bateau
					$re->couleur_coque=$profil_xml['user']['#']['couleur1']['0']['#']; // coque
					$re->couleur_vav=$profil_xml['user']['#']['couleur2']['0']['#']; // GV
					$re->couleur_gv=$profil_xml['user']['#']['couleur3']['0']['#']; // VoileAvant
					$re->couleur_spi=$profil_xml['user']['#']['couleur4']['0']['#']; // Spi
					$re->couleur_spi2=$profil_xml['user']['#']['couleur5']['0']['#']; // Spi 2
					$re->drapeau=$profil_xml['user']['#']['country']['0']['#']; // pavillon
					
					// Position
					$re->latitude=$profil_xml['user']['#']['position']['0']['#']['latitude']['0']['#']; // latitude
					$re->longitude=$profil_xml['user']['#']['position']['0']['#']['longitude']['0']['#']; // longitude
					$re->cap=$profil_xml['user']['#']['position']['0']['#']['cap']['0']['#']; // cap
					$re->vitesse=$profil_xml['user']['#']['position']['0']['#']['vitesse']['0']['#']; // vitesse
					$re->classement=$profil_xml['user']['#']['position']['0']['#']['classement']['0']['#']; // longitude
					$re->spi=$profil_xml['user']['#']['position']['0']['#']['spi']['0']['#']; // spi
					$re->voile=$profil_xml['user']['#']['position']['0']['#']['voile']['0']['#']; // voile 1,2,4,8,16,32
					$re->voiles_cassees=$profil_xml['user']['#']['position']['0']['#']['voiles_cassees']['0']['#']; // 0,1
					$re->wind_speed=$profil_xml['user']['#']['position']['0']['#']['wind_speed']['0']['#']; // flot
					$re->wind_angle=$profil_xml['user']['#']['position']['0']['#']['wind_angle']['0']['#']; // °
					$re->rang=$rang;
					
					// importer les trajectoire
					$re->trajectoire=$profil_xml['user']['#']['trajectoire']['0']['#']; // "-3.33432!47.2949;-3.56244!46.7142;-3.82456!46.5706;-4.59243!46.2514;-4.88353!46.1594;-5.96411!46.1468;-6.38087!46.1561;-6.53319!45.9274;"
			        // importer les amis
			    	// echo "<br>\n";
					// print_r($profil_xml['user']['#']['amis']['0']['#']['boat']);
					// echo "<br>\n";
					while ($i<count($profil_xml['user']['#']['amis']['0']['#']['boat'])){
						if ($profil_xml['user']['#']['amis']['0']['#']['boat'][$i]['#']['pseudo']['0']['#']!=""){
							$re->amis[$i] = $profil_xml['user']['#']['amis']['0']['#']['boat'][$i]['#']['pseudo']['0']['#']; // "Pollen Nine"
						}
						$i++;
					}
					/*
					// print_r($re);
					echo "<br><br>ID:$re->id<br>
					Pseudo:$re->pseudo<br>
		Date:$re->date_enregistrement<br>
		couleur1:$re->couleur_coque<br> 
		couleur2:$re->couleur_vav<br>
		couleur3:$re->couleur_gv<br>
		couleur4:$re->couleur_spi<br>
		couleur5:$re->couleur_spi2<br>
		Pavillon:$re->drapeau
<br>Position<br>LON:$re->longitude<br>LAT: $re->latitude<br>CAP: $re->cap<br>VITESSE: $re->vitesse<br>RANG: $re->classement
<br>Voiles : SPI:".$re->spi."; TYPE:".$re->voile."; CASSEES:".$re->voiles_cassees.";<br>Vent<br>Vitesse:".$re->wind_speed." Nds;<br>Angle: ".$re->wind_angle." °
<br>Trajectoire<br>$re->trajectoire
<br>Amis<br>\n";
		print_r($re->amis);
		echo "<br>\n";
		exit;
		*/
					$un_voilier= new Voilier();
					
					// Drapeau national ?
					if ($drapeau==''){ // on ne force pas le drapeau
						if (isset($re->drapeau) && ($re->drapeau!='')){ // on recupere l'info stockee
							$drapeau=$re->drapeau;  
						}
					}
					
					if ($re->pseudo!=$LE_GRUMEAU){
						$un_voilier->SetPosition($re->id,
						$re->pseudo,
						$re->date_enregistrement,
						$re->latitude,
						$re->longitude,
						$re->cap,
						$re->vitesse,
						$re->classement,
						$re->spi,
						$re->voile,
						$re->voiles_cassees,
						$re->wind_speed,
						$re->wind_angle,
						$re->couleur_coque,
						$re->couleur_vav,
						$re->couleur_gv,
						$re->couleur_spi,
						$re->couleur_spi2,
						$drapeau);
						$un_voilier->SetRang($rang);
						$rang++;
					}
					
					else{ // le Grumeau est un Ka
						$un_voilier->SetPosition($re->id,
						$re->pseudo,
						$re->date_enregistrement,
						$re->latitude,
						$re->longitude,
						$re->cap,
						$re->vitesse,
						$re->classement,
						$re->spi,
						$re->voile,
						$re->voiles_cassees,
						$re->wind_speed,
						$re->wind_angle,
						$re->couleur_coque,
						$re->couleur_vav,
						$re->couleur_gv,
						$re->couleur_spi,
						$re->couleur_spi2,
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
global $ok_marque_passage, $lon_marque, $lat_marque, $mode_passage;

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
				// $profil_xml = new SimpleXMLElement($profil); // non supporte par de nombreux serveurs
				
				$profil_xml = xmlize($profil, 0 ); // basique mais efficace
				/*
				echo "<br>DEBUG:: 396";
				traverse_xmlize($profil_xml, 'profil_xml_'); // affcihe la structure du fichier
 				print '<pre>' . implode("", $GLOBALS['traverse_array']) . '</pre>';
				exit;
				*/
				if ($profil_xml){
		
        $re = new stdClass();
		$re->id=$profil_xml['user']['@']['id_user']; // id user
		$re->date_enregistrement=$profil_xml['user']['@']['date']; // "2009-04-07 08:46:49"
		$re->pseudo=$profil_xml['user']['#']['pseudo']['0']['#']; // nom du bateau
		$re->couleur_coque=$profil_xml['user']['#']['couleur1']['0']['#']; // coque
		$re->couleur_vav=$profil_xml['user']['#']['couleur2']['0']['#']; // GV
		$re->couleur_gv=$profil_xml['user']['#']['couleur3']['0']['#']; // VoileAvant
		$re->couleur_spi=$profil_xml['user']['#']['couleur4']['0']['#']; // Spi
		$re->couleur_spi2=$profil_xml['user']['#']['couleur5']['0']['#']; // Spi 2
		$re->drapeau=$profil_xml['user']['#']['country']['0']['#']; // pavillon
		
		// Position
		$re->latitude=$profil_xml['user']['#']['position']['0']['#']['latitude']['0']['#']; // latitude
		$re->longitude=$profil_xml['user']['#']['position']['0']['#']['longitude']['0']['#']; // longitude
		$re->cap=$profil_xml['user']['#']['position']['0']['#']['cap']['0']['#']; // cap
		$re->vitesse=$profil_xml['user']['#']['position']['0']['#']['vitesse']['0']['#']; // vitesse
		$re->classement=$profil_xml['user']['#']['position']['0']['#']['classement']['0']['#']; // longitude
		$re->spi=$profil_xml['user']['#']['position']['0']['#']['spi']['0']['#']; // spi
		$re->voile=$profil_xml['user']['#']['position']['0']['#']['voile']['0']['#']; // voile 1,2,4,8,16,32
		$re->voiles_cassees=$profil_xml['user']['#']['position']['0']['#']['voiles_cassees']['0']['#']; // 0,1
		$re->wind_speed=$profil_xml['user']['#']['position']['0']['#']['wind_speed']['0']['#']; // flot
		$re->wind_angle=$profil_xml['user']['#']['position']['0']['#']['wind_angle']['0']['#']; // °
		$re->rang=$rang;
		
		// importer les trajectoire
		$re->trajectoire=$profil_xml['user']['#']['trajectoire']['0']['#']; // "-3.33432!47.2949;-3.56244!46.7142;-3.82456!46.5706;-4.59243!46.2514;-4.88353!46.1594;-5.96411!46.1468;-6.38087!46.1561;-6.53319!45.9274;"
		/*
		// print_r($re);
		echo "<br><br>ID:$re->id<br>
		Pseudo:$re->pseudo<br>
		Date:$re->date_enregistrement<br>
		couleur1:$re->couleur_coque<br> 
		couleur2:$re->couleur_vav<br>
		couleur3:$re->couleur_gv<br>
		couleur4:$re->couleur_spi<br>
		couleur5:$re->couleur_spi2<br>
		Pavillon:$re->drapeau
<br>Position<br>LON:$re->longitude<br>LAT: $re->latitude<br>CAP: $re->cap<br>VITESSE: $re->vitesse<br>RANG: $re->classement
<br>Voiles : SPI:".$re->spi."; TYPE:".$re->voile."; CASSEES:".$re->voiles_cassees.";<br>Vent<br>Vitesse:".$re->wind_speed." Nds;<br>Angle: ".$re->wind_angle." °
<br>Trajectoire<br>$re->trajectoire
<br>Amis<br>\n";
		print_r($re->amis);
		echo "<br>\n";
		exit;
		*/
					$un_voilier= new Voilier();
					
					// Drapeau national ?
					if ($drapeau==''){ // on ne force pas le drapeau
						if (isset($re->drapeau) && ($re->drapeau!='')){ // on recupere l'info stockee
							$drapeau=$re->drapeau;  
						}
					}
					
					if ($re->pseudo!=$LE_GRUMEAU){
						$un_voilier->SetPosition($re->id,
						$re->pseudo,
						$re->date_enregistrement,
						$re->latitude,
						$re->longitude,
						$re->cap,
						$re->vitesse,
						$re->classement,
						$re->spi,
						$re->voile,
						$re->voiles_cassees,
						$re->wind_speed,
						$re->wind_angle,
						$re->couleur_coque,
						$re->couleur_vav,
						$re->couleur_gv,
						$re->couleur_spi,
						$re->couleur_spi2,
						$drapeau);
						$un_voilier->SetRang($rang);
						$rang++;
					}
					
					else{ // le Grumeau est un Ka
						$un_voilier->SetPosition($re->id,
						$re->pseudo,
						$re->date_enregistrement,
						$re->latitude,
						$re->longitude,
						$re->cap,
						$re->vitesse,
						$re->classement,
						$re->spi,
						$re->voile,
						$re->voiles_cassees,
						$re->wind_speed,
						$re->wind_angle,
						$re->couleur_coque,
						$re->couleur_vav,
						$re->couleur_gv,
						$re->couleur_spi,
						$re->couleur_spi2,
						$drapeau);
						$un_voilier->SetRang(0);
					}
					// Trajectoire
					if ($oktrajectoire==true){ // par defaut on n'importe pas la trajectoire.
						// DEBUG
						// echo "<br />DEBUG :: import_positions.php :: Ligne 333 : Trajectoire :".$xmluser->trajectoire."\n";
						// 	<trajectoire>120.45!35.7402;121.513!35.6521;121.723!35.7717;</trajectoire>
						$un_voilier->SetTrajectoire($re->trajectoire);
						if ($ok_marque_passage){
							$un_voilier->SetMarqueValide($lon_marque, $lat_marque, $mode_passage);
						}
					}
					// Amis
					if ($okamis==true){ // par defaut on n'importe pas la liste d'amis.
				        // importer les amis
				    	// echo "<br>\n";
						// print_r($profil_xml['user']['#']['amis']['0']['#']['boat']);
						// echo "<br>\n";
						$re->amis=array();
						$boats=$profil_xml['user']['#']['amis']['0']['#']['boat'];
						if (isset($boats) && ($boats)){
							while ($i<count($boats)){
								if ($boats[$i]['#']['pseudo']['0']['#']!=""){
									$re->amis[] = $boats[$i]['#']['pseudo']['0']['#']; // "Pollen Nine"
								}
								$i++;
							}
							if ($re->amis){
								$un_voilier->SetListeAmis($re->amis);
							}
						}
					}
					// Enregistrer les donnees du voilier dans le cache 
					sauve_cache_data_voilier($un_voilier);
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