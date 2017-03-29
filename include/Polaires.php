<?php
// ENREGISTREMENT DE LA POLAIRE DES VOILES à la VRTool
// JF - Avril 2009
// V 0.1 
/**
# MyPolars.cvs - Polar chart points database. Format:			
# WindAngle	WindSpeed	BoatSpeed	SailNumber
62	0.54	3.24	8 (genois)
87	0.54	3.874	16
122	0.54	1.848	8
161	0.54	1.534	32
173	0.54	0.804	32
25	1.08	0.315	16
31	1.08	1.013	8
34	1.08	0.737	32
73	1.08	3.092	16
74	1.08	3.186	16
*/
/**
1) Charger le tableau des allures 
	$t_allures=ChargerPolaires($dossier_polaires);
2) Charger les nouvelles allures 
	$t_allures= new Allure($WindAngle, $WindSpeed, $BoatSpeed, $SailNumber, $BrokenSail, $Amure);
3) Ordonner le tableau des allures 
	$t_allures= OrdonnerAllures($t_allures);
	$t_allures= PurgerDoublons($t_allures);
4) Enregistrer le fichier des Polaires 
	EnregistrerPolaires($dossier_polaires, GenerePolaires($t_allures));
*/


if (!isset($dir_serveur)){
	$dir_serveur = dirname($_SERVER['SCRIPT_FILENAME']) ; 
}

$dossier_polaires="Polaires";
$fichierpolaires="MyPolar";
$extension_polaire=".csv";

$t_polaires = array(); // tableau global

class Allure{
	// 
	var $WindAngle;
	var $WindSpeed;
	var	$BoatSpeed;
	var $SailNumber;
	var $BrokenSail;
	var $SailName;
	var $Amure;
	
	function GetWindAngle(){
		return $this->WindAngle;
	}
	function GetWindSpeed(){
		return $this->WindSpeed;
	}
	function GetBoatSpeed(){
		return $this->BoatSpeed;
	}
	function GetSailNumber(){
		return $this->SailNumber;
	}	
	function GetAmure(){
		return $this->Amure;
	}

	function Allure($WindAngle, $WindSpeed, $BoatSpeed, $SailNumber, $BrokenSail, $Amure){
		$this->WindAngle= $WindAngle;
		$this->WindSpeed=$WindSpeed;
		$this->BoatSpeed=$BoatSpeed;
		$this->SailNumber=$SailNumber;
		$this->BrokenSail=$BrokenSail;
		$this->SailName=$this->GetSailName($SailNumber);
		$this->Amure=$Amure; // babord, tribord  ; port, starboard 
	}

	function GetSailName($SailNumber){
		switch ($SailNumber){
			case 2 : return 'spi'; break;
			case 4 : return 'foc2'; break;
			case 8 : return 'genois'; break;
			case 16 : return 'code zero'; break;
			case 32 : return 'spi leger'; break;
			case 64 : return 'gennaker'; break;
			default : return 'foc'; break;
		}
	}
	
	function IsBrokenSail(){
		return ($this->BrokenSail!=0);
	}
	
	function DisplayAllure(){
		$s='';
		$s.=$this->WindAngle.';'.$this->WindSpeed.';'.$this->BoatSpeed.';'.$this->SailNumber.' ('.$this->SailName;
		if ($this->IsBrokenSail())
			$s.= 'broken';
		$s.=')'."\n";
		return $s;
	}
	
	function DisplayPolar(){
// # MyPolars.cvs - Polar chart points database. Format:			
// # WindAngle	WindSpeed	BoatSpeed	SailNumber
		$s=$this->WindAngle.';'.$this->WindSpeed.';'.$this->BoatSpeed.';'.$this->SailNumber."\n";
		return $s;
	}
} // Fin de la classe Allure



// --------------------
function Inserre_une_allure($t_allures, $allure){
// BOGGUEE 
	$echange=true;
	$sortie=false;
	$n=count($t_allures);
	if ($n>0){
		while ($sortie==false){
			$i=0;
			$echange=false;
			while (($i<$n-1) && ($echange==false) && ($sortie==false)){
				// comparaison 
				$ordre=compare_allure($allure, $t_allures[$i]);
				if ($ordre==-1){ // a<b
					// echanger
					$aux=$t_allures[$i];
					$t_allures[$i]=$allure;
					// repousser
					$j=$n;
					while ($j>$i+1){
						$t_allures[$j+1]=$t_allures[$j];
						$j--;
					}
					$t_allures[$i+1]=$aux;
					$echange=true;
					$sortie=true;
				}
				else if ($ordre==0){ // egalite
					$sortie=true;
				}
				$i++;
			}
		}
	}
	
	// echo "<br />DEBUG :: test_allures.php :: 109 :  <b>Parcours ORDONNE</b><br />\n";
	// print_r($t_allures);
	return ($t_allures);

}

// --------------------
function InserreAlluresBateaux($t_voilier, $t_allures){
// la class Allure a partir de la class Voilier
	if ($t_voilier){
		for ($i=0; $i<count($t_voilier); $i++){
			// $t_allures=Inserre_une_allure($t_allures, new Allure($t_voilier[$i]->GetWindAngle(), $t_voilier[$i]->GetWindSpeed(), $t_voilier[$i]->GetBoatSpeed(), $t_voilier[$i]->GetBoatSail(), $t_voilier[$i]->GetBrokenSails(), 0)); 
			$t_allures[]= new Allure($t_voilier[$i]->GetWindAngle(), $t_voilier[$i]->GetWindSpeed(), $t_voilier[$i]->GetBoatSpeed(), $t_voilier[$i]->GetBoatSail(), $t_voilier[$i]->GetBrokenSails(), 0); 
		}
	}
	return $t_allures;
}

// --------------------
function GenerePolaires($t_allures){
global $fichierpolaires;
global $extension_polaire;
global $course;
global $t_course;
	$s='#'.$fichierpolaires.'_'.$t_course[$course].$extension_polaire.'- Polar chart points database. Format:
#WindAngle;WindSpeed;BoatSpeed;SailNumber'."\n";
	if (($t_allures) && is_array($t_allures)){
		$n=count($t_allures);
		for ($i=0; $i<$n; $i++){
			$s.=$t_allures[$i]->DisplayPolar();
		}
	}
	return $s;
}


// --------------------
function compare_allure($a, $b){
// comparaisons de deux allures
	// retourne 1 si a>b, 0 si a==b, et -1 sinon
	$a_ws=(float)$a->GetWindSpeed();
	$a_wa=(float)$a->GetWindAngle();
	$a_bs=(float)$a->GetBoatSpeed(); 
	$a_s=(float)$a->GetSailNumber();
	$b_ws=(float)$b->GetWindSpeed();
	$b_wa=(float)$b->GetWindAngle();
	$b_bs=(float)$b->GetBoatSpeed(); 
	$b_s=(float)$b->GetSailNumber();
	
	if ($a_ws>$b_ws) $ordre= 1;
	elseif (($a_ws==$b_ws) && ($a_wa>$b_wa)) $ordre= 1;
	elseif (($a_ws==$b_ws) && ($a_wa==$b_wa) && ($a_bs>$b_bs))  $ordre= 1;
	elseif (($a_ws==$b_ws) && ($a_wa==$b_wa) && ($a_bs==$b_bs) &&  ($a_s>$b_s)) $ordre= 1;
	elseif (($a_ws==$b_ws) && ($a_wa==$b_wa) && ($a_bs==$b_bs) &&  ($a_s==$b_s)) $ordre= 0;
	else $ordre= -1;
	// echo $ordre;
	return $ordre;
}


// --------------------
function PurgerDoublons($t_allures){
	// DEBUG
	// echo "<br />DEBUG :: test_allures.php :: 206 :  <b>Polaires</b><br />\n";
	// print_r($t_allures);
	// tableau ordonne en entree 
	$t_allure_ord=array();
	$k=0;
	$n=count($t_allures);
	if ($n>0){
		$i=0;
		while ($i<$n-1){
			// comparaison 
			if (compare_allure($t_allures[$i], $t_allures[$i+1])==-1){ // a<b
				$t_allure_ord[$k]=$t_allures[$i];
				$k++;
			}
			$i++;
		}
		$t_allure_ord[$k]=$t_allures[$i];
		$k++;
	}
	
	// echo "<br />DEBUG :: test_allures.php :: 226 :  <b>Parcours ORDONNE et PURGE des doublons</b><br />\n";
	// print_r($t_allure_ord);
	return ($t_allure_ord);
}

// --------------------
function OrdonnerAllures($t_allures){
	// DEBUG
	// echo "<br />DEBUG :: test_allures.php :: 98 :  <b>Parcours</b><br />\n";
	// print_r($t_allures);
	
	$echange=true;
	$n=count($t_allures);
	if ($n>0){
		while ($echange==true){
			$i=0;
			$echange=false;
			while ($i<$n-1){
				// comparaison 
				if (compare_allure($t_allures[$i], $t_allures[$i+1])==1){ // a>b
					// echanger
					$aux=$t_allures[$i];
					$t_allures[$i]=$t_allures[$i+1];
					$t_allures[$i+1]=$aux;
					$echange=true;
				}
				$i++;
			}
		}
	}
	
	// echo "<br />DEBUG :: test_allures.php :: 109 :  <b>Parcours ORDONNE</b><br />\n";
	// print_r($t_allures);
	return ($t_allures);
}



// -----------------------
function EnregistrerPolaires($dossier_polaires, $contenu){
// Deux fichiers sont crees : un fichier d'archive et un fichier courant (dit de cache) au contenu identique.
global $dir_serveur;
global $fichierpolaires;
global $extension_polaire;
global $course;
global $t_course;

	// Commencer par enregister le fichier 
	$f_polaires=$dir_serveur.'/'.$dossier_polaires.'/'.$fichierpolaires.'_'.$t_course[$course].$extension_polaire;
	$fp_data = fopen($f_polaires, 'w');
	if ($fp_data ){
		fwrite($fp_data, $contenu);
		fclose($fp_data);
	}
	echo '<br>Le fichier de polaires <a href="'.$dossier_polaires.'/'.$fichierpolaires.'_'.$t_course[$course].$extension_polaire.'" target="_blank"><b>'.$fichierpolaires.'_'.$t_course[$course].$extension_polaire.'</b></a> a été actualisé.'."\n";	
}

// -----------------------
function ChargerPolaires($dossier_polaires){
// retourne le tableau de polaires archive
global $dir_serveur;
global $fichierpolaires;
global $extension_polaire;
global $course;
global $t_course;
$tallures=array();
$t_polaires=array();
	$f_data_name=$dir_serveur.'/'.$dossier_polaires.'/'.$fichierpolaires.'_'.$t_course[$course].$extension_polaire;
	// DEBUG
	// echo "<br>Fichier courant: $f_data_name\n";
	
	if (file_exists($f_data_name)){
		$t_polaires=file($f_data_name);
		for ($i=0; $i<count($t_polaires); $i++){ 
			if ($t_polaires[$i][0]!='#'){// commentaire
				list($WindAngle,$WindSpeed,$BoatSpeed,$SailNumber)=explode(';',$t_polaires[$i]);
				$tallures[]= new Allure($WindAngle,$WindSpeed,$BoatSpeed,$SailNumber, 0, 0); 
			}
		}
		return $tallures;
	}
	else{
		return NULL;
	}
}


// --------------------
function AffichePolaires($t_allures){
	if (($t_allures) && is_array($t_allures)){
		$n=count($t_allures);
		for ($i=0; $i<$n; $i++){
			echo '<br>'.$t_allures[$i]->DisplayPolar();
		}
	}
}


// --------------------
function GenereEntetePolaires(){
global $groupe; // groupe courant determine aussi le prefixe du fichier 
global $t_groupe; // code du groupe
global $t_nom_course;
global $course;
global $t_course;
	$s='<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>polaires des bateaux du '.$t_nom_course[$course].' - '.date('Y/m/d H').'</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<meta name="Author" content="JF">
	<meta name="description" content="Génération de fichiers de polaires pour '.$t_nom_course[$course].'."/>
    <link rel="author" title="Auteur" href="mailto:jean.fruitet@free.fr">
	<link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
';
	
	echo '<table border="0" width="100%">
<tr valign="top">
<td width="80%"><h1 align="center">Polaires des vitesse <br>pour la '.$t_nom_course[$course].' - '.date('Y/m/d H').'</h1>
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
<h3>polaires '.strtoupper($t_groupe[$groupe]).'</h3><p>du '.date("d/m/Y H").'</p>
'."\n";

return $s;
}


// -------------------------
function GenereEnQueuepolaires(){
	$s.='</body>
</html>
';
	return $s;
}


?>