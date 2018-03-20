<?php

if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}

/**
 * Abonner un auteur
 * @param  int $id_abonnements_offre
 * @param  array  $options
 * 		int 	id_auteur
 * 		string 	statut
 * 		int 	id_commande
 * 		string 	prix_ht_initial
 * 		string 	date
 * 		string 	date_debut
 * 		string 	date_fin
 * 		string 	numero_debut
 * 		string 	numero_fin
 * 		string 	mode_paiement
 * @return int|bool  id_abonnement ou false
 */
function abonnements_abonner_dist($id_abonnements_offre, $options = array()) {
	include_spip('base/abstract_sql');
	$id_abonnement = 0;
	
	if ($row = sql_fetsel('*', 'spip_abonnements_offres', 'id_abonnements_offre='.$id_abonnements_offre)) {
		
		$defaut = array(
			'id_auteur' => 0, // TODO: prévoir workflow sans auteur ?
			'statut' => 'prepa',
			'id_commande' => 0, // TODO: prévoir workflow sans commande ?
			'prix_ht_initial' => null,
			'date' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
			'date_debut' => '',
			'date_fin' => '',
			'numero_debut' => '',
			'numero_fin' => '',
			'mode_paiement' => '',
			'log' => ''
		);
		
		$options = array_merge($defaut, $options);
		
		$prix_ht_initial = $options['prix_ht_initial'];
		if (is_null($prix_ht_initial)) {
			$prix_ht_initial = $row['prix_ht'];
		}
		
		$numero_debut = $options['numero_debut'];
		
		// Ajouter les données nécessaires à l'abonnement
		// 
		// date_debut
		// 
		// La date_debut correspond à la date de sortie du numéro (date_numero)
		$numero_depart = sql_fetsel('date_numero', 'spip_rubriques', 'reference='.sql_quote($numero_debut));
		
		include_spip('inc/vabonnements_calculer_date');
		
		if ($numero_depart) {
			// La date_numero est "normalisée" en début de saison.
			$date_debut = vabonnements_calculer_date_debut($numero_depart['date_numero']);
			
		} else {
			// Si la rubrique n'existe pas, l'abonnement débute 
			// avec le prochain numéro. La date de ce numéro est 
			// alors calculée à partir de la date du numéro en cours.
			$numero_encours = sql_fetsel(
				"date_numero", 
				"spip_rubriques", 
				"statut='publie' AND id_parent=115", 
				"", 
				"titre DESC"
			);
			$date_debut = filtre_calculer_numero_futur_date($numero_encours['date_numero']);
		}
		
		// date_fin
		$duree = explode(" ", $row['duree']); // 12 month ou 24 month
		$duree_valeur = reset($duree);
		$date_fin = vabonnements_calculer_date_fin($date_debut, $duree_valeur);
		
		// numero_fin
		// 
		// Nombre de numéros à servir pour cet abonnement (1 numéro par trimestre).
		// Total moins 1 car le rang utilisé pour le calcul de la référence démarre à zéro.
		$numeros_quantite = ($duree_valeur / 3) - 1;
		$numero_fin = filtre_calculer_numero_futur_reference($numero_debut, $numeros_quantite);
		
		$statut = $options['statut'];
		
		// Créer l'abonnement
		$ins = array(
			'id_abonnements_offre' => $id_abonnements_offre,
			'id_auteur' => $options['id_auteur'],
			'id_commande' => $options['id_commande'],
			'date' => $options['date'],
			'date_debut' => $date_debut,
			'date_fin' => $date_fin,
			'numero_debut' => $numero_debut,
			'numero_fin' => $numero_fin,
			'mode_paiement' => $options['mode_paiement'],
			// TODO: Ajouter colonne prix_echeance dans la table Abonnements
			// 'prix_echeance' => $options['prix_ht_initial'],
			'statut' => $statut,
			// 'log' => ''
		);
		
		$id_abonnement = sql_insertq('spip_abonnements', $ins);
		
		if (!$id_abonnement){
			spip_log("Impossible de creer l'abonnement en base " . var_export($ins, true), "vabonnements" . _LOG_ERREUR);
			return false;
		}
		
		if ($statut == 'actif') {
			// TODO: activer notifications
			//$notifications = charger_fonction("notifications", "inc");
			//$notifications('activerabonnement', $id_abonnement, array('statut' => $statut, 'statut_ancien' => 'prepa'));
		}
	}
	
	return $id_abonnement;
}
