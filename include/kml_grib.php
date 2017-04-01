<?php

// JF

// Génération KML des  Grib en 2D
/*
	var $id;
	var $longitude;
	var $latitude;
	var $twd;
	var $tws;
	var $taille;
	var $couleur; // RRVVBB
	var $modele;

*/

define ('MAX_BARBULE', 4); // maximum de barbule sur une flèche

// --------------------
function rgb2kml($couleur, $transparence='ff'){
// conversion d'une couleur RVB "ffaa33" en hexadecimal + transparence en tête
// The order of expression is aabbggrr, where aa=alpha (00 to ff); bb=blue (00 to ff); gg=green (00 to ff); rr=red (00 to ff).
	$hex=$transparence;
	if (!empty($couleur) && strlen($couleur)>=6){
		$r= substr($couleur,0,2);
        $g= substr($couleur,2,2);
		$b= substr($couleur,4,2);
		return $transparence.$b.$g.$r;
	}

}


// --------------------
function GetCorps($tws, $twd, $lon, $lat, $taille, $color, $echelle){
	$twdr = $twd * M_PI / 180.0; // conversion en radian
	$cos = cos($twdr);
	$sin = sin($twdr);
	$lon_extremite= $lon + $taille * $echelle * $cos;
    $lat_extremite= $lat + $taille * $echelle * $sin;
	$s='
		<Placemark>
			<name>TWS: '.$tws.' TWD: '.$twd.'</name>
			<Style>
				<LineStyle>
					<color>'.rgb2kml($color).'</color>
					<width>2</width>
				</LineStyle>
			</Style>
			<LineString>
				<extrude>1</extrude>
				<tessellate>1</tessellate>
				<coordinates>
';
	$s.=$lon.','.$lat.',0 '.$lon_extremite.','.$lat_extremite.',0';
	$s.='
				</coordinates>
			</LineString>
		</Placemark>
';
	return ($s);
}

// --------------------
function GetPointe($tws, $twd, $lon, $lat, $taille, $color, $echelle){
	$twdr = $twd * M_PI / 180.0; // conversion en radian
	$cos = cos($twdr);
	$sin = sin($twdr);
    $cos5pi4 = cos( $twdr + 5.0 *  M_PI / 4.0);
	$sin5pi4 = sin( $twdr + 5.0 *  M_PI / 4.0);
    $cos3pi4 = cos( $twdr + 3.0 *  M_PI / 4.0);
	$sin3pi4 = sin( $twdr + 3.0 *  M_PI / 4.0);

	$lon_pointe = $lon + $taille * $cos * $echelle;
    $lat_pointe = $lat + $taille * $sin * $echelle;
	$lon_extremite0 = $lon_pointe  + $taille * $cos5pi4 * $echelle * 0.3; // cos($twdr+PI/2)
    $lat_extremite0 = $lat_pointe  + $taille * $sin5pi4 * $echelle * 0.3; // sin($twdr+PI/2)
	$lon_extremite1 = $lon_pointe + $taille * $cos3pi4 * $echelle * 0.3; // cos($twdr-PI/2)
    $lat_extremite1 = $lat_pointe + $taille * $sin3pi4 * $echelle * 0.3; // sin($twdr-PI/2)
    $s='	<Placemark>
			<name>TWS: '.$tws.' TWD: '.$twd.'</name>
			<Style>
				<LineStyle>
					<color>'.rgb2kml($color).'</color>
					<width>2</width>
				</LineStyle>
			</Style>
			<LineString>
				<extrude>1</extrude>
				<tessellate>1</tessellate>
				<coordinates>
';
$s.=$lon_extremite0.','.$lat_extremite0.',0 '.$lon_pointe.','.$lat_pointe.',0 '.$lon_extremite1.','.$lat_extremite1.',0';
	$s.='
				</coordinates>
			</LineString>
		</Placemark>
';
	return ($s);
}

// --------------------
function GetEmpenage($nbarbules, $tws, $twd, $lon, $lat, $taille, $color, $echelle){
	$twdr= $twd * M_PI / 180.0; // conversion en radian
	$t_lon0 = array();
    $t_lat0 = array();
    $t_lon1 = array();
    $t_lat1 = array();
    $cos = cos($twdr);
	$sin = sin($twdr);
	$cospi = cos($twdr + M_PI / 2.0);
	$sinpi = sin($twdr + M_PI / 2.0);

 	for ($i=0; $i<$nbarbules; $i++){
        $t_lon0[$i] = $lon + $taille * $cos * $echelle * 0.05 * ($i);    // Au pif
        $t_lat0[$i] = $lat + $taille * $sin * $echelle * 0.05 * ($i);
//        $t_lon1[$i] = $lon + $taille * $cos * $echelle * 0.1 * ($i) + $taille * $cospi * $echelle * 0.5 * ($nbarbules-$i);  // cos($twdr+PI/4)
//        $t_lat1[$i] = $lat + $taille * $sin * $echelle * 0.1 * ($i) + $taille * $sinpi * $echelle * 0.5 * ($nbarbules-$i); // sin($twdr+PI/4)
        $t_lon1[$i] = $lon + $taille * $cos * $echelle * 0.05 * ($i) + ($taille * 0.1) * $cospi * $echelle * ($nbarbules-$i);  // cos($twdr+PI/4)
        $t_lat1[$i] = $lat + $taille * $sin * $echelle * 0.05 * ($i) + ($taille * 0.1) * $sinpi * $echelle * ($nbarbules-$i); // sin($twdr+PI/4)
	}

	$s='';
	for ($i=0; $i<$nbarbules; $i++){
		$s.= '
		<Placemark>
			<name>TWS: '.$tws.' TWD: '.$twd.'</name>
			<Style>
				<LineStyle>
					<color>'.rgb2kml($color).'</color>
					<width>2</width>
				</LineStyle>
			</Style>
			<LineString>
				<extrude>1</extrude>
				<tessellate>1</tessellate>
				<coordinates>';
	$s.= $t_lon0[$i].','.$t_lat0[$i].',0 '.$t_lon1[$i].','.$t_lat1[$i].',0';
    $s.='</coordinates>
			</LineString>
		</Placemark>
';
	}
	return ($s);
}


// --------------------
function GetLosange($tws, $twd, $lon, $lat, $taille, $color, $echelle){
	$delta = (float) $taille * $echelle * 0.1;
	// DEBUG
	// echo '<br />TWS: '.$tws.' TWD: '.$twd.' Lon: '.$lon.' Lat: '.$lat.' Taille: '.$taille.' Color: '.$color.' Echelle: '.$echelle."\n";
	// echo '<br />DELTA: '.$delta;
	// echo '<br />LOSANGE: ';
	$lon1 =(float) $lon - $delta;
    $lon2 =(float) $lon + $delta;
    $lat1 =(float) $lat - $delta;
    $lat2 =(float) $lat + $delta;
	$lon3 =(float) $lon - 3 * $delta;
    $lon4 =(float) $lon + 3 * $delta;
    $lat3 =(float) $lat - 3 * $delta;
    $lat4 =(float) $lat + 3 * $delta;
	// echo '<br /> '.$lon1.','.$lat.',0 ';
	// echo '<br /> '.$lon.','.$lat1.',0 ';
	// echo '<br /> '.$lon2.','.$lat.',0 ';
	// echo '<br /> '.$lon.','.$lat2.',0 ';
	// echo '<br /> '.$lon1.','.$lat.',0 '."\n";

	$s='';
	$s.= '
		<Placemark>
			<name>TWS: '.$tws.' TWD: '.$twd.'</name>
			<Style>
				<LineStyle>
					<color>'.rgb2kml($color).'</color>
					<width>2</width>
				</LineStyle>
			</Style>
			<LineString>
				<extrude>1</extrude>
				<tessellate>1</tessellate>
				<coordinates>';
	$s.= $lon1.','.$lat.',0 '.$lon.','.$lat1.',0 '.$lon2.','.$lat.',0 '.$lon.','.$lat2.',0 '.$lon1.','.$lat.',0 ';
    $s.='</coordinates>
			</LineString>
		</Placemark>
';
	$s.= '
		<Placemark>
			<name>TWS: '.$tws.' TWD: '.$twd.'</name>
			<Style>
				<LineStyle>
					<color>'.rgb2kml($color).'</color>
					<width>2</width>
				</LineStyle>
			</Style>
			<LineString>
				<extrude>1</extrude>
				<tessellate>1</tessellate>
				<coordinates>';
    $s.= $lon3.','.$lat.',0 '.$lon.','.$lat3.',0 '.$lon4.','.$lat.',0 '.$lon.','.$lat4.',0 '.$lon3.','.$lat.',0 ';
    $s.='</coordinates>
			</LineString>
		</Placemark>
';

	return ($s);
}


// --------------------
function GetModeleBarb($barb, $tws, $twd, $lon0, $lat0, $taille, $color, $echelle){
// retourne une serie de PlaceMark barbule
	$s='';
	switch ($barb){
		case '0' : // 'fleche';
			$s.=GetCorps($tws, $twd, $lon0, $lat0, $taille, $color, $echelle);
			$s.=GetPointe($tws, $twd, $lon0, $lat0, $taille, $color, $echelle);
			break;
		default :  // barbule
            if ($tws>35.0){  $nbarbules=5; }
			else if ($tws>25.0) {  $nbarbules=4; }
			else if ($tws>15.0) {  $nbarbules=3; }
            else if ($tws>10.0) {  $nbarbules=2; }
            else if ($tws>5.0) {  $nbarbules=1; }
			else {  $nbarbules=0; }
			if (($tws>3.0)){
				$s.=GetCorps($tws, $twd, $lon0, $lat0, $taille, $color, $echelle);
				$s.=GetEmpenage($nbarbules, $tws, $twd, $lon0, $lat0, $taille, $color, $echelle);
			}
			else {
                $s.=GetLosange($tws, $twd, $lon0, $lat0, $taille, $color, $echelle);
			}
			break;
	}
	return $s;
}

// --------------------
function GetInfoBarb($barb, $tws, $twd, $lon0, $lat0, $echelle){
// retourne un PlaceMark barbule

    $twd180=$twd-180.0;
	$s='';
	$s.= '
	<Placemark>
		<name>TWS: '.$tws.' TWD: '.$twd.'</name>
		<open>0</open>
		<description>
			<![CDATA[<b>Lon: '.$lon0.', Lat: '.$lat0.'</b>]]>
		</description>
		<LookAt>
			<longitude>'.$lon0.'</longitude>
			<latitude>'.$lat0.'</latitude>
			<altitude>0</altitude>
			<heading>'.$twd180.'</heading>
			<tilt>0</tilt>
			<range>1245000</range>
            <altitudeMode>relativeToGround</altitudeMode>
		</LookAt>
        <styleUrl>#stylemap_id2</styleUrl>
		<Point>
			<altitudeMode>relativeToGround</altitudeMode>
			<coordinates>'.$lon0.','.$lat0.',0</coordinates>
		</Point>

        <Style>
			<LabelStyle>
    			<scale>0.65</scale>
			</LabelStyle>
        </Style>
	</Placemark>
';
	return $s;
}


// -------------------------
function GenereBarbuleKML($barbule, $echelle){
	$echelle *= 0.005;
	$s='';
	// source de difficulté et de broullage de l'affichage
	// $s.=GetInfoBarb($barbule->modele,  $barbule->tws, $barbule->twd, $barbule->longitude, $barbule->latitude, $echelle);
	$s.=GetModeleBarb($barbule->modele, $barbule->tws, $barbule->twd, $barbule->longitude, $barbule->latitude, $barbule->taille, $barbule->couleur, $echelle);
	return $s;
}

// -------------------------
function GenereEnQueueKML_Grib(){
global $t_parcours;
	$s='</Folder>
	</Folder>
</kml>
';
	return $s;
}

//----------------
function recopier_windbar($dossier_cible){
// copie la palette des vents
global $dir_serveur;
global $dossier_textures;
    $fnamein = $dir_serveur.'/sources_3d/'.$dossier_textures.'/windbar.png';
    $fnameout = $dir_serveur .'/'.$dossier_cible.'/'.$dossier_textures.'/windbar.png';
	if (!file_exists($fnameout) && file_exists($fnamein)){
		$fin = fopen($fnamein, 'rb');
        $fout = fopen($fnameout, 'wb');
		if ($fin && $fout){
			$ok=fwrite($fout, fread($fin, filesize($fnamein)));
			fclose($fin);
			fclose($fout);
			if ($ok){
				return true;
			}
		}
	}
    return false;
}

// -----------------------
function EnregistreKML_Grib($dossier_grib,  $contenu,  $archive=false, $al=NULL){
// Deux fichiers sont crees : un fichier d'archive et un fichier courant (dit de cache) au contenu mmsientique.
// c'est ce fichier de cache (dont le nom est toujours mmsientique) qui est appelé par le fichier rkn.kml lu par GoogleEarth
// Le dossier d'achive est zippé

global $dir_serveur;
global $fichier_kml_courant;
global $fichier_kml_cache;
global $extension_kml;
global $extension_kmz;
global $dossier_kml;
global $dossier_kmz;
global $dossier_modeles;
global $dossier_textures;

	$fichier_kml_cache_grib=$fichier_kml_cache.'Grib';
	// Commencer par enregister le fichier KML
	$f_cache_name=$dir_serveur .'/'.$dossier_kml.'/'.$dossier_grib.'/'.$fichier_kml_cache_grib.$extension_kml;
	$fp_data = fopen($f_cache_name, 'w');
	if ($fp_data ){
		fwrite($fp_data, $contenu);
		fclose($fp_data);
	}

	if ($archive){
		// faire une copie zippee du  dossier $dossier_grib 
		$t_fichiers=array();
		$t_fichiers[0]=$dossier_kml.'/'.$dossier_grib.'/'.$fichier_kml_cache_grib.$extension_kml;
		$t_fichiers[1]=$dossier_kml.'/'.$dossier_grib.'/'.$dossier_modeles;
		$t_fichiers[2]=$dossier_kml.'/'.$dossier_grib.'/'.$dossier_textures;

		if (creer_fichier_zip($dossier_kml.'/'.$dossier_grib, $t_fichiers, $fichier_kml_cache_grib)){
			// puis le renommer .kmz
			$nom_fichier_kmz=renommer_fichier($fichier_kml_cache_grib, $extension_kmz);
			// DEBUG
			//echo "<br>DEBUG :: kml_grib.php :: 357 :: $nom_fichier_kmz\n";
			if ($nom_fichier_kmz!=''){
				// creer un fichier d'archive
				// le nom du fichier d'archive recoit une date+heure qui sera utilisee 
				// pour verifier si le delai depuis la génération précédente est suffisant
				$f_name_cache=$dir_serveur.'/'.$nom_fichier_kmz;
				$f_archive=$dir_serveur.'/'.$dossier_kmz.'/'.nom_fichier($nom_fichier_kmz).date('YmdH').$extension_kmz;
				copy($f_name_cache, $f_archive);
                rename($f_name_cache, $dir_serveur.'/'.$dossier_kmz.'/'.$nom_fichier_kmz);

  				if ($al){
					echo $al->get_string('file_updated').' <a href="'.$dossier_kmz.'/'.$fichier_kml_cache_grib.$extension_kmz.'"><b>'.$fichier_kml_cache_grib.$extension_kmz.'</b></a>'."\n";
        			echo '<br />'.$al->get_string('file_zip').' <a href="'.$dossier_kmz.'/'.nom_fichier($nom_fichier_kmz).date('YmdH').$extension_kmz.'"><b>'.nom_fichier($nom_fichier_kmz).date('YmdH').$extension_kmz.'</b></a>'."\n";
				}
				else{
					echo 'File updated: <a href="'.$dossier_kmz.'/'.$fichier_kml_cache_grib.$extension_kmz.'"><b>'.$fichier_kml_cache_grib.$extension_kmz.'</b></a>'."\n";
					echo '<br />File zipped: <a href="'.$dossier_kmz.'/'.nom_fichier($nom_fichier_kmz).date('YmdH').$extension_kmz.'"><b>'.nom_fichier($nom_fichier_kmz).date('YmdH').$extension_kmz.'</b></a>'."\n";
				}
			}
		}
	}
	else{
		if ($al){
			echo $al->get_string('file_updated').' <a href="'.$dossier_kml.'/'.$dossier_grib.'/'.$fichier_kml_cache_grib.$extension_kml.'"><b>'.$fichier_kml_cache_grib.$extension_kml.'</b></a>'."\n";
		}
		else{
			echo 'File updated: '.' <a href="'.$dossier_kml.'/'.$dossier_grib.'/'.$fichier_kml_cache_grib.$extension_kml.'"><b>'.$fichier_kml_cache_grib.$extension_kml.'</b></a>'."\n";
		}
	}
}

// -----------------------
function ExisteKML_Grib(){
// verifie si une generation a ete faite durant l'heure courante
global $dir_serveur;
global $dossier_kmz;
global $dossier_grib;
global $fichier_kml_courant;
global $fichier_kml_cache;
global $extension_kml;
global $extension_kmz;

	$fichier_kml_cache_grib=$fichier_kml_cache.'Grib';

	$f_data_name=$dir_serveur.'/'.$dossier_kmz.'/'.$fichier_kml_cache_grib.date('YmdH').$extension_kmz;
	// DEBUG
	// echo "<br>Fichier courant: $f_data_name\n";
	
	if (file_exists($f_data_name)){
		return $f_data_name;
	}
	else{
		return '';
	}
}


// --------------------
function GenereEnteteKML_Grib($dossier_grib, $url_serveur, $grib_info){
/*
$grib_info = stdClass();
$grib_info->timestamp_deb;
$grib_info->timestamp_fin;
$grib_info->longitude_centre;
$grib_info->latitude_centre;
$grib_info->framecourante
*/

global $dir_serveur;
global $dossier_kml;
global $dossier_textures;

	$chemin=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_grib.'/'.$dossier_textures.'/';
	if ($url_serveur!=''){ // liens absolus
		$url=$url_serveur.'/'.$dossier_kml.'/'.$dossier_grib.'/'.$dossier_textures.'/';
	}
	else{ // liens relatifs
		$url=$dossier_textures.'/';
	}

// <href>http://maps.google.com/mapfiles/kml/shapes/arrow.png</href>
	$s='<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">
<Folder>
	<name>SailOnLine winds</name>
	<Snippet maxLines="2">'.date("Y/m/d H:i:s T",$grib_info->timestamp_deb).'</Snippet>
	<description><![CDATA[SailOnLine Wind Map<br>
Winds (knots)<br>
Simulation ending at:<br>&nbsp; &nbsp; '.date("Y/m/d H:i:s T",$grib_info->timestamp_fin).'<br>
Longitude min: '.$grib_info->longitude_min.'<br>
Latitude min: '.$grib_info->latitude_min.'<br>
Longitude max: '.$grib_info->longitude_max.'<br>
Latitude max: '.$grib_info->latitude_max.'<br>
Url: http://www.sailonline.org/]]></description>
	<LookAt>
		<longitude>'.$grib_info->longitude_centre.'</longitude>
		<latitude>'.$grib_info->latitude_centre.'</latitude>
		<altitude>0</altitude>
		<heading>0</heading>
		<tilt>0</tilt>
		<range>1245000</range>
	</LookAt>
	<ScreenOverlay>
		<name>Winds Legend</name>
		<Icon>
			<href>'.$url.'windbar.png</href>
		</Icon>
		<overlayXY x="0" y="1" xunits="fraction" yunits="fraction"/>
		<screenXY x="0.52" y="0.93" xunits="fraction" yunits="fraction"/>
		<rotationXY x="0" y="0" xunits="fraction" yunits="fraction"/>
		<size x="0" y="0" xunits="fraction" yunits="fraction"/>
	</ScreenOverlay>
	<Folder>
		<name>SOL Grib</name>
	<Style id="style">
		<IconStyle>
			<scale>0.9</scale>
			<Icon>
				<href>http://maps.google.com/mapfiles/kml/shapes/open-diamond.png</href>
			</Icon>
		</IconStyle>
		<LabelStyle>
			<scale>0.65</scale>
		</LabelStyle>
		<ListStyle>
		</ListStyle>
	</Style>
	<Style id="style0">
		<IconStyle>
			<scale>1.0</scale>
			<Icon>
				<href>http://maps.google.com/mapfiles/kml/shapes/open-diamond.png</href>
			</Icon>
		</IconStyle>
		<LabelStyle>
			<scale>0.65</scale>
		</LabelStyle>
		<ListStyle>
		</ListStyle>
	</Style>
	<StyleMap id="stylemap_id2">
		<Pair>
			<key>normal</key>
			<styleUrl>#style</styleUrl>
		</Pair>
		<Pair>
			<key>highlight</key>
			<styleUrl>#style0</styleUrl>
		</Pair>
	</StyleMap>
';
return $s;
}


// --------------------
function GenereKML_Barb($dossier_grib, $url_serveur){
// génere le fichier courant à charger dans Google Earth

global $dir_serveur;
global $dossier_kml;
global $fichier_kml_courant;
global $fichier_kml_cache;
global $extension_kml;
global $extension_kmz;
$fichier_kml_cache_grib=$fichier_kml_cache.'grib';

	$s='<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Folder>
    <NetworkLink>
      <refreshVisibility>0</refreshVisibility>
      <flyToView>1</flyToView>
      <Link>
        <href>'.$url_serveur.'/'.$dossier_kml.'/'.$dossier_grib.'/'.$fichier_kml_cache_grib.$extension_kml.'</href>
        <refreshInterval>1800</refreshInterval>
        <viewRefreshMode>onRequest</viewRefreshMode>
      </Link>
    </NetworkLink>
  </Folder>
</kml>
';
	// enregistrer ce ficher
	$fp_data = fopen($fichier_kml_courant.$extension_kml, 'w');
	if ($fp_data ){
		fwrite($fp_data, $s);
		fclose($fp_data);
	}
}

?>