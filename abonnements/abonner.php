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
 * 		string	duree
 * 		string	log
 * @return int|bool  id_abonnement ou false
 */
function abonnements_abonner_dist($id_abonnements_offre, $options = array()) {
	include_spip('base/abstract_sql');
	
	$id_abonnement = 0;
	
	if ($row = sql_fetsel('*', 'spip_abonnements_offres', 'id_abonnements_offre='.$id_abonnements_offre)) {
		
		$defaut = array(
			'id_auteur' => 0,
			'statut' => 'prop', // en commande. Le statut sera modifié lorsque le paiement sera effectif.
			'id_commande' => 0,
			'prix_ht_initial' => null,
			'taxe' => $row['taxe'],
			'date' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
			'date_debut' => '',
			'date_fin' => '',
			'numero_debut' => '',
			'numero_fin' => '',
			'duree' => '',
			'mode_paiement' => '',
			'log' => ''
		);
		
		$options = array_merge($defaut, $options);
		
		// 
		// Pas d'auteur ?
		// 
		if (!$options['id_auteur']) {
			spip_log("Impossible de créer l'abonnement en base : aucun auteur " . var_export($options, true), "vabonnements" . _LOG_ERREUR);
			return false;
		}
		
		$statut = 'prop';
		
		$prix_ht_initial = $options['prix_ht_initial'];
		if (is_null($prix_ht_initial)) {
			$prix_ht_initial = $row['prix_ht'];
		}
		
		$numero_debut = $options['numero_debut'];
		
		// 
		// Ajouter les données nécessaires à l'abonnement
		// 
		
		include_spip('inc/vabonnements_calculer_numeros_debut_fin');
		
		$numeros = vabonnements_calculer_numeros_debut_fin($id_abonnements_offre, $numero_debut);
		
		$duree = $row['duree'];
		$numero_fin = $numeros['numero_fin'];
		
		// 
		// Log
		// 
		include_spip('inc/vabonnements');
		
		$titre_offre = supprimer_numero($row['titre']);
		$duree_en_clair = filtre_duree_en_clair($duree);
		$prix_en_clair = prix_formater($prix_ht_initial + ($prix_ht_initial * $options['taxe']));
		$id_commande = $options['id_commande'];
		$commande_en_clair = "Commande n°$id_commande : "; 
		
		// Exemple -> Commande n° XX : souscription abonnement 2 ans, offre Tarif réduit, (prix TTC), du numéro xx au numéro yy.
		$log_abos = $commande_en_clair."ajout de l'abonnement $duree_en_clair, offre $titre_offre (prix $prix_en_clair), ";
		$log_abos .= "du numéro $numero_debut au numéro $numero_fin. ";
		$log_abos .= "Mode de paiement : ".$options['mode_paiement'].".";
		$log = vabonnements_log($log_abos);
		
		// 
		// Créer l'abonnement
		// 
		$ins = array(
			'id_abonnements_offre' => $id_abonnements_offre,
			'id_auteur' => $options['id_auteur'],
			'id_commande' => $options['id_commande'],
			'date' => $options['date'],
			'date_debut' => $numeros['date_debut'],
			'date_fin' => $numeros['date_fin'],
			'numero_debut' => $numero_debut,
			'numero_fin' => $numero_fin,
			'mode_paiement' => $options['mode_paiement'],
			'prix_echeance' => $prix_ht_initial,
			'duree_echeance' => $duree,
			'statut' => $statut,
			'log' => $log
		);
		
		// TODO: utiliser plutôt l'API de SPIP pour l'ajout de l'abonnement ?
		// cela permettrait notamment d'envoyer les données aux pipelines standard.
		$id_abonnement = sql_insertq('spip_abonnements', $ins);
		
		if (!$id_abonnement){
			spip_log("Impossible de creer l'abonnement en base " . var_export($ins, true), "vabonnements" . _LOG_ERREUR);
			return false;
		}
		
		//if ($statut == 'actif') {
			// TODO: activer notifications ?
			//$notifications = charger_fonction("notifications", "inc");
			//$notifications('activerabonnement', $id_abonnement, array('statut' => $statut, 'statut_ancien' => 'prepa'));
		//}
	}
	
	return $id_abonnement;
}
