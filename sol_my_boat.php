<?php
// sol_login.php
// JF 2009  - 2017
// Connexion à la curse avec son propre identifiant
// Retouorne un token valide

define ('DEBUG', 0);      // debogage maison !:))
//define ('DEBUG', 1);
define ('DEBUG2', 0);
require_once('include/utils.php'); // utilitaires divers
require_once('lang/GetStringClass.php'); // localisation languages files
require_once('sol_include/sol_config.php');  // global variables
require_once('sol_include/sol_Boat_class.php'); // Booat class for SOL
require_once('sol_include/CoordGeo_class.php'); // coordinate conversion
require_once('include/GeoCalc.class.php');      // distance with geographic coordinates

/*
// sol_config.php global variables
// download further in the script after string initialisation
// Path and urls for data download
$solhost='http://node1.sailonline.org/';    // == http://www.sailonline.org
$webclient = 'webclient/';                  // all web services
$serviceauth = 'authenticate.xml';          // login authentification
$servicerace='race_';                       // boats and positions
$serviceactiveraces='races.xml';          	// get all active races
$serviceraceinfo = 'auth_raceinfo_';      	// get race info
$serviceboat = 'boat.xml';                  // a boat position and cog sog
$servicetracks='traces_';                   // all boats tracks (gziped)

$racenumber='';
$racename='';
$token='';

*/


$version="0.1-20170328";
$lang='fr'; // par defaut
$module='sol2kml'; // pour charger le bon fichier de langue !
$tlanglist=array(); // Langues disponibles

// Variable pour le téléchargement des fichiers de données
$pathrace='race_info';
$prefixrace='race_';
$extension='.xml';
$nomfichiercourse='';

$tokensol='';
$login='';
$passwd='';
$action='';

if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
	$uri = 'https://';
} else {
	$uri = 'http://';
}
//$url_serveur_local = $uri.$_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].get_url_pere($_SERVER['SCRIPT_NAME']);
$url_serveur_local = $uri.$_SERVER['HTTP_HOST'].get_url_pere($_SERVER['SCRIPT_NAME']);
// DEBUG
//echo "<br>URL : $url_serveur_local<br />\n";
$dir_serveur = dirname($_SERVER['SCRIPT_FILENAME']);
// DEBUG
//echo "<br>Répertoire serveur : $dir_serveur<br />\n";
// Nom du script chargé dynamiquement.
$phpscript=substr($_SERVER["PHP_SELF"], strrpos($_SERVER["PHP_SELF"],'/')+1);
$appli=$uri.$_SERVER['HTTP_HOST'].$_SERVER["PHP_SELF"];
//echo $appli;
//exit;

// COOKIES INPUT

if (isset($_COOKIE["sollang"]) && !empty($_COOKIE["sollang"])){
	$lang=$_COOKIE["sollang"];
}

if (isset($_COOKIE["soltoken"]) && !empty($_COOKIE["soltoken"])){
	$token=$_COOKIE["soltoken"];
}

if (isset($_COOKIE["solracenumber"]) && !empty($_COOKIE["solracenumber"])){
	$racenumber=$_COOKIE["solracenumber"];
}

if (isset($_COOKIE["solracename"]) && !empty($_COOKIE["solracename"])){
	$racename=$_COOKIE["solracename"];
}

// GET
if (isset($_GET['lang'])){
	$lang=$_GET['lang'];
}

if (isset($_GET['racenumber'])){
	$racenumber=$_GET['racenumber'];
}

if (isset($_GET['racename'])){
	$racename=$_GET['racename'];
}

if (isset($_GET['token'])){
	$token=$_GET['token'];
}


// POST
if (isset($_POST['lang'])){
	$lang=$_POST['lang'];
}

// Don't change _POST order
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


if (isset($_POST['tokensol'])){     // generic token 'sol', 'sol'
	$token=$_POST['tokensol'];
}
if (isset($_POST['token'])){        // user token
	$token=$_POST['token'];
}

if (isset($_POST['login'])){        // user login
	$login=$_POST['login'];
}
if (isset($_POST['passwd'])){       // user pass
	$passwd=$_POST['passwd'];
}

if (isset($_POST['action'])){
	$action=$_POST['action'];
}


// COOKIES  OUTPUT
if (isset($lang) && !empty($lang) ){
	setcookie("sollang", $lang);
}
if (isset($racename) && !empty($racename) ){
	setcookie("solracename", $racename);
}
if (isset($racenumber) && ($racenumber!="") ){
	setcookie("solracenumber", $racenumber);
}
if (isset($token) && ($token!="") ){
	setcookie("soltoken", $token);
}

// Localisation linguistique
$al= new GetString();
$tlanglist=$al->getAllLang('./lang',$module);
if ($aFile = $al->setLang('./lang', $lang, $module)){
    require_once($aFile); // pour la localisation linguistique
}

require_once("./sol_include/sol_connect.php"); // utilitaires de connexion au serveur SOL

// recuperer un token generique avec le compte "sol" "sol"
if (empty($token) && empty($tokensol) && !empty($racenumber)){
	$tokensol=get_sol_token($racenumber);
}


entete();

if ($action==$al->get_string('validate')){
	if (!empty($login) && !empty($passwd) && !empty($racenumber)){
		$user=get_login_token($racenumber, $login, $passwd);
		if ($user){
			$token = $user->token;
		}
	}
}

menu();


if (!empty($token)){
	//  http://node1.sailonline.org/webclient/boat.xml?token=dd1d3af4ab5710c1eceadf16f4909ffb
    if ($boatdata=getBoat($token)){
		echo '<div id="menudroite"><h4>'.$al->get_string('process').'</h4>
';
		$timestamp=time();
		echo $date=date("Y/m/d H:i:s T",$timestamp) . "<br />\n";
		date_default_timezone_set('UTC');
		echo $date=date("Y/m/d H:i:s T",$timestamp) . "<br>\n";
		if (DEBUG){
				echo '<br /><pre>'."\n";
				print_r(htmlentities($boatdata));
		    	echo '</pre>'."\n";
		}

        if ($boat_xml = new SimpleXMLElement($boatdata)){
			if (DEBUG){
				echo '<br /><pre>'."\n";
				print_r($boat_xml);
		    	echo '</pre>'."\n";
			}

			$myboat=new SolBoat();
    	    $myboat->setBoat($boat_xml->boat->id, $boat_xml->boat->name, $boat_xml->boat->start_time, $boat_xml->boat->finish_time, $boat_xml->boat->twa, $boat_xml->boat->twd, $boat_xml->boat->tws, $boat_xml->boat->sog, $boat_xml->boat->efficiency,
					$boat_xml->boat->dtg, $boat_xml->boat->dbl, $boat_xml->boat->lon, $boat_xml->boat->lat, $boat_xml->boat->cog, $boat_xml->boat->ranking, $boat_xml->boat->current_leg, $boat_xml->boat->last_cmd_type);
			$myboat->displayBoat();
		}
        echo '</div>
';

	}
}

enqueue();




//---------
function entete(){
	global $appli;
	global $nomfichier;
	global $racenumber;
	global $racename;
	global $tokensol; // token générique
	global $token;
	global $dir_serveur;
	global $url_serveur;
	global $al;
    global $lang;
	global $tlanglist;
	global $login;
    global $passwd;

	echo '<!DOCTYPE html>
<html  dir="ltr" lang="fr" xml:lang="fr">
<head>
	<title>Sailonline :: My Boat</title>
	<meta name="ROBOTS" content="none,noarchive">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Author" content="JF">
	<meta name="description" content="SailOnLine races"/>
    <link rel="author" title="Auteur" href="mailto:jean.fruitet@free.fr">
	<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
';
echo '<div id="bandeau">
';
if (!empty($login)){
    echo '<h1 align="center">'.$al->get_string('titlemyboat', $login).'</h1>'."\n";
}
else{
    echo '<h1 align="center">'.$al->get_string('welcome',$al->get_string('guest')).'. - '.$al->get_string('titlelogin').'</h1>
';
}

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
 	select_a_race();   // modifie $t_race par effet de bord

	echo '
</div>
';

}


//---------
function menu(){
	global $appli;
	global $nomfichier;
	global $racenumber;
	global $racename;
	global $tokensol; // token générique
	global $token;
	global $dir_serveur;
	global $url_serveur;
	global $al;
    global $lang;
	global $tlanglist;
	global $login;
    global $passwd;

	echo '
<div id="menucentre">
<form action="'.$appli.'" method="post">
* <b><i><label for="text1">'.$al->get_string('racenumber').'</label></i></b><br /><input type="text" class="textInput" id="text1"  name="racenumber" size="4" value="'.$racenumber.'" />
<br />* <b><i><label for="text3">'.$al->get_string('login').'</label></i></b><br /><input type="text" class="textInput" id="text3"  name="login" size="20" value="'.$login.'" />
<br />'.$al->get_string('login_help1').'
<br />* <b><i><label for="text4">'.$al->get_string('passwd').'</label></i></b><br /><input type="password" class="textInput" id="text4"  name="passwd" size="20" value="'.$passwd.'" />
<br /><br />
<input type="reset" />
<input id="submitBtn" type="submit" name="action" value="'.$al->get_string('validate').'" />
<input type="hidden" name="racenumber" id="racenumber" value="'.$racenumber.'" />
<input type="hidden" name="racename" id="racename" value="'.$racename.'" />
<input type="hidden" name="url_serveur" id="url_serveur" value="'.$url_serveur.'"/>
<input type="hidden" name="lang" id="lang" value="'.$lang.'"/>
<input type="hidden" name="tokensol" id="lang" value="'.$tokensol.'"/>
<input type="hidden" name="token" id="lang" value="'.$token.'"/>
</form>
<form action="'.$appli.'" method="post">
<br />'.$al->get_string('login_help2').'
<br /><b><i><label for="text2">Token</label></i></b><br /><input type="text" class="textInput" id="text2"  name="token" size="35" value="'.$token.'" />
<br /><br />
<input type="reset" />
<input id="submitBtn" type="submit" name="action" value="'.$al->get_string('token').'" />
<input type="hidden" name="racenumber" id="racenumber" value="'.$racenumber.'" />
<input type="hidden" name="racename" id="racename" value="'.$racename.'" />
<input type="hidden" name="url_serveur" id="url_serveur" value="'.$url_serveur.'"/>
<input type="hidden" name="lang" id="lang" value="'.$lang.'"/>
<input type="hidden" name="tokensol" id="tokensol" value="'.$tokensol.'"/>
<input type="hidden" name="login" id="login" value="'.$login.'"/>
</form>
';

echo '</div>
';

}

?>