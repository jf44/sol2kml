<?php

// ######################### Fichier KLM ##########################


// --------------------
function GenereKML(){
// génere le fichier courant à charger dans Google Earth
global $url_serveur;
global $fichier_kml_courant;
global $fichier_kml_cache;
global $extension_kml;
	$s='<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Folder>
    <NetworkLink>
      <refreshVisibility>0</refreshVisibility>
      <flyToView>1</flyToView>
      <Link>
        <href>'.$url_serveur.'/'.$fichier_kml_cache.$extension_kml.'</href>
        <refreshInterval>1800</refreshInterval>
        <viewRefreshMode>onRequest</viewRefreshMode>
      </Link>
    </NetworkLink>
  </Folder>
</kml>
';

	$fp_data = fopen($fichier_kml_courant.$extension_kml, 'w');
	if ($fp_data ){
		fwrite($fp_data, $s);
		fclose($fp_data);
	}
}


// --------------------
function GenereEnteteKML($longitude, $latitude){
global $groupe; // groupe courant determine aussi le prefixe du fichier KML
global $t_groupe; // code du groupe
	$s='<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.1">
<Folder>
<name>'.strtoupper($t_groupe[$groupe]).'</name>
<open>1</open>
<LookAt>
<longitude>'.$longitude.'</longitude>
<latitude>'.$latitude.'</latitude>
<altitude>0</altitude>
<range>1000000</range>
<tilt>0</tilt>
<heading>0</heading>
</LookAt>
';
return $s;
}


// --------------------
function GenereStylesBateauxKML(){
global $url_serveur;
	$s='
<Style id="Bato_grumeau">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_grumeau.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>

<Style id="Bato_bleu">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_bleu.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_rouge">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_rouge.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_vert">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_vert.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_jaune">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_jaune.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_mauve">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_mauve.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_noir">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_noir.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_blanc">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_blanc.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>

<Style id="Bato_bleu_fr">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_bleu_fr.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_rouge_fr">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_rouge_fr.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_vert_fr">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_vert_fr.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_jaune_fr">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_jaune_fr.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_mauve_fr">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_mauve_fr.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_noir_fr">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_noir_fr.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_blanc_fr">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_blanc_fr.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>

<Style id="Bato_bleu_it">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_bleu_it.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_rouge_it">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_rouge_it.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_vert_it">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_vert_it.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_jaune_it">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_jaune_it.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_mauve_it">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_mauve_it.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_noir_it">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_noir_it.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_blanc_it">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_blanc_it.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>

<Style id="Bato_bleu_pf">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_bleu_pf.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_rouge_pf">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_rouge_pf.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_vert_pf">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_vert_pf.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_jaune_pf">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_jaune_pf.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_mauve_pf">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_mauve_pf.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_noir_pf">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_noir_pf.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_blanc_pf">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_blanc_pf.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>

<Style id="Bato_rouge_mc">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_blanc_be.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_rouge_pf">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_rouge_mc.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>
<Style id="Bato_bleu_uk">
<IconStyle>
<scale>1.0</scale>
<Icon>
<href>'.$url_serveur.'/sources/bato_bleu_uk.gif</href>
</Icon>
</IconStyle>
<LabelStyle>
<scale>0.7</scale>
</LabelStyle>
</Style>

';

	return $s;
}


// --------------------
function GenereMarquesParcoursEtDebutPositionsBateauxKML($okmarques=true){
global $INDEX_COURSE;
global $VOR_LEG;
global $url_serveur;
global $groupe;
global $course;
global $t_groupe;
global $url_fichier_marque;
$s='';
	if ($okmarques){
	/*
		if ($course==$INDEX_COURSE){
			$s.=GenereMarquesParcours_bostn_galway_rkn();
		}
		else{
			$s.=GenereMarquesParcours_vor($VOR_LEG);
		}

Peut etre remplace par 
*/
		$s='
	<NetworkLink id="MarquesStyles">
		<name>Marques de parcours</name>
		<refreshVisibility>1</refreshVisibility>
		<flyToView>0</flyToView>
		<Link id="vor">
			<href>';
	$s.=$url_serveur.$url_fichier_marque; // $url_fichier_marque='/sources/vor_styles_marques.kml';
	$s.='</href>
			<refreshMode>onChange</refreshMode>   
		</Link>
	</NetworkLink>
';

	}
	
	$s.='<Folder>
<name>'.strtoupper($t_groupe[$groupe]).'_Position</name>
';

	return $s;
}

// --------------------
function GenereBateauKML($bato){
// drapeau intégré à l'image
global $LE_GRUMEAU;
global $groupe;
global $t_groupe;
$s='';
	if ($bato->pseudo!=$LE_GRUMEAU){
		$s.='<Placemark>
<name>'.$bato->pseudo.'</name>
<description>
<![CDATA[
ID: '.$bato->id.' 
Date: '.$bato->date_enregistrement.'
<br>Lon: '.$bato->longitude.'
<br>Lat: '.$bato->latitude.'
<br>Cap: '.$bato->cap.'
<br>Vitesse: '.$bato->vitesse.'
<br>Classement VOR: '.$bato->classement.'
<br>Rang '.strtoupper($t_groupe[$groupe]).' :'.$bato->rang.'
]]></description>
<LookAt>
<longitude>'.$bato->longitude.'</longitude>
<latitude>'.$bato->latitude.'</latitude>
<altitude>0</altitude>
<range>1000000</range>
<tilt>0</tilt>
<heading>'.$bato->cap.'</heading>
<altitudeMode>relativeToGround</altitudeMode>
</LookAt>
';
		if ($bato->drapeau){ // Le drapeau
			$s.='
<styleUrl>#Bato_'.$bato->couleur1.'_'.$bato->drapeau.'</styleUrl>
';
		}
		else{
			$s.='
<styleUrl>#Bato_'.$bato->couleur1.'</styleUrl>
';
		}
		$s.='<Point>
<coordinates>'.$bato->longitude.','.$bato->latitude.',0</coordinates>
</Point>
</Placemark>
';
	}
	else{
		$s.='<Placemark>
<name>Le Grumeau</name>
<description>
<![CDATA[
<br>Lon: '.$bato->longitude.'
<br>Lat: '.$bato->latitude.'
<br>Cap: '.$bato->cap.'
<br>Vitesse: '.$bato->vitesse.'
<br>Classement VOR: '.$bato->classement.'
]]></description>
<LookAt>
<longitude>'.$bato->longitude.'</longitude>
<latitude>'.$bato->latitude.'</latitude>
<altitude>0</altitude>
<range>1000000</range>
<tilt>0</tilt>
<heading>0</heading>
<altitudeMode>relativeToGround</altitudeMode>
</LookAt>
<styleUrl>#Bato_grumeau</styleUrl>
<Point>
<coordinates>'.$bato->longitude.','.$bato->latitude.',0</coordinates>
</Point>
</Placemark>
';
	}

	return $s;
}

// ########################## Drapeaux comme PlaceMark

/*
// --------------------
function insere_drapeau($drapeau){
global $t_drapeaux;
	$i=0;
	$trouve=false;
	while (($i<count($t_drapeaux)) && ($trouve==false)){
		if ($t_drapeaux[$i]==$drapeau){
			$trouve=true;
		}
		$i++;
	}
	
	if ($trouve==false){ // insertion
		$t_drapeaux[$i]=$drapeau;
	}
}

// --------------------
function StyleDrapeau($drapeau){
$s='
<Style id="drapeau_'.$drapeau.'">
<IconStyle>
<scale>0.25</scale>
<Icon>
<href>http://static.volvooceanracegame.org/images/profile_flags/'.$drapeau.'.gif</href>
</Icon>
</IconStyle>
</Style>
';
return $s;
}

// --------------------
function GenereStylesDrapeauxKML($t_voilier){
// cree un fichier KML de styles des drapeaux nationaux
global $t_drapeaux;
reset($t_drapeaux);
	
	$s='';
	// lister les drapeaux
	$i=0;
	while ($i<count($t_voilier)){
		insere_drapeau($t_voilier[$i]->drapeau);
		$i++;
	}
	// creer les styles correspondants
	if (count($t_drapeaux)>0){ 
		$i=0;
		while ($i<count($t_drapeaux)){
			$s.=StyleDrapeau($t_drapeaux[$i]);
			$i++;
		}
	}

	return $s;
}

// --------------------
function GenereBateauKML($bato){
// drapeau comme un objet Placemark
global $LE_GRUMEAU;
$s='';

	if ($bato->pseudo!=$LE_GRUMEAU){
		$s.='<Placemark>
<name>'.$bato->pseudo.'</name>
<description>
<![CDATA[
ID: '.$bato->id.' 
Date: '.$bato->date_enregistrement.'
<br>Lon: '.$bato->longitude.'
<br>Lat: '.$bato->latitude.'
<br>Cap: '.$bato->cap.'
<br>Vitesse: '.$bato->vitesse.'
<br>Classement VOR: '.$bato->classement.'
<br>Rang RKN:'.$bato->rang.'
]]></description>
<LookAt>
<longitude>'.$bato->longitude.'</longitude>
<latitude>'.$bato->latitude.'</latitude>
<altitude>0</altitude>
<range>1000000</range>
<tilt>0</tilt>
<heading>'.$bato->cap.'</heading>
<altitudeMode>relativeToGround</altitudeMode>
</LookAt>
<styleUrl>#Bato_'.$bato->couleur1.'</styleUrl>
<Point>
<coordinates>'.$bato->longitude.','.$bato->latitude.',0</coordinates>
</Point>
</Placemark>
';
	}
	else{
		$s.='<Placemark>
<name>Le Grumeau</name>
<description>
<![CDATA[
<br>Lon: '.$bato->longitude.'
<br>Lat: '.$bato->latitude.'
<br>Cap: '.$bato->cap.'
<br>Vitesse: '.$bato->vitesse.'
<br>Classement VOR: '.$bato->classement.'
]]></description>
<LookAt>
<longitude>'.$bato->longitude.'</longitude>
<latitude>'.$bato->latitude.'</latitude>
<altitude>0</altitude>
<range>1000000</range>
<tilt>0</tilt>
<heading>0</heading>
<altitudeMode>relativeToGround</altitudeMode>
</LookAt>
<styleUrl>#Bato_grumeau</styleUrl>
<Point>
<coordinates>'.$bato->longitude.','.$bato->latitude.',0</coordinates>
</Point>
</Placemark>
';
	}
	if ($bato->drapeau){ // Le drapeau
		$long_flag=$bato->longitude;
		$lat_flag=$bato->latitude;
		$s.='<Placemark>
<description>
<![CDATA[
Pavillon: '.$bato->pseudo.'_'.$bato->drapeau.'
]]></description>
<LookAt>
<longitude>'.$long_flag.'</longitude>
<latitude>'.$lat_flag.'</latitude>
<altitude>10000</altitude>
<range>100000</range>
<tilt>0</tilt>
<heading>0</heading>
<altitudeMode>relativeToGround</altitudeMode>
</LookAt>
<styleUrl>#drapeau_'.$bato->drapeau.'</styleUrl>
<Point>
<coordinates>'.$long_flag.','.$lat_flag.',0</coordinates>
</Point>
</Placemark>
';
	}

	return $s;
}
*/

// -------------------------
function GenereEnQueueKML(){
	$s='</Folder>
</Folder>
</kml>
';
	return $s;
}

// -----------------------
function EnregistreKML($contenu){
// Deux fichiers sont crees : un fichier d'archive et un fichier courant (dit de cache) au contenu identique.
// c'est ce fichier de cache (dont le nom est toujours identique) qui est appelé par le fichier rkn.kml lu par GoogleEarth

global $dir_serveur;
global $fichier_kml_courant;
global $fichier_kml_cache;
global $extension_kml;

	// archive
	// le nom du fichier d'archive recoit une date+heure qui sera utilisee 
	// pour verifier si le delai depuis la génération précédente est suffisant
	$f_data_name=$dir_serveur.'/'.$fichier_kml_courant.date('YmdH').$extension_kml;
	$fp_data = fopen($f_data_name, 'w');
	if ($fp_data ){
		fwrite($fp_data, $contenu);
		fclose($fp_data);
	}
	// le contenu du cache
	$f_cache_name=$dir_serveur.'/'.$fichier_kml_cache.$extension_kml;
	$fp_data2 = fopen($f_cache_name, 'w');
	if ($fp_data2 ){
		fwrite($fp_data2, $contenu);
		fclose($fp_data2);
	}
	// astuce pour forcer la mise a jour depuis GoogleEarth : le fichier rkn.kml appelle le fichier de cache
	echo '<br>Exportation achev&eacute;e. Le fichier <a href="'.$fichier_kml_courant.$extension_kml.'"><b>'.$fichier_kml_courant.$extension_kml.'</b></a> a &eacute;t&eacute; actualis&eacute; dans Google Earth &reg;'."\n";
}

// -----------------------
function ExisteKML_2D(){
// verifie si une generation a ete faite durant l'heure courante
global $dir_serveur;
global $fichier_kml_courant;
global $fichier_kml_cache;
global $extension_kml;

	$f_data_name=$dir_serveur.'/'.$fichier_kml_courant.date('YmdH').$extension_kml;

	// DEBUG
	// echo "<br>Fichier courant: $f_data_name\n";
	
	if (file_exists($f_data_name)){
		return $f_data_name;
	}
	else{
		return '';
	}
}



?>