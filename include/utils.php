<?php

//---------------
function onelinemenu(){
global $phpscript;
global $lang;
global $al;
global $racenumber;
global $token;
	// DEBUG
	// echo " '$phpscript' ";
	if (!empty($phpscript)){
		switch ($phpscript)  {
			case 'login.php' :
				echo '  <a href="../../soltools/index.php?lang='.$lang.'&racenumber='.$racenumber.'&token='.$token.'">'.$al->get_string('home').'</a> -  <b>Login</b> - <a href="../../soltools/solboats2kml.php?lang='.$lang.'&racenumber='.$racenumber.'&token='.$token.'">SolBoatsToKml</a> - <a href="../../soltools/solgrib2kml.php?lang='.$lang.'&racenumber='.$racenumber.'&token='.$token.'">SolToGrib</a>'."\n";
			break;
			case 'solboats2kml.php' :
				echo '  <a href="../../soltools/index.php?lang='.$lang.'&racenumber='.$racenumber.'&token='.$token.'">'.$al->get_string('home').'</a> - <a href="server/login.php?lang='.$lang.'&racenumber='.$racenumber.'&token='.$token.'">SolMyBoat</a> - <b>SolBoatsToKml</b> - <a href="../../soltools/solgrib2kml.php?lang='.$lang.'&racenumber='.$racenumber.'&token='.$token.'">SolToGrib</a>'."\n";
			break;
            case 'solgrib2kml.php' :
            	echo '  <a href="../../soltools/index.php?lang='.$lang.'&racenumber='.$racenumber.'&token='.$token.'">'.$al->get_string('home').'</a> - <a href="../../soltools/sol_my_boat.php?lang='.$lang.'&racenumber='.$racenumber.'&token='.$token.'">SolMyBoat</a> - <a href="../../soltools/solboats2kml.php?lang='.$lang.'&racenumber='.$racenumber.'&token='.$token.'">SolBoatsToKml</a> - <b>SolToGrib</b>'."\n";
            break;
			default :
            	echo '  <b>'.$al->get_string('home').'</b> - <a href="server/login.php?lang='.$lang.'&racenumber='.$racenumber.'&token='.$token.'">SolMyBoat</a> - <a href="../../soltools/solboats2kml.php?lang='.$lang.'&racenumber='.$racenumber.'&token='.$token.'">SolBoatsToKml</a> - <a href="../soltools/solgrib2kml.php?lang='.$lang.'&racenumber='.$racenumber.'&token='.$token.'">SolToGrib</a>'."\n";
			break;
		}
	}
}

//---------
function enqueue(){
global $version;
echo '
<div id="footer">
Version '.$version.' (<a target="_blank" href="https://creativecommons.org/licenses/by-sa/3.0/fr/">cc - by sa</a>) <a href="mailto:jean.fruitet@free.fr">JF</a> 2016-2017  &nbsp;
</div>
</body>
</html>
';
}


// ----------------------------
function get_url_pere($path) {
// Retourne l'URL du répertoire contenant le script
// global $PHP_SELF;
// DEBUG
// echo "<br>PHP_SELF : $PHP_SELF\n";
//	$path = $PHP_SELF;
	$nomf = substr( strrchr($path, "/" ), 1);
	if ($nomf){
		$pos = strlen($path) - strlen($nomf) - 1;
		$pere = substr($path,0,$pos);
	}
	else
		$pere = $path;
	return $pere;
}


// ----------------------------
// Recursive rmdir
// http://fr.php.net/manual/fr/function.rmdir.php
// From itay@itgoldman.com
function rrmdir($src) {
    $dir = opendir($src);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            $full = $src . '/' . $file;
            if ( is_dir($full) ) {
                rrmdir($full);
            }
            else {
                unlink($full);
            }
        }
    }
    closedir($dir);
    rmdir($src);
}

?>