<?php


if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function vabonnements_calculer_numeros_debut_fin($id_abonnements_offre, $numero_debut) {
	include_spip('inc/vabonnements_calculer_date');
	include_spip('vabonnements_fonctions');
	
	$duree = sql_getfetsel('duree', 'spip_abonnements_offres', 'id_abonnements_offre='.intval($id_abonnements_offre));
	
	// 
	// -----------
	// Date_debut
	// -----------
	// La date_debut correspond à la date de sortie du numéro (date_numero)
	// 
	$date_numero_depart = sql_getfetsel('date_numero', 'spip_rubriques', 'reference='.sql_quote($numero_debut));
	
	if ($date_numero_depart) {
		// 
		// La date_numero est "normalisée" en début de saison.
		// 
		$date_debut = vabonnements_calculer_date_debut($date_numero_depart);
		
	} else {
		// 
		// Si la rubrique n'existe pas, l'abonnement débute 
		// avec le prochain numéro. La date de ce numéro est 
		// alors calculée à partir de la date du numéro en cours.
		// 
		$numero_encours = sql_fetsel(
			"date_numero", 
			"spip_rubriques", 
			"statut='publie' AND id_parent=115", 
			"", 
			"titre DESC"
		);
		$date_debut = filtre_calculer_numero_futur_date($numero_encours['date_numero']);
	}
	
	// 
	// -----------
	// Date_fin
	// -----------
	// 
	$duree_ = explode(" ", $duree); // 12 month ou 24 month
	$duree_valeur = reset($duree_);
	$date_fin = vabonnements_calculer_date_fin($date_debut, $duree_valeur);
	
	// 
	// -----------
	// Numero_fin
	// -----------
	// Nombre de numéros à servir pour cet abonnement (1 numéro par trimestre).
	// Total moins 1 car le rang utilisé pour le calcul de la référence démarre à zéro.
	// 
	$numeros_quantite = ($duree_valeur / 3) - 1;
	
	include_spip('vabonnements_fonctions');
	$numero_fin = filtre_calculer_numero_futur_reference($numero_debut, $numeros_quantite);
	
	return array('date_debut' => $date_debut, 'date_fin' => $date_fin, 'numero_fin' => $numero_fin);
}
