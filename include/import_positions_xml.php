<?php
// JF

// Importation des positions des bateaux depuis MP
// Les données de latitude et longitude sont en système décimal comme dans Google Maps. et Google Earth.

// Utilise la classe SimpleXML pour récupérer les données
// NON supporte par de nombreux serveurs
// require_once( "import_positions_simplexml.php" );

// Utilise le script xmlize.php. Peu souple mais supporté par tout serveur
// Si SimpleXML est supporté utiliser import_positions_simplexml.php car plus souple

require_once( "import_positions_xmlize.php" );


?>