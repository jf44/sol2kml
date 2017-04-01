<?php
// index.php
// JF 2009  - 2017

//define ('DEBUG', 0);      // debogage maison !:))
define ('DEBUG', 1);
define ('DEBUG2', 0);
require_once('include/utils.php'); // utilitaires divers
require_once('lang/GetStringClass.php'); // localisation
require_once('sol_include/sol_config.php'); // utilitaires de connexion au serveur SOL


$version="0.1-20170331";
$lang='en'; // par defaut
$module='sol2kml'; // pour charger le bon fichier de langue !
$tlanglist=array(); // Langues disponibles

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

// COOKIES INPUT

if (isset($_COOKIE["sollang"]) && !empty($_COOKIE["sollang"])){
	$lang=$_COOKIE["sollang"];
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

if (isset($racename) && !empty($racename) ){
	setcookie("solracename", $racename);
}
if (isset($racenumber) && ($racenumber!="") ){
	setcookie("solracenumber", $racenumber);
}

if (isset($lang) && ($lang!="") ){
	setcookie("sollang", $lang);
}


// Localisation linguistique
$al= new GetString();
$tlanglist=$al->getAllLang('./lang',$module);
if ($aFile = $al->setLang('./lang', $lang, $module)){
    require_once($aFile); // pour la localisation linguistique
}

require_once("./sol_include/sol_connect.php"); // utilitaires de connexion au serveur SOL

entete();

echo '<div id="bigdisplay">
<h2>'.$al->get_string('teaser').'</h2>
<p>
<a href="md2html.php?lang='.$lang.'&amp;filename=README.md">Readme Doc</a> - <a href="md2html.php?lang='.$lang.'&amp;filename=DEVELOPER.md">Developer Doc</a> - <a href="images/index.php">Images</a>
</p>
<p><img src="images/sol_BostonNewport_20170330_2.jpg" alt="Boston Newport 2017" title="Boston Newport 2017" heigth="600" width="1200">
</p>
</div>
';


enqueue();


// ----------
function get_readmefile(){
	if (file_exists('README.md')){
		return file_get_contents('README.md');
	}
	else{
 		if (file_exists('redame.txt')){
			return  file_get_contents('readme.txt');
		}
		else{
        	return ('**Not any README.md file here**'."\n");
		}
	}
}

//---------
function entete(){
	global $appli;
	global $racenumber;
	global $racename;
	global $token;
	global $al;
    global $lang;
	global $tlanglist;

	echo '<!DOCTYPE html>
<html  dir="ltr" lang="fr" xml:lang="fr">
<head>
	<title>Sailonline Tools</title>
	<meta name="ROBOTS" content="none,noarchive">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Author" content="JF">
	<meta name="description" content="SailOnLine Tools"/>
    <link rel="author" title="Auteur" href="mailto:jean.fruitet@free.fr">
	<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
';
	echo '<div id="bandeau">
<h1 align="center">'.$al->get_string('titleindex').'</h1>
<p align="center">
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
	echo '</p>
</div>
';
}


?>