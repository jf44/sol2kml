<?php

// JF

// Classement des bateaux
include_once("include/GeoCalc.class.php");

// CLASSER LES BATEAUX


$distance_depart_marque=array();
$distance_arrivee_marque=array();

// --------------------
function distance_marque($lon, $lat, $lonm, $latm){
// retourne la distance par raaport à la marque;
	$oGC = new GeoCalc();
	return($oGC->GCDistance($lon, $lat, $lonm, $latm));
}



// --------------------
function initialise_table_marques(){
global $course;
global $t_marques_course;
global $distance_depart_marque;
global $distance_arrivee_marque;
global $ok_marque_passage;
global $t_ok_marque_passage;
global $lon_marque;
global $lat_marque;
global $mode_passage;

$ok_marque_passage=$t_ok_marque_passage[$course];
$mode_passage=$t_mode_passage[$course];
	if ($ok_marque_passage){
		$t_marques=$t_marques_course[$course];
		// Boston Galway RKN : array('-70.05432160738889,42.42977229374129,0', '-14.53439373206126,66.37932820634309,0', '-9.479982806707477,53.05945881914277,0');
		$nm=count($t_marques);
		list($lonmdep, $latmdep, $altmdep)=explode(',',$t_marques[0]);
		list($lonmarr, $latmarr, $altmarr)=explode(',',$t_marques[$nm-1]);
		list($lon_marque, $lat_marque, $alt_marque)=explode(',',$t_marques[1]);
		for ($i=0; $i<$nm; $i++){ // de la seconde à l'avant dernière marque 0 : depart nm : arrivée
			list($lon_m, $lat_m, $alt_m)=explode(',',$t_ms[$i]);
			$distance_depart_m[$i]=distance_m($lonmdep, $latmdep, $lon_m, $lat_m);
			$distance_arrivee_m[$i]=distance_m($lon_m, $lat_m, $lonmarr, $latmarr);
			echo "<br>DEBUG // MARQUE de course $i : $lonm, $altm, $altm<br>Distance départ : ".$distance_depart_m[$i]."<br>Distance arrivée : ".$distance_arrivee_m[$i]."\n"; 
		}
	}
}

// --------------------
function compare_classement_Marques($voilier_a, $voilier_b, $numero_marque){
// comparaisons de deux coordonnées par rapport à une liste de marques de parcours
// le tableau des coordonnees des marques de parcours
/// retourne 1 si a>b, 0 si a==b, et -1 sinon

global $course;
global $t_marques_course;
global $distance_depart_marque;
global $distance_arrivee_marque;
	// coordonnees géographiques de la marque
	list($lonm, $latm, $altm)=explode(',',$t_marques_course[$course][$numero_marque]);	
	// coordonnées arrivee
	list($lonarr, $latarr, $altarr)=explode(',',$t_marques_course[$course][count($t_marques_course[$course])-1]);
	
	$a_lon=(float)$voilier_a->GetLon();
	$a_lat=(float)$voilier_a->GetLat();
	$b_lon=(float)$voilier_b->GetLon();
	$b_lat=(float)$voilier_b->GetLat();
	
	// on calcule la distance restante
	if ($voilier_a->GetMarqueValide()){
		$a_cla=distance_marque($lon_a, $lat_a, $lonmarr, $lonmarr);
	}
	else{
		$a_cla=$distance_arrivee_marque[$numero_marque]+distance_marque($lon_a, $lat_a, $lonm, $latm);
	}

	if ($voilier_b->GetMarqueValide()){
		$b_cla=distance_marque($lon_b, $lat_b, $lonmarr, $lonmarr);
	}
	else{
		$b_cla=$distance_arrivee_marque[$numero_marque]+distance_marque($lon_b, $lat_b, $lonm, $latm);
	}
	
	echo '<br>COMPARAISON : ('.$a_cla.') avec ('.$b_cla.') -- &gt; '."\n";
	if ($a_cla>$b_cla) $ordre= -1; // on calcule la distance restante
	elseif ($a_cla==$b_cla) $ordre= 0;
	else $ordre= 1;
	echo $ordre;
	return $ordre;
}

// --------------------
function OrdonnerBateauxMarques($t_voilier){
// $t_voilier : input tableau de Voilier()	; retourne le tableau classé
// selon le classement de la course par rapport a des marques de parcours
	
	initialise_table_marques(); // affecte tableaux globaux de marques et la distance à ces marques 
	$num_marque=1; // Classement par rapport à la marque N°1
	$echange=true;
	
	$n=count($t_voilier);
	if ($n>0){
		while ($echange==true){
			$i=0;
			$echange=false;
			while ($i<$n-1){
				// comparaison 
				$ordre=compare_classement_Marques($t_voilier[$i], $t_voilier[$i+1], $num_marque); 
				if ($ordre==1){ //
					// echanger
					$aux=$t_voilier[$i];
					$t_voilier[$i]=$t_voilier[$i+1];
					$t_voilier[$i+1]=$aux;
					$echange=true;
				}
				$i++;
			}
		}
	}
	
	return ($t_voilier);
}

// --------------------
function ClassementBateauxMarques($t_voilier){
// met a jour le rang du bateau après classement par rapport à la marque de course
	$t_voilier=OrdonnerBateauxMarques($t_voilier);
	$t_voilier=SetClassementBateaux($t_voilier);
	return $t_voilier;
}

// --------------------
function GenereClassementBateauxMarques(){
global $t_voilier;
	$t_voilier=ClassementBateauxMarques($t_voilier);
	return AfficheClassement($t_voilier);
}

// --------------------
function compare_distance($a, $b, $ref){
// comparaisons de deux coordonnées par rapport à un point geographique de reference
// par la methode du grand cercle
	// retourne 1 si $a>b, 0 si a==$b, et -1 sinon

	$a_lon=(float)$a->GetLon();
	$a_lat=(float)$a->GetLat();
	$a_alt=(float)$a->GetAlt(); 
	$b_lon=(float)$b->GetLon();
	$b_lat=(float)$b->GetLat();
	$b_alt=(float)$b->GetAlt();
	
	// point de réference
	$ref_lon=(float)$ref->GetLon();
	$ref_lat=(float)$ref->GetLat();
	$ref_alt=(float)$ref->GetAlt(); 

	$oGC = new GeoCalc();
	
	$a_reference= $oGC->GCDistance($ref_lat, $ref_lon, $a_lat, $a_lon);
	$b_reference= $oGC->GCDistance($ref_lat, $ref_lon, $b_lat, $b_lon);
	
	echo '<br>DEBUG :: kml_trajectoire.php :: Ligne 117 :: COMPARAISON : ('.$a_lon.','.$a_lat.','.$a_alt.') avec ('.$b_lon.','.$b_lat.','.$b_alt.') -- &gt; '."\n";
	if ($a_reference>$b_reference) $ordre= 1;
	else if ($a_reference==$b_reference) $ordre= 0;
	else $ordre= -1;
	echo $ordre;
	return $ordre;
}


// --------------------
function compare_classement($voilier_a, $voilier_b){
// comparaisons de deux rangs
	// retourne 1 si a>b, 0 si a==b, et -1 sinon
	$a_cla=(float)$voilier_a->GetClassement();
	$b_cla=(float)$voilier_b->GetClassement();
	// echo '<br>COMPARAISON : ('.$a_cla.') avec ('.$b_cla.') -- &gt; '."\n";
	if ($a_cla>$b_cla) $ordre= 1;
	elseif ($a_cla==$b_cla) $ordre= 0;
	else $ordre= -1;
	// echo $ordre;
	return $ordre;
}


// --------------------
function OrdonnerBateaux($t_voilier){
// $t_voilier : input tableau de Voilier()	; retourne le tableau classé
	$echange=true;
	
	$n=count($t_voilier);
	if ($n>0){
		while ($echange==true){
			$i=0;
			$echange=false;
			while ($i<$n-1){
				// comparaison 
				if (compare_classement($t_voilier[$i], $t_voilier[$i+1])==1){ //
					// echanger
					$aux=$t_voilier[$i];
					$t_voilier[$i]=$t_voilier[$i+1];
					$t_voilier[$i+1]=$aux;
					$echange=true;
				}
				$i++;
			}
		}
	}
	
	return ($t_voilier);
}

// --------------------
function SetClassementBateaux($t_voilier){
global $LE_GRUMEAU;
// Met à jour le rang des voiliers en fonction du classement effectué par OrdonnerClassement()
// $t_voilier : tableau ordonné de voiliers	
	$n=count($t_voilier);
	if ($n>0){
		$rang=1;
		for ($i=0; $i<$n; $i++){
			if ($t_voilier[$i]->GetPseudo()!=$LE_GRUMEAU){
				$t_voilier[$i]->SetRang($rang);
				$rang++;
			}
			else{
				$t_voilier[$i]->SetRang('');
			}
		}
	}
	return ($t_voilier);
}

// --------------------
function ClassementBateaux($t_voilier){
// met a jour le rang du bateau après classement
	$t_voilier=OrdonnerBateaux($t_voilier);
	$t_voilier=SetClassementBateaux($t_voilier);
	return $t_voilier;
}


// --------------------
function AfficheClassement($t_voilier){
global $groupe;
global $course;
global $t_course;
global $t_groupe;
global $t_nom_groupe;
	$s='';
	if (isset($t_voilier) && ($t_voilier)){
		$s='<hr><h5>Classement '.strtoupper($t_nom_groupe[$groupe]).' du '.date("d/m/Y H").'H GMT</h5>
<table cellspacing="1" cellpadding="2" bgcolor="#333300">
<tr bgcolor="#ffffaa"><td><i>'.strtoupper($t_groupe[$groupe]).'</i></td><td><b>Bateau</b></td><td><b>'.$t_course[$course].'<b></td></tr>'."\n";
		for($i=0; $i< count($t_voilier); $i++){ 
			if (isset($t_voilier[$i]) && ($t_voilier[$i])){
				$s.='<tr bgcolor="#ffffff"><td><i>'.$t_voilier[$i]->GetRang().'</i></td><td>'.$t_voilier[$i]->GetPseudo().'</td><td>'.$t_voilier[$i]->GetClassement().'</td></tr>'."\n";
			}
		}
		$s.='
</table>
<hr>
';
	}
	return $s;
}

// --------------------
function GenereClassementBateaux(){
global $t_voilier;
	$t_voilier=ClassementBateaux($t_voilier);
	return AfficheClassement($t_voilier);
}


/*
// --------------------

include_once("include/GeoCalc.class.php");

function compare_distance($a, $b, $ref){
// comparaisons de deux coordonnées par rapport à un point geographique de reference
// par la methode du grand cercle
	// retourne 1 si $a>b, 0 si a==$b, et -1 sinon

	$a_lon=(float)$a->GetLon();
	$a_lat=(float)$a->GetLat();
	$a_alt=(float)$a->GetAlt(); 
	$b_lon=(float)$b->GetLon();
	$b_lat=(float)$b->GetLat();
	$b_alt=(float)$b->GetAlt();
	
	// point de réference
	$ref_lon=(float)$ref->GetLon();
	$ref_lat=(float)$ref->GetLat();
	$ref_alt=(float)$ref->GetAlt(); 

	$oGC = new GeoCalc();
	
	$a_reference= $oGC->GCDistance($ref_lat, $ref_lon, $a_lat, $a_lon);
	$b_reference= $oGC->GCDistance($ref_lat, $ref_lon, $b_lat, $b_lon);
	
	echo '<br>DEBUG :: kml_trajectoire.php :: Ligne 117 :: COMPARAISON : ('.$a_lon.','.$a_lat.','.$a_alt.') avec ('.$b_lon.','.$b_lat.','.$b_alt.') -- &gt; '."\n";
	if ($a_reference>$b_reference) $ordre= 1;
	else if ($a_reference==$b_reference) $ordre= 0;
	else $ordre= -1;
	echo $ordre;
	return $ordre;
}
*/


// --------------------
function GenereEnteteClassement(){
global $groupe; // groupe courant determine aussi le prefixe du fichier KML
global $t_groupe; // code du groupe
global $t_nom_course;
global $course;
	$s='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>Classement des bateaux du '.$t_nom_course[$course].' - '.date('Y/m/d H').'</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="Author" content="JF">
	<meta name="description" content="Génération de fichiers de classement pour '.$t_nom_course[$course].'."/>
    <link rel="author" title="Auteur" href="mailto:jean.fruitet@free.fr">
	<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
';
	
	echo '<table border="0" width="100%">
<tr valign="top">
<td width="80%"><h1 align="center">Classement <br>pour la '.$t_nom_course[$course].' - '.date('Y/m/d H').'</h1>
</td>
<td align="center" width="20%">Contact 
<a href="mailto:jean.fruitet@free.fr">JF</a>
<br />'.$version.'
<br><span class="small"><b>Auteurs</b><br>'.$auteurs.'</span>
</td></tr>
<tr valign="top">
<td colspan="2" width="100%" align="center"><b>Avertissement</b>
<br />Les données sont collectées sur le site de jeu en ligne 
<a href="'.$t_url_course[$course].'" target="_blank">'.$t_nom_course[$course].'</a> 
de <a href="http://www.manyplayers.com"><b>ManyPlayers</b>&reg;</a>, 
<br />Elles sont fournies &quot;en l\'état&quot;, sans garantie quant à leur fiabilité. 
<br />
Leur utilisation n\'engage ni la société ManyPlayers&reg; ni l\'auteur du programme.
</td></tr>
</table>
<h3>Classement '.strtoupper($t_groupe[$groupe]).'</h3><p>du '.date("d/m/Y H").'</p>
'."\n";

return $s;
}


// -------------------------
function GenereEnQueueClassement(){
	$s.='</body>
</html>
';
	return $s;
}




// -----------------------
function EnregistreClassement($dossier_classement, $archive=false, $contenu){
// Deux fichiers sont crees : un fichier d'archive et un fichier courant (dit de cache) au contenu identique.
// c'est ce fichier de cache (dont le nom est toujours identique) qui est appelé par le fichier rkn.kml lu par GoogleEarth
// Le dossier d'achive est zippé

global $dir_serveur;
global $fichierclassement_courant;
global $fichierclassement_cache;
global $extension_mp;
global $extension_zip;
	
	// Commencer par enregister le fichier KML
	$f_cache_name=$dir_serveur.'/'.$dossier_classement.'/'.$fichierclassement_cache.$extension_mp;
	$fp_data = fopen($f_cache_name, 'w');
	if ($fp_data ){
		fwrite($fp_data, $contenu);
		fclose($fp_data);
	}
	echo '<br>Le fichier <a href="'.$f_name_cache.'"><b>'.$f_name_cache.'</b></a> a été actualisé.'."\n";	
	
	if ($archive==true){
		// faire une copie zippee du  dossier $dossier_classement 
		// if (creer_fichier_zip($dir_serveur, $dossier_classement, $fichierclassement_cache_3d)){
			// if (creer_fichier_zip('', $dossier_classement, $fichierclassement_cache_3d)){
		$t_fichiers=array();
		$t_fichiers[0]=$dossier_classement.'/'.$fichierclassement_cache.$extension_mp;
		
		if (creer_fichier_zip($dossier_classement, $t_fichiers, $fichierclassement_cache)){
				// creer un fichier d'archive
				// le nom du fichier d'archive recoit une date+heure qui sera utilisee 
				// pour verifier si le delai depuis la génération précédente est suffisant
				$f_name_cache=$dir_serveur.'/'.$nom_fichier_zip;
				$f_archive=$dir_serveur.'/'.nom_fichier($nom_fichier_zip).date('YmdH').$extension_zip;
				copy($f_name_cache, $f_archive);
				echo '<br>Le fichier d\'archive <a href="'.$f_name_cache.'"><b>'.$f_name_cache.'</b></a> a été actualisé.'."\n";
		}
	}
}

// -----------------------
function ExisteClassement(){
// verifie si une generation a ete faite durant l'heure courante
global $dir_serveur;
global $dossier_classement;
global $fichierclassement_courant;
global $fichierclassement_cache;
global $extension_mp;
global $extension_zip;

	$f_data_name=$dir_serveur.'/'.$fichierclassement_cache.date('YmdH').$extension_zip;
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