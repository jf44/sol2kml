<?php
// JF

// ------------------
function afficheImages($prefixe_image='', $extension_image='jpg,png'){
$nobj=0;
$ndir=0;
$njpg=0;
$tjpg=array();

 	$t_ext=explode(',',$extension_image);

// DEBUG
//echo "<br>Extensions :\n";
//print_r($t_ext);
//exit;
	$tjpg=array();
	$sep = '/';
	$path="./";

	$h1=opendir($path);
    while ($f = readdir($h1) )
    {
		if (($f != ".") && ($f != "..")) {
			// Les fichiers commençant par '_' ne sont pas affichés
			// Ni le fichier par defaut ni le fichier de cache ne sont affichés
			// Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
			// les fichier n'ayant pas la bonne extension ne sont pas affichés
	        if (!is_dir($path.$sep.$f)){
                if ((!empty($prefixe_image) && (substr($f,0,3) == substr($prefixe_image,0,3)))
					 // Les fichiers ne commençant pas par le nom par defaut ne sont pas affichés
					|| empty($prefixe_image)) {
					foreach(  $t_ext as $ext){
						if (strpos($f, $ext) !== false){
                        	$g= eregi_replace($ext,"",$f) ;
							// DEBUG
							// echo "<br>g:$g  g+:$g$ext  f:$f\n ";
        			  		if ((substr($g,0,1) != "_") // Les fichiers commençant par '_' ne sont pas affichés
								&&
								(strtoupper($g.$ext) == strtoupper($f)) // les fichier n'ayant pas la bonne extension ne sont pas affichés
								)
							{
			            	   	$nobj ++;
              			 		$njpg ++;
	               				$tjpg[$f] = $f ;
							}
						}
					}
				} // fin traitement d'un fichier
			} // fin du test sur entrees speciales . et ..
		}  // fin du while sur les entrees du repertoire traite
	}
	closedir($h1);

	if ($njpg > 0) {
	    asort($tjpg);
	    $i=0;
		while (list($key) = each($tjpg)) {
	       	if (!$i){
               echo '<option value="'.$tjpg[$key].'" selected>'.$i."\n";
            }
            else{
                echo '<option value="'.$tjpg[$key].'">'.$i."\n";
            }
	       	$i++;
    	}
	}
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="ROBOTS" content="none,noarchive">
	<meta name="Author" content="JF">
	<meta name="description" content="R&eacute;gates virtuelles"/>
    <link rel="author" title="Auteur" href="mailto:jean.fruitet@free.fr">
	<link href="../style.css" rel="stylesheet" type="text/css">
	<title>R&eacute;gates virtuelles</title>
</head>

<SCRIPT LANGUAGE="JavaScript">
var timeoutID;
var rotate_delay = 3000; // rotate_delay indique le délai d'affichage entre deux images en cas de déroulement automatique (en millisecondes)
current = 0; // current indique le numéro de l'image de départ. 0 correspond à la première image de votre liste.
function next() {
if (document.slideform.slide[current+1]) {
document.images.show.src = document.slideform.slide[current+1].value;
document.slideform.slide.selectedIndex = ++current;
   }
else first();
}
function previous() {
if (current-1 >= 0) {
document.images.show.src = document.slideform.slide[current-1].value;
document.slideform.slide.selectedIndex = --current;
   }
else last();
}
function first() {
current = 0;
document.images.show.src = document.slideform.slide[0].value;
document.slideform.slide.selectedIndex = 0;
}
function last() {
current = document.slideform.slide.length-1;
document.images.show.src = document.slideform.slide[current].value;
document.slideform.slide.selectedIndex = current;
}
function ap(text) {
document.slideform.slidebutton.value = (text == "Stop") ? "Start" : "Stop";
rotate();
}
function change() {
current = document.slideform.slide.selectedIndex;
document.images.show.src = document.slideform.slide[current].value;
}
function rotate() {
if (document.slideform.slidebutton.value == "Stop") {
current = (current == document.slideform.slide.length-1) ? 0 : current+1;
document.images.show.src = document.slideform.slide[current].value;
document.slideform.slide.selectedIndex = current;
timeoutID=window.setTimeout("rotate()", rotate_delay);
   }
}
function plus_vite() {
if (rotate_delay >=1000) {
	rotate_delay-=1000;
   }
  window.clearTimeout(timeoutID);
  rotate();
}
function moins_vite() {
if (rotate_delay <10000) {
	rotate_delay+=1000;
   }
   window.clearTimeout(timeoutID);
   rotate();
}

var str_delay=new String("Temporisation : ");
function affiche_tempo() {
	alert(str_delay + rotate_delay);
}
//  End -->
</script>

</head>

<body>


<form name=slideform>
<table cellspacing=1 cellpadding=4 bgcolor="#000000">
<tr>
<td align=center bgcolor="#C0C0FF" colspan="2">Le RienKaNou en course  &nbsp;
:: <a href="../">Retour</a>
</td>
</tr>
<tr>
<td align="right">
<input type=button onClick="first();" value="|<<" title="Début">
<br>
<input type=button onClick="previous();" value="<<" title="Précédent">
<br>
<input type=button onClick="moins_vite(); " value="-" title="Ralentir">
<br>
<input type=button name="slidebutton" onClick="ap(this.value);" value="Start" title="AutoPlay">
<br>
<input type=button onClick="plus_vite(); " value="+" title="Accélérer">
<br>
<input type=button onClick="next();" value=">>" title="Suivant">
<br>
<input type=button onClick="last();" value=">>|" title="Fin">
<br>
<select name="slide" onChange="change();">
<?php
afficheImages('','jpg,png,gif');
?>
</select>
</td>

<td align=center bgcolor="white" width=800 height=550>
<img src="Image00.jpg" name="show">
</td>
</tr>
<tr>
<td align=center bgcolor="#C0C0FF" colspan="2">
<a name="credits"></a>
<p align="center"><span class="small"><b>Cr&eacute;dits</b></span><br />
<a class="small" target="_blank" href="http://www.sailonline.org/">SailOnLine</a>
- <a class="small" target="_blank" href="http://sol.brainaid.de/sailonline/toolbox/">Brainaid Toolbox</a>
- <a class="small" target="_blank" href="http://sol.kroppyer.nl/">Kroppyer'tools for sailonline.org</a>
- <a class="small" target="_blank" href="http://www.navmonpc.com/">NavMonPc</a>
- <a class="small" target="_blank" href="https://sourceforge.net/projects/qtvlm/">qtVlm</a>
<br />
<a class="small" mailto="jean.fruitet@free.fr">jf44</a>
</p>
</td></tr>
</table>
</form>
</body>
</html>
