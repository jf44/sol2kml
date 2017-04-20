<?php
// JF 2017

// DEFINITION D'UN CLASSE Barbule

define ('MAX_WIND', 70.0);
define ('MAX_COLOR_BARBULES', 15);
// TWS en noeuds [0.0 ... 50.0]
$t_couleurs = ['ffffff','0000dd','0000ff', '00aa00','00dd00','00ff00','ffaa00','ffd000','ffff00','aa0000','dd0000','ff0000','aa00aa','dd00dd','ff00ff'];


// -----------------------------------
// la classe Barbules (fleches de vent)
class Barbule{
	var $id;
	var $longitude;
	var $latitude;
	var $twd;
	var $tws;
	var $modele;
	var $taille;
	var $couleur; // RRVVBB



 	function GetNumero(){
		return $this->id;
	}
	function GetLongitude(){
		return $this->longitude;
	}
	function GetLatitude(){
		return $this->latitude;
	}
	function GetCouleur(){
		return $this->couleur;
	}
	function GetModele(){
		if (empty($this->modele)){
            return 'fleche';
		}
		else{
			switch ($this->modele){
				case 1 : return 'barbule'; break;
				default : return 'fleche'; break;
			}
		}
	}
	function GetTwd(){
		return $this->twd;
	}
	function GetTws(){
		return $this->tws;
	}

	function GetTaille(){
		return $this->taille;
	}

	function SetTailleEtCouleur(){
		// calcule une longueur et une couleur en fonction de TWS
		global $t_couleurs;
		if (!empty($this->tws)){
			$this->taille =  ($this->tws);

			if ($this->tws < 3.0){ $this->couleur = $t_couleurs[0]; }
			else if ($this->tws < 5.0){ $this->couleur = $t_couleurs[1]; }
            else if ($this->tws < 7.0){ $this->couleur = $t_couleurs[2]; }
            else if ($this->tws < 9.0){ $this->couleur = $t_couleurs[3]; }
            else if ($this->tws < 11.0){ $this->couleur = $t_couleurs[4]; }
            else if ($this->tws < 13.0){ $this->couleur = $t_couleurs[5]; }
            else if ($this->tws < 15.0){ $this->couleur = $t_couleurs[6]; }
            else if ($this->tws < 17.0){ $this->couleur = $t_couleurs[7]; }
            else if ($this->tws < 20.0){ $this->couleur = $t_couleurs[8]; }
            else if ($this->tws < 23.0){ $this->couleur = $t_couleurs[9]; }
			else if ($this->tws < 26.0){ $this->couleur = $t_couleurs[10]; }
			else if ($this->tws < 29.0){ $this->couleur = $t_couleurs[11]; }
            else if ($this->tws < 32.0){ $this->couleur = $t_couleurs[12]; }
			else if ($this->tws < 39.0){ $this->couleur = $t_couleurs[13]; }
			else { $this->couleur = $t_couleurs[14]; }
		}
		else{
			$this->taille = 10.0;
			$this->couleur=$t_couleurs[6];
		}

	}

	function SetBarbule($id, $latitude, $longitude,	$modele, $twd, $tws){
            $this->id=$id;
			$this->latitude=$latitude;
			$this->longitude=$longitude;
			$this->modele=$modele;     // 0 fleche 1 barbule
			$this->twd=$twd;
			$this->tws=$tws;
            $this->SetTailleEtCouleur();
	}

	function Affiche(){
		echo ('Id: '.$this->id.', Modele: '.$this->modele.', Lat: '.$this->latitude.', Long: '.$this->longitude.', TWD: '.$this->twd.', TWS: '.$this->tws.', Taille: '.$this->taille.', Couleur: '.$this->couleur);
		echo "<br />\n";
	}
	
	function Dump() {
        var_dump(get_object_vars($this));
    }

} // Class

?>