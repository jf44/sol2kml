<?php
// JF 2009  - 2017
// Affiche un Grib de SailOnLine dans G.E. en exportant un fichier KML
// Pas de base de données
// Decompression fichier
// Marques  et polaires
// http://144.76.111.8/webclient/webclient/auth_raceinfo_1018.xml?token=8a293f8c7c53cc894511b442cc49b408
// retourne un ficher xml gzipé
// evolution de sol_get_grib.php

define ('DEBUG', 0);      // debogage maison !:))
//define ('DEBUG', 1);

require_once('lang/GetStringClass.php'); // pour le fonction de manipulation de chaines
require_once('sol_include/sol_config.php'); // utilitaires de connexion au serveur SOL
require_once('include/utils.php'); // utilitaires divers

// Gestion des Grib et des traces
require_once("./include/GribClass.php"); // pour le fonction de manipulation de grib
require_once("./include/Barbule.php"); // creation de barbules de vent

// Gestion des fichiers KML / KMZ

require_once('include/zip.php'); // utilise la bibliotheque pclzip
require_once('include/cache_voiliers.php'); // fonction de cache pour les donnees voilier
require_once('include/kml_grib.php'); // Génération des barbules

$version="0.3-20170331";
$archive = true; // Les ficiers créés sot archivés sous forme kmz
$lang='en'; // by defautt
$module='sol2kml'; // pour charger le bon fichier de langue !
$tlanglist=array(); // Langues disponibles

// Path and urls for data download
$prefixrace='race_';
$extension='.xml';

$filenamemarkpolars='';

// Variable pour le téléchargement des fichiers de données
$racefilename='';

$grib_path='SOLGribXml';  // sous-dossier pour stocker les fichiers Grib (identique à DCChecker)
$grib_filename='';         //
$prefixgrib='weather_';
$grib2load='';
$grib2export='';
$weatherurl='';   // url des gribs de a race  :: /webclient/weatherinfo_196.xml

// Initialisé par le chargement de la polaire de barbs
$maxindextwa=0; // 181 : incice max des angles acceptable [0..180]
$maxindextws=0; // :: 40 indice max des vitesses acceptables [à priori 0..39]
$t_polaires = array();  // Table [twa][tws] retourne sog

$t_wp = array();
$t_grib = array();

$appli='';
$n=0;

$barb=0; // fleche par defaut
$t_barbules = array(); // liste des barbules chargées

$action='';

$scale=2;		// valeur d'echelle des voiliers 3D par defaut

$dossier_kml='kml';
$dossier_kmz='kmz';
$dossier_grib='SolGrib';
$dossier_grib_cache=$dossier_grib;
$extension_dae='.dae'; // fichier COLLADA
$extension_kmz='.kmz'; // lue par Google Earth
$extension_kml='.kml'; // lue par Google Earth
$dossier_textures='textures';
$dossier_modeles='models';
$t_url_serveur=array();
$utiliser_cache=0; // par defaut il y a pas de cache d'une heure sur les donnes des voiliers
$datacache='data'; // dossier de cache des voiliers; doit exister sur le serveur
$ext_data='.dat';	// fichier de donnees sauvegardee
$MAXTAILLECACHE='1024';

if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
	$uri = 'https://';
} else {
	$uri = 'http://';
}
$url_serveur = $uri.$_SERVER['HTTP_HOST'].get_url_pere($_SERVER['SCRIPT_NAME']);
$url_serveur_local = $uri.$_SERVER['HTTP_HOST'].get_url_pere($_SERVER['SCRIPT_NAME']);
$dir_serveur = dirname($_SERVER['SCRIPT_FILENAME']);
// Nom du script chargé dynamiquement.
$phpscript=substr($_SERVER["PHP_SELF"], strrpos($_SERVER["PHP_SELF"],'/')+1);
$appli=$uri.$_SERVER['HTTP_HOST'].$_SERVER["PHP_SELF"];

// COOKIES INPUT
if (isset($_COOKIE["solracenumber"]) && !empty($_COOKIE["solracenumber"])){
	$racenumber=$_COOKIE["solracenumber"];
}

// GET
if (isset($_GET['lang'])){
	$lang=$_GET['lang'];
}
if (isset($_GET['filename'])){
	$filename=$_GET['filename'];
	$action="go";
}
if (isset($_GET['racenumber'])){
	$racenumber=$_GET['racenumber'];
}
if (isset($_GET['token'])){
	$token=$_GET['token'];
}

// POST
if (isset($_POST['lang'])){
	$lang=$_POST['lang'];
}

// Attention : keep this order of instruction
if (isset($_POST['racenamenumber'])){
	$racenamenumber=$_POST['racenamenumber'];
	if (!empty($racenamenumber)){
		list($racenumber, $racename) = explode('#§#',$racenamenumber);
	}
    else {
	    if (isset($_POST['racenumber'])){
			$racenumber=$_POST['racenumber'];
		}
    	if (isset($_POST['racename'])){
			$racename=$_POST['racename'];
		}
	}
}
else {
    if (isset($_POST['racenumber'])){
		$racenumber=$_POST['racenumber'];
	}
    if (isset($_POST['racename'])){
		$racename=$_POST['racename'];
	}
}

if (isset($_POST['token'])){
	$token=$_POST['token'];
}
if (isset($_POST['action'])){
	$action=$_POST['action'];
}
if (isset($_POST['newserveur']) && ($_POST['newserveur']!='')){
	$url_serveur=$_POST['newserveur'];
	$url_serveur_marques_parcours=$url_serveur;
}
if (isset($_POST['url_serveur']) && ($_POST['url_serveur']!='')){
	$url_serveur=$_POST['url_serveur'];
	$url_serveur_marques_parcours=$url_serveur;
}
// cache temporel sur les donnes des voiliers
if (isset($_POST['utiliser_cache']) && ($_POST['utiliser_cache']!='')){
	$utiliser_cache=$_POST['utiliser_cache'];
}

// echelle des objets dans G.E.
if (isset($_POST['scale']) && ($_POST['scale']!='')){
	$scale=$_POST['scale'];
}

// barbule ou fleche des vents
if (isset($_POST['barb']) && ($_POST['barb']!='')){
	$barb=1;
}
else{
    $barb=0;
}


// COOKIES  OUTPUT
if (isset($racename) && !empty($racename) ){
	setcookie("solracename", $racename);
}
if (isset($racenumber) && ($racenumber!="") ){
	setcookie("solracenumber", $racenumber);
}
if (isset($lang) && ($lang!="") ){
	setcookie("sollang", $lang);
}

// Localisation
$al = new GetString();
$tlanglist=$al->getAllLang('./lang',$module);

if ($aFile = $al->setLang('./lang', $lang, $module)){
    require_once($aFile); // pour la localisation linguistique
}

require_once("./sol_include/sol_connect.php"); // utilitaires de connexion au serveur SOL

// recuperer un token generique avec le compte "sol" "sol"
if (empty($token) && !empty($racenumber)){
	$token=get_sol_token($racenumber);
}

// NOM DES FICHIERS EN SORTIE

$fichier_kml_courant='SolWind'.$racenumber; // celui qui est lu par Google Earth; il serait utile de pouvoir modifier ce prefixe depuis le programme
$fichier_kml_cache=$fichier_kml_courant.'_cache'; // celui qui est regénére à chaque appel du programme et archivé


// NOM FICHIRES ENTREE
// race server to get marks and polars
$filenamemarkpolars=$serviceraceinfo.$racenumber.$extension.'?token='.$token;

// ########################### DEBUT DU PROGRAMME ###################
entete();
menu();
echo '<div id="consolebasse">
<h4>'.$al->get_string('process').'</h4>
';
$timestamp=time();
echo $date=date("Y/m/d H:i:s T",$timestamp) . "<br />\n";
date_default_timezone_set('UTC');
echo $date=date("Y/m/d H:i:s T",$timestamp) . "<br>\n";


if (($action=='go') || ($action==$al->get_string('validate'))){

	if ($action=='go'){
		$grib=null;
		if (!empty($filename) && file_exists($filename)){
     		if (DEBUG){
			 	echo "Fichier chargé : $filename<br />\n";
			}
			if ($fp = fopen($filename, "r")){
        		$grib  = fread($fp, filesize($filename));
	            fclose($fp);
			}
		}
		else{
    		echo "<br /><br />ERREUR : Fichier absent du disque.\n";
		}
	}

	else if ($action==$al->get_string('validate')){
		// Fichier de marques et polaires
		echo $solhost.$webclient.$filenamemarkpolars."\n";
    	if ($marques=my_get_content($solhost.$webclient.$filenamemarkpolars)){
			// On va utilier SimpleXML
	        $marques_xml = new SimpleXMLElement($marques);
			if ($marques_xml){
   				if (DEBUG){
					echo '<br /><pre>'."\n";
					print_r($marques_xml);
	    	    	echo '</pre>'."\n";
				}
        	    $url=$marques_xml->url; //  : /webclient/race_1018.xml
				$weatherurl=$marques_xml->weatherurl; // : /webclient/weatherinfo_196.xml
				$traceUrl=$marques_xml->traceUrl;  // : /webclient/traces_1018.xml
   				if (DEBUG){
					echo '<br />Weatherurl: '.$weatherurl.'<br />'."\n";
            	    echo '<br />URL Météo: '.$solhost.$weatherurl.'?token='.$token;
                	echo '<br />'."\n";
				}

				foreach ($marques_xml->course->waypoint as $wp_xml){
        	        $wp = new stdClass();
            	    $wp->num=$wp_xml->order;
                	$wp->name=$wp_xml->name;
	            	$wp->longitude = $wp_xml->lon; // 173.522473
    	            $wp->latitude = $wp_xml->lat;  // -34.975873
        	        $wp->any_side = $wp_xml->any_side; // False
					$t_wp[]=$wp;            ;
				}
   				if (DEBUG){
					echo '<br /><pre>'."\n";
					print_r($t_wp);
	    	    	echo '</pre>'."\n";
				}
				afficheMarques($t_wp);

            	echo "<br /><b>Bateau</b>: ".$marques_xml->boat->type."<br />\n";
	  			flush();

				// Polaires
				$polaires = new stdClass();
	    	    $polaires->name = $marques_xml->boat->vpp->name;
	    	    $polaires->tws = $marques_xml->boat->vpp->tws_splined;
    	    	$polaires->twa = $marques_xml->boat->vpp->twa_splined;
				$polaires->bs = $marques_xml->boat->vpp->bs_splined;

   				if (DEBUG){
					echo '<br /><pre>'."\n";
					print_r($polaires);
    		    	echo '</pre>'."\n";
				}
	            $t_polaires = getPolaires($polaires);
    	        echo "<br />Polaires ".$polaires->name." chargées<br />\n";
       		    if (DEBUG){
					affichePolaires($polaires->name, $t_polaires);
				}

	            flush();
			}
		}

		// Grib
		if (!empty($weatherurl) && !empty($token)){
			if ($meteoinfo=my_get_content($solhost.$weatherurl.'?token='.$token)){
				if (!empty($meteoinfo)){
        	        $meteoinfo_xml = new SimpleXMLElement($meteoinfo);
					// DEBUG
                	if (DEBUG){
						echo '<br /><pre>'."\n";
						print_r($meteoinfo_xml);
    		    		echo '</pre>'."\n";
					}
					$meteo_rec = new stdClass();
	                $meteo_rec->id = $meteoinfo_xml->id;
    	            $meteo_rec->last_update = $meteoinfo_xml->last_update;
        	        $meteo_rec->url = $meteoinfo_xml->url;

					// Recuperer le fichier grib
                	$pos=strrpos($meteoinfo_xml->url,'/');
					$len=strlen($meteoinfo_xml->url);
    	            if ($grb_filename = substr($meteoinfo_xml->url,$pos,$len)){
                        $meteo_rec->filename = $grb_filename;
                        $grib2load=$dir_serveur."/".$grib_path."/".$grb_filename;

                    	$pos2=strrpos($grb_filename,'.');
						$grb_filename2export = substr($grb_filename,0,$pos2);
                        $grib2export=$grib_path."/".$grb_filename2export;
	            	    // DEBUG
    	            	if (DEBUG){
							echo '<br />METEO GRIB<pre>'."\n";
							print_r($meteo_rec);
    		    			echo '</pre>'."\n";
						}

                        // DEBUG
		                if (DEBUG){
							echo '<br />METEO GRIB<pre>'."\n";
							echo ($grib2load);
    	    				echo '</pre>'."\n";
						}

						// verifier si en cache
	    	            if (!empty($grib2load) && file_exists($grib2load)){
    						// DEBUG
        	    	    	if (DEBUG){
								echo '<br /><span class="small">Fichier chargé : <i>'.$grib2load.'</i></span><br />'."\n";
							}
		    				if ($gf = fopen($grib2load, "r")){
    	                	    $grib = fread($gf, filesize($grib2load));
								fclose($gf);
							}
						}
						else{
							// sinon
        	    	    	if ($grib = my_get_content($meteo_rec->url)){
								// enregistrer dans le dossier ./SOLGribXml
		    					if ($gf = fopen($grib2load, "w")){
                    	    		if (fwrite($gf, $grib) === FALSE){
										echo "<br />Erreur d'écriture du fichier Grib...\n";
									}
									fclose($gf);
								}
							}
						}
					}
				}
			}
		}
	}
}


// Decoder le fichier grib
if (!empty($grib)){
	if ($grib_xml = new SimpleXMLElement($grib)){
        // DEBUG
        if (DEBUG){
			echo '<br />METEO GRIB<pre>'."\n";
			print_r($grib_xml);
    	    echo '</pre>'."\n";
		}

		// cartouche entete
		$g_at = new stdClass();
        $g_at->id = $grib_xml['id'];
        $g_at->lon_min = $grib_xml['lon_min'];
        $g_at->lon_max = $grib_xml['lon_max'];
        $g_at->lat_min = $grib_xml['lat_min'];
        $g_at->lat_max = $grib_xml['lat_max'];
        $g_at->lon_n = $grib_xml['lon_n_points'];
		$g_at->lon_inc = $grib_xml['lon_increment'];
        $g_at->lat_n = $grib_xml['lat_n_points'];
		$g_at->lat_inc = $grib_xml['lat_increment'];
        // DEBUG
        if (DEBUG){
			echo '<br />METEO GRIB<pre>'."\n";
			print_r($g_at);
    		echo '</pre>'."\n";
		}

		foreach ($grib_xml->frames->frame as $frame_xml){
        	$g_frame = new stdClass();
            $g_frame->date = $frame_xml['target_time'];   // 2017/03/03 09:00:00 UTC
       		if (($timestamp_grib = strtotime($g_frame->date)) === false){
    			echo "The string ($g_frame->date) is bogus";
			}
			else {
           		// DEBUG
                if (DEBUG){
                	date_default_timezone_set('UTC');
					echo "$g_frame->date == " . date('l dS \o\f F Y h:i:s A T', $timestamp_grib)."<br />\n";
				}
                $g_frame->timestamp = $timestamp_grib;
			}
			// Lire http://solfans.org/blog/uncategorized/confessions-from-the-canaries/
			$g_frame->u = $frame_xml->U;    // composante  Nord / Sud  de TWS
			$g_frame->v = $frame_xml->V;    // composante  Est / Ouest  de TWS
			$t_grib[] = $g_frame;
		}
		//
        // DEBUG
        if (DEBUG){
			echo '<br />METEO GRIB : '.count($t_grib).' enregistrements.<br /><pre>'."\n";
			print_r($t_grib);
	    	echo '</pre>'."\n";
		}

		$timestamp_min=$timestamp-3600; // Une heure dans le passé
        $timestamp_max=$timestamp+6*3600; // 6s heures dans le futur pour avoir deux fenêtres consécutives

        $uneGrib = new Grib();
		$uneGrib->setGrib($g_at, $t_grib, $timestamp_min, $timestamp_max); // ne conserver que la fen^tre temporelle courante
		// verification
        if (DEBUG){
			echo '<br />METEO GRIB<pre>'."\n";
			print_r($uneGrib);
    	    echo '</pre>'."\n";
		}
        if (true){
			// echo '<br />METEO GRIB COMPONENTS<br />'."\n";
			// $uneGrib->affGrib(true);
            //$uneGrib->gribToTable(true);
            //echo '<br />METEO GRIB TWD / TWS<br />'."\n";
            //$uneGrib->gribToTable(false);
    	    //exit;
            if (!empty($grib2export)){
				// Calculer la date UTC
                date_default_timezone_set('UTC');
				$grib2export.='_'.date("Y-m-d-H T",time()).'.csv';
				if ($uneGrib->exportGrib($dir_serveur."/".$grib2export)){
                    echo '<br />Données météo TWD / TWS exportées dans le fichier <a target="_blank" href="./'.$grib2export.'"><i>'.$grib2export.'</i></a> <br />'."\n";
				}
			}
		}
		else{
			echo "<br />Grib <i>".$grib2export."</i> enregistré.<br />\n";
		}

		// Enregistrement du Grib pour G.E.
        $grib_info = $uneGrib->getGribInfo($timestamp);
        $grib_info->race = $racenumber;
        $grib_info->namerace = $racename;

        $t_barbule=$uneGrib->exportGrib2Barbules($grib_info->framecourante, $barb);
	}
}


//  Génération des données pour G.E.
if (!empty($t_barbule)){
	echo '<h4>'.$al->get_string('newmap').'</h4>'."\n";
	echo $al->get_string('wait')."\n";
	flush();

 	// Structure d'accueil pour les données
	creer_dossier_kml($archive);
	if (isset($t_barbule) && is_array($t_barbule) && (count($t_barbule)>0) ){
  		echo '<p>'.$al->get_string('export1');
		flush();
		// donnees à placer sur un serveur distant : adressage absolue
      	$s=GenereEnteteKML_Grib($dossier_grib, $url_serveur, $grib_info);
  		$i=0;
		while ($i<count($t_barbule)){
			$s.=GenereBarbuleKML($t_barbule[$i], $scale);
			$i++;
		}
		$s.=GenereEnQueueKML_Grib();
		EnregistreKML_Grib($dossier_grib, $s, $archive, $al);
        GenereKML_Grib($dossier_grib, $url_serveur); // Fichier kml a appeler depuis GoogleEarth

  		echo '<br />'.$al->get_string('export2')."\n";
		unset($t_barbule);
	}
}

// afficheArchivesKML();
if ($datatodisplay=verifieArchivesKML()){
	echo '<p><span class="small">'.$al->get_string('info1').'<br /><i>'.$al->get_string('info2').'</i></span></p>'."\n";

	echo '
<h4>'.$al->get_string('mapready').'</h4>
';

    displayArchivesKML($datatodisplay);
	if ($datatodisplay->nkml){
		echo '
<button id="rollButtonkml" type="button" onclick="rollKml()">++KML</button>
';
		echo '<div id="divkml">
<script type="text/javascript">
displayPagekml();
</script>
</div>
';
	}

	if ($datatodisplay->nkmz){
		echo '
<button id="rollButtonkmz" type="button" onclick="rollKmz()">++KMZ</button>
';
		echo '<div id="divkmz">
<script type="text/javascript">
displayPagekmz();
</script>
</div>
';
	}
}


echo '</div>
';
enqueue();


// ------------------
function verifieArchivesKML(){

global $dossier_kml;
global $extension_kml;
global $dossier_kmz;
global $extension_kmz;
global $al;

// DEBUG
// echo "<br>Fichier KML courant : $fichier_kml_courant\n";
	$tikml=array();
	$tikmz=array();
	$traceskml=array();
	$traceskmz=array();
	$sep = '/';
    $nobj = 0;
    $nkml = 0;
	$nkmz = 0;
	$ndir = 0;

	$path = './'.$dossier_kml;
	$h1=opendir($path);

    while ($f = readdir($h1) )
    {
		if (($f != ".") && ($f != "..")) {
			// Les fichiers commençant par '_' ne sont pas affichés
			// Ni le fichier par defaut ni le fichier de cache ne sont affichés
			// Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
			// les fichier n'ayant pas la bonne extension ne sont pas affichés
	        if (!is_dir($path.$sep.$f)){
				// KML
    	       	$g= eregi_replace($extension_kml,"",$f) ;
				// DEBUG
				// echo "<br>g:$g  g+:$g$extension_kml  f:$f\n ";
        	  	if (
/*
					(strtoupper($g) != strtoupper($fichier_kml_courant)) // le fichier par defaut n'est pas affiché
					&&
					(strtoupper($g) != strtoupper($fichier_kml_cache)) // le fichier de cache n'est pas affiché
					&&
					(substr($g,0,1) == substr($fichier_kml_courant,0,1)) // Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
					&&
*/
					(substr($g,0,1) != "_") // Les fichiers commençant par '_' ne sont pas affichés
					&&
					(strtoupper($g.$extension_kml) == strtoupper($f)) // les fichier n'ayant pas la bonne extension ne sont pas affichés
				) {
            	   	$nobj ++;
               		$nkml ++;
	               	$tikml[$f] = $f ;
				}
			} // fin traitement d'un fichier
		} // fin du test sur entrees speciales . et ..
	}  // fin du while sur les entrees du repertoire traite

	closedir($h1);

    $path = './'.$dossier_kmz;
 	$h2=opendir($path);

    while ($f = readdir($h2) )
    {
		if (($f != ".") && ($f != "..")) {
			// Les fichiers commençant par '_' ne sont pas affichés
			// Ni le fichier par defaut ni le fichier de cache ne sont affichés
			// Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
			// les fichier n'ayant pas la bonne extension ne sont pas affichés
	        if (!is_dir($path.$sep.$f)){
				// KML
    	       	$g= eregi_replace($extension_kmz,"",$f) ;
				// DEBUG
				// echo "<br>g:$g  g+:$g$extension_kml  f:$f\n ";
        	  	if (
/*
					(strtoupper($g) != strtoupper($fichier_kml_courant)) // le fichier par defaut n'est pas affiché
					&&
					(strtoupper($g) != strtoupper($fichier_kml_cache)) // le fichier de cache n'est pas affiché
					&&
					(substr($g,0,1) == substr($fichier_kml_courant,0,1)) // Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
					&&
*/
					(substr($g,0,1) != "_") // Les fichiers commençant par '_' ne sont pas affichés
					&&
					(strtoupper($g.$extension_kmz) == strtoupper($f)) // les fichier n'ayant pas la bonne extension ne sont pas affichés
				) {
            	   	$nobj ++;
               		$nkmz ++;
	               	$tikmz[$f] = $f ;
				}
			} // fin traitement d'un fichier
		} // fin du test sur entrees speciales . et ..
	}  // fin du while sur les entrees du repertoire traite

	closedir($h2);

	if ($nobj>0){
		$data = new stdClass();
        $data->nkml = $nkml;
        $data->tikml = $tikml;
        $data->nkmz = $nkmz;
        $data->tikmz = $tikmz;
        return $data;
	}

	return NULL;

}

//-------------------
function displayArchivesKML($data){
global $dossier_kml;
global $extension_kml;
global $dossier_kmz;
global $extension_kmz;
global $al;

$sep = '/';
$path = '.';

	if (!empty($data) && isset($data->nkml) && isset($data->nkmz)){

 		if (($data->nkml > 0) || ($data->nkmz > 0)){
			// Javascript
        	echo '<script type="text/javascript">'."\n";
			echo '
// Display KML files
var indexkml = 0;
var tjkml = new Array();
';

			if ( $data->nkml > 0){
            	//echo '<b>'.$al->get_string('kmlfile').'</b><br />'."\n";
		        rsort($data->tikml);
				$j=0;
				while (list($key) = each($data->tikml)) {
					echo 'tjkml['.$j.'] = "<a  class=\"small\" href=\"'.$path.$sep.$dossier_kml.$sep.$data->tikml[$key].'\">'.$data->tikml[$key].'</a>  &nbsp; &nbsp; &nbsp; ";'."\n";
					$j++;
    			}
			}

			// fonctions
			echo '
function displayPagekml() {
	var skml = \'\';
	if ( tjkml.length< 20){
   		for (i=0;i<tjkml.length;i++){
			skml+= tjkml[i] + " ";
		}
	}
	else{
		var $aff =  Math.min (indexkml+20, tjkml.length);
		var aff2 =  Math.min (20 - ($aff - indexkml), tjkml.length);
        for (i=indexkml;i<$aff;i++){
			skml+= tjkml[i] + " ";
		}
        //skml+= \'<br>\'+aff2+\'<br>\';
		if (aff2>0){
        	for (i=0;i<aff2;i++){
				skml+= tjkml[i] + " ";
			}
		}
	}
	document.getElementById(\'divkml\').innerHTML=skml;
}
';

			echo '
function rollKml() {
    indexkml=++indexkml  % tjkml.length;  // pre-increment is better
    displayPagekml();
}
';

            echo '
// Display KMZ files
var indexkmz = 0;
var trkmz = new Array();
var tjkmz = new Array();

';
			if ( $data->nkmz > 0){
				//echo '<br /><br /><b>'.$al->get_string('kmzfile').'</b><br />'."\n";
		        rsort($data->tikmz);
				// Lister les courses
				/*
				$j=0;
				$k=0;
				while (list($key) = each($tikmz)) {
					if ($race=substr($tikmz[$key],0,strpos($tikmz[$key],'_')) !== false) {
						if (!isset($traceskmz[$race])){
                            $traceskmz[$race]=$race;
							echo '$t_rkmz['.$k.'] = "'.$race.'";';
							$k++;
						}
					}
				}
				*/
				$j=0;
				while (list($key) = each($data->tikmz)) {
		        	//echo '<a  class="small" href="'.$path.$sep.$key.'">'.$tikmz[$key].'</a>  &nbsp; &nbsp; &nbsp; '."\n";
					echo 'tjkmz['.$j.'] = "<a  class=\"small\" href=\"'.$path.$sep.$dossier_kmz.$sep.$data->tikmz[$key].'\">'.$data->tikmz[$key].'</a>  &nbsp; &nbsp; &nbsp; ";'."\n";
					$j++;
    			}
			}
			// fonctions
  			echo '
function displayRacekmz() {
	var rkmz = \'\';
	for (i=0;i<trkmz.length;i++){
		rkmz+= trkmz[i] + " ";
	}
    document.getElementById(\'divrkmz\').innerHTML=rkmz;
}

function displayPagekmz() {
	var skmz = \'\';
	if ( tjkmz.length< 20){
   		for (i=0;i<tjkmz.length;i++){
			skmz+= tjkmz[i] + " ";
		}
	}
	else{
		var $aff =  Math.min (indexkmz+20, tjkmz.length);
		var aff2 =  Math.min (20 - ($aff - indexkmz), tjkmz.length);
        for (i=indexkmz;i<$aff;i++){
			skmz+= tjkmz[i] + " ";
		}
        //skmz+= \'<br>\'+aff2+\'<br>\';
		if (aff2>0){
        	for (i=0;i<aff2;i++){
				skmz+= tjkmz[i] + " ";
			}
		}
	}
	document.getElementById(\'divkmz\').innerHTML=skmz;
}
';

			echo '
var rollKmz = function () {
    indexkmz=(indexkmz+3)  % tjkmz.length;  // pre-increment is better
    displayPagekmz();
}
';
/*
            echo '
//when the user presses the button it will display  the array
    document.getElementById(\'rollButtonkmz\').addEventListener(\'click\', rollKmz());
';
*/
			echo '</script>'."\n";
		}
	}
	else{
        echo '<p>'.$al->get_string('nofilekml').'</p>'."\n";
	}

}



// ------------------
function selectFichier($path, $prefix, $extension, $racenumber, $maxdisplay=10){
global $appli;
global $max_ligne;
global $ligne;

	if (!empty($racenumber)){
		$pref = $prefix.$racenumber;
	}
	else{
    	$pref = $prefix;
	}

	// DEBUG
	if (false){
		echo $pref;
	}
	$tf=array();
	$sep = '/';

	$h1=opendir($path);
    $n = 0;

    while ($f = readdir($h1) )
    {
		if (($f != ".") && ($f != "..")) {
			// Les fichiers commençant par '_' ne sont pas affichés
			// Ni le fichier par defaut ni le fichier de cache ne sont affichés
			// Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
			// les fichier n'ayant pas la bonne extension ne sont pas affichés
	        if (!is_dir($path.$sep.$f)){
				// TEXT
    	       	$g= eregi_replace($extension,"",$f) ;
				// DEBUG
				// echo "<br>g:$g  g+:$g$extension_kml  f:$f\n ";
        	  	if (
					(substr($g,0,1) != "_") // Les fichiers commençant par '_' ne sont pas affichés
					&&
					(substr($g,0,strlen($pref)) == $pref)
					&&
					(strtoupper($g.$extension) == strtoupper($f)) // les fichier n'ayant pas la bonne extension ne sont pas affichés
				) {
               		$n ++;
	               	$tf[$f] = $f ;
				}

			} // fin traitement d'un fichier
		} // fin du test sur entrees speciales . et ..
	}  // fin du while sur les entrees du repertoire traite

	closedir($h1);

    if ($n > 0) {
		// Javascript
	    echo '<script type="text/javascript">'."\n";
		echo '
// Display  files
var index = 0;
var tj = new Array();
';

	    asort($tf);
		$j=0;
		while (list($key) = each($tf)) {
			//echo '<a  class="small" href="'.$path.$sep.$key.'">'.$tf[$key].'</a>  &nbsp; &nbsp; &nbsp; '."\n";
			if ($j<10){
				// <a  class="small" href="'.$appli.'?filename='.urlencode($path.$sep.$key).'">'.$tf[$key].'</a></li>
				echo 'tj['.$j.'] = "&nbsp; '.$j.': <a  class=\"small\" href=\"'.$appli.'?filename='.urlencode($path.$sep.$tf[$key]).'\">'.$tf[$key].'</a>  &nbsp; <br /> ";'."\n";
			}
			else{
				echo 'tj['.$j.'] = "'.$j.': <a  class=\"small\" href=\"'.$appli.'?filename='.urlencode($path.$sep.$tf[$key]).'\">'.$tf[$key].'</a>  &nbsp; <br /> ";'."\n";
			}
			$j++;
    	}


		// fonctions
		echo '
function displayPage() {
	var s = \'\';
	if ( tj.length < '.$maxdisplay.'){
   		for (i=0;i<tj.length;i++){
			s+= tj[i] + " ";
		}
	}
	else{
		var $aff =  Math.min (index+'.$maxdisplay.', tj.length);
		var aff2 =  Math.min ('.$maxdisplay.' - ($aff - index), tj.length);
        for (i=index;i<$aff;i++){
			s+= tj[i] + " ";
		}
        //s+= \'<br>\'+aff2+\'<br>\';
		if (aff2>0){
        	for (i=0;i<aff2;i++){
				s+= tj[i] + " ";
			}
		}
	}
	document.getElementById(\'divgrib\').innerHTML=s;
}
';

		echo '
function roll() {
    index=++index % tj.length;  // pre-increment is better
    displayPage();
}
';
		echo '
function roll10() {
    index=(index+'.$maxdisplay.') % tj.length;  // pre-increment is better
    displayPage();
}
';
		echo '
function rollfirst() {
    index=0;  // pre-increment is better
    displayPage();
}
';
		echo '</script>'."\n";

	}
	else{
    	echo '<br />Aucun fichier ne correspond.'."\n";
        echo '<br /><br/>'."\n";
	}
}


// ------------------
function selectFichier_old($path, $prefix, $extension, $racenumber, $msg){
global $appli;
global $nobj;
global $max_ligne;
global $ligne;
$s='';

	if (!empty($racenumber)){
		$pref = $prefix.$racenumber;
	}
	else{
    	$pref = $prefix;
	}
	if (false){
		echo $pref;
	}
	$tf=array();
	$sep = '/';


	$h1=opendir($path);
    $nobj = 0;
    $n = 0;

    while ($f = readdir($h1) )
    {
		if (($f != ".") && ($f != "..")) {
			// Les fichiers commençant par '_' ne sont pas affichés
			// Ni le fichier par defaut ni le fichier de cache ne sont affichés
			// Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
			// les fichier n'ayant pas la bonne extension ne sont pas affichés
	        if (!is_dir($path.$sep.$f)){
				// TEXT
    	       	$g= eregi_replace($extension,"",$f) ;
				// DEBUG
				// echo "<br>g:$g  g+:$g$extension_kml  f:$f\n ";
        	  	if (
/*
					(strtoupper($g) != strtoupper($fichier_kml_courant)) // le fichier par defaut n'est pas affiché
					&&
					(strtoupper($g) != strtoupper($fichier_kml_cache)) // le fichier de cache n'est pas affiché
					&&
					(substr($g,0,1) == substr($fichier_kml_courant,0,1)) // Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
					&&
*/
					(substr($g,0,1) != "_") // Les fichiers commençant par '_' ne sont pas affichés
					&&
					(substr($g,0,strlen($pref)) == $pref)
					&&
					(strtoupper($g.$extension) == strtoupper($f)) // les fichier n'ayant pas la bonne extension ne sont pas affichés
				) {
            	   	$nobj ++;
               		$n ++;
	               	$tf[$f] = $f ;
				}

			} // fin traitement d'un fichier
		} // fin du test sur entrees speciales . et ..
	}  // fin du while sur les entrees du repertoire traite

	closedir($h1);

	if ($n != 0) {
	    asort($tf);
		$s.= '<p>Sélectionnez '.$msg.'</p>'."\n";
		$s.= '<ul>'."\n";
       	while (list($key) = each($tf)) {
	       	$s.= '<li><a  class="small" href="'.$appli.'?filename='.urlencode($path.$sep.$key).'">'.$tf[$key].'</a></li>'."\n";
   		}
   		$s.= '</ul>'."\n";
        $s.= '<br /><br/>'."\n";
	}
	else{
    	$s.= '<br />Aucun fichier ne correspond.'."\n";
        $s.= '<br /><br/>'."\n";
	}

	return $s;
}

//---------
function entete(){
	global $appli;
	global $nomfichier;
	global $racenumber;
	global $racename;
	global $token;
	global $dir_serveur;
	global $okfichier_charge;
    global $barb;
    global $scale;
	global $extension;
	global $prefixmarquesetpolaires;
	global $url_serveur;
	global $grib_path, $prefixgrib;
	global $al;
    global $lang;
	global $tlanglist;

	echo '<!DOCTYPE html>
<html  dir="ltr" lang="fr" xml:lang="fr">
<head>
	<title>Sailonline Grib to Google Earth</title>
	<meta name="ROBOTS" content="none,noarchive">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Author" content="JF">
	<meta name="description" content="races SailOnLine."/>
    <link rel="author" title="Auteur" href="mailto:jean.fruitet@free.fr">
	<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>

<div id="bandeau">
<h1 align="center">'.$al->get_string('titlegrib').'</h1>
';
echo '<p align="center">
';
if (!empty($tlanglist)){
	foreach ($tlanglist as $alang){
		if ($alang==$lang){
			echo ' <b>'.$al->get_string($alang).'</b> &nbsp; - ';
		}
		else{
			echo '<a href="'.$appli.'?lang='.$alang.'&racenumber='.$racenumber.'">'.$al->get_string($alang).'</a> &nbsp; - ';
		}
	}
}

onelinemenu();

echo '</div>
<div id="menugauche">

<h4>'.$al->get_string('serverconnect').'</h4>
';


	$params = array();
	$params['url_serveur'] = $url_serveur;
	$params['scale'] = $scale;
	$params['barb'] = $barb;
 	select_a_race($params );   // modifie $t_race par effet de bord

	echo '
<form action="'.$appli.'" method="post">
* <b><i><label for="text1">'.$al->get_string('racenumber').'</label></i></b><br /><input type="text" class="textInput" id="text1"  name="racenumber" size="4" value="'.$racenumber.'" />
<br />
* <b><i><label for="text2">'.$al->get_string('token').'</label></i></b><br /><input type="text" class="textInput" id="text2"  name="token" size="35" value="'.$token.'" />
<br /><br />
<input type="reset" />
<input id="submitBtn" type="submit" name="action" value="'.$al->get_string('validate').'" />
<i><label for="text4">'.$al->get_string('clicktoload').'</label></i>
<input type="hidden" name="racenumber" value="'.$racenumber.'" />
<input type="hidden" name="racename" value="'.$racename.'" />
<input type="hidden" name="lang" id="lang" value="'.$lang.'"/>
<input type="hidden" name="barb" id="barb" value="'.$barb.'"/>
<input type="hidden" name="url_serveur" id="url_serveur" value="'.$url_serveur.'"/>
<input type="hidden" name="scale" id="scale" value="'.$scale.'"/>
</form>
</div>
';
}

// ------------------------------
function afficheWP($wp){
$s='';
	if (!empty($wp)){
		$s.=' N°: <i>'.$wp->num.'</i> <b>'.$wp->name.'</b> '.$wp->longitude.', '.$wp->latitude.' ['.$wp->any_side."]\n";
 	}
	return $s;
}

// ------------------------------
function afficheBoatModel($bModel){
$s='';
	if (!empty($bModel)){
		$s.=' <b>'.$bModel.'<b>'."\n";
 	}
	return $s;
}

// ------------------------------
function getPolaires($polaires){
$t_twa=array();
$t_tws=array();
$t_bs=array();
$t_pol=array();
	$t_twa = explode(' ',$polaires->twa);
    $t_tws = explode(' ',$polaires->tws);
    $t_bs = explode(';',$polaires->bs);
	foreach($t_bs as $tbs){
		if (!empty($tbs)) {
			$t_pol[] = explode(' ', $tbs );
		}
	}
	return  $t_pol;
}

// ------------------------------
function affichePolaires($nom, $t_pol){
	echo "<br />Polaire du <b>$nom</b><br />\n";
	echo "<pre>\n";
	echo ("TWA\TWS\t");
	for ($tws=0; $tws<40; $tws++){
		printf(" %8d\t",$tws);
	}
	echo "\n";
	for ($twa=0; $twa<181; $twa++){
  		printf("%-8d\t",$twa);
  		for ($tws=0; $tws<40; $tws++){
			printf(" %-2.6F\t", $t_pol[$twa][$tws]);
		}
		echo "\n";
	}
	echo "\n";
    echo "</pre>\n";
}


// ------------------------------
function afficheMarques($t_wp){
global $racenumber;
global $racename;
    echo "<h5>Marques de parcours</h5><p><b>$racename</b> (race N° <i>$racenumber</i>)<br />\n";

	if (!empty($t_wp)){
		echo '<ul>';
        //echo '<br /><pre>'."\n";
 		//print_r( t_wp);
        //echo '</pre>'."\n";
		// DEBUG
		foreach ($t_wp as $awp){
			echo '<li>'.afficheWP($awp).'</li>'."\n";
		}
        echo '</ul>'."\n";
	}
}


//----------------------------------
class UploadException extends Exception
{
    public function __construct($code) {
        $message = $this->codeToMessage($code);
        parent::__construct($message, $code);
    }

    private function codeToMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;

            default:
                $message = "Unknown upload error";
                break;
        }
        return $message;
    }
}



// ---------------------------
function menu(){
	global $appli;
	global $nomfichier;
	global $racenumber;
	global $racename;
	global $token;
	global $dir_serveur;
	global $url_serveur;
	global $url_serveur_local;
	global $okfichier_charge;
    global $barb;
    global $scale;
	global $extension;
	global $prefixmarquesetpolaires;
	global $grib_path;
	global $prefixgrib;
	global $al;
    global $lang;
	global $tlanglist;

	echo '<div id="menucentre">
<h4>'.$al->get_string('fileexportsetup').'</h4>
<h5 align="center"><b>'.$al->get_string('mapserver').'</b></h5>
<form action="'.$appli.'" method="post" name="saisie_serveur" id="saisie_serveur">
<b>'.$al->get_string('serverurl').'</b> '.$al->get_string('dispokml').'
<br /><i>'.$url_serveur .'</i>
<br /><br />
<b>'.$al->get_string('selectnewurl').'</b>
<br />
<select name="url_serveur" id="url_serveur" size="3">
';
		if ($url_serveur_local==$url_serveur){
			echo '<option value="'.$url_serveur_local.'" SELECTED>'.$url_serveur_local.'</option>';
		}
		else{
			echo '<option value="'.$url_serveur_local.'"/>'.$url_serveur_local.'</option>';
		}

		if ('http://voilevirtuelle.free.fr/SailOnLine/g'==$url_serveur){
			echo '<option value="http://voilevirtuelle.free.fr/SailOnLine/g" SELECTED>http://voilevirtuelle.free.fr/SailOnLine/g</option>';
		}
		else{
			echo '<option value="http://voilevirtuelle.free.fr/SailOnLine/g"/>http://voilevirtuelle.free.fr/SailOnLine/g</option>';
		}

	echo '
</select>

<br /><br />
<b>'.$al->get_string('inputnewurl').'</b> :
<br />
<span class="small">'.$al->get_string('serverwarning').'</span>
<br />
<input type="text" name="new_serveur" id="new_serveur" value="" size="40" maxlength="255"/>
<br />
<input type="reset"  />
<input type="submit" name="action" id="action" value="'.$al->get_string('server').'"/>
<input type="hidden" name="scale" id="scale" value="'.$scale.'"/>
<input type="hidden" name="racenumber" id="racenumber" value="'.$racenumber.'"/>
<input type="hidden" name="racename" id="racename" value="'.$racename.'"/>
<input type="hidden" name="barb" id="barb" value="'.$barb.'"/>
</form>
</div>
<div id="menudroite">
<h4>'.$al->get_string('modebarb').'</h4>
<form action="'.$appli.'" method="post" name="saisie_mode" id="saisie_mode"/>
<b>'.$al->get_string('display').'</b>
<br />'.$al->get_string('barb').' <input type="checkbox"  name="barb" id="barb" ';
	if ($barb){
		echo ' CHECKED />';
	}
	else{
		echo '  />';
	}

	echo '<br /> '.$al->get_string('barbsize').' :
<input type="text" name="scale" size="1" maxsize="3" value="'.$scale.'"/>
(<span class="small">[<i>0.1</i>, <i>2.0</i>].</span>)
<br /><br />
<input type="reset" value="Corriger"/>
<input type="submit" value="Valider"/>
<input type="hidden" name="url_serveur" id="url_serveur" value="'.$url_serveur.'"/>
<input type="hidden" name="racenumber" id="racenumber" value="'.$racenumber.'"/>
<input type="hidden" name="racename" id="racename" value="'.$racename.'"/>
</form>
';
	echo '<h4>'.$al->get_string('selectlocalfile').'</h4>
';
	echo selectFichier($grib_path, $prefixgrib, $extension, '', 6);
	echo '
<button id="rollButtongrib" type="button" onclick="roll()">+GRIB</button>
<button id="rollButtongrib1" type="button" onclick="roll10()">++GRIB</button>
<button id="rollButtongrib2" type="button" onclick="rollfirst()">RESET</button>
';
	echo '<div id="divgrib">
<script type="text/javascript">
displayPage();
</script>
</div>
';
 	if ($okfichier_charge){
		echo '<p>'.$al->get_string('datadispo')."</p>\n";
	}
	echo '
</div>
';
}


//------------------------
function creer_dossier_kml($archive=false){
// Crée un dossier unique pour archiver les donnees KML
global $dir_serveur;
global $dossier_kml;
global $dossier_kmz;
global $dossier_grib;
global $dossier_grib_cache;
global $dossier_textures;
global $dossier_modeles;

	$dir_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_grib;
	if (!file_exists($dir_name)){
		mkdir($dir_name);
	}
	$dir_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_grib.'/'.$dossier_modeles;
	if (!file_exists($dir_name)){
		mkdir($dir_name);
	}
	$dir_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_grib.'/'.$dossier_textures;
	if (!file_exists($dir_name)){
		mkdir($dir_name);
	}

	if ($archive){
		$dossier_grib_cache=$dossier_grib.'_'.date("YmdH");
		$dir_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_grib_cache;
		if (!file_exists($dir_name)){
			mkdir($dir_name);
		}
		$dir_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_grib_cache.'/'.$dossier_modeles;
		if (!file_exists($dir_name)){
			mkdir($dir_name);
		}
		$dir_name=$dir_serveur.'/'.$dossier_kml.'/'.$dossier_grib_cache.'/'.$dossier_textures;
		if (!file_exists($dir_name)){
			mkdir($dir_name);
		}
	 }
}




?>