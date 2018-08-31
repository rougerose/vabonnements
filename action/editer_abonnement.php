<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function abonnement_inserer($id_parent = null, $champs = array()) {
	include_spip('inc/autoriser');
	
	// L'auteur doit être explicite
	$id_auteur = 0;
	if (isset($champs['id_auteur'])) {
		$id_auteur = intval($champs['id_auteur']);
	}
	
	if (autoriser('abonner', '', 0, $id_auteur)) {
		
		// La date de création
		$champs['date'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
		
		// Le statut par défaut
		if (!isset($champs['statut'])) {
			$champs['statut'] = 'prepa';
		}
		
		// 
		// Déléguer à une fonction spécifique l'ajout des informations nécessaires
		// pour un abonnement personnel et pour un abonnement offert.
		// 
		// Dans ce dernier cas, toutes les informations relatives au bénéficiaire
		// sont présentes également.
		// 
		// Pour les infos communes aux deux types d'abonnements, le principe 
		// est que les champs minimum obligatoires existent déjà dans $champs : 
		// id_abonnements_offre, id_auteur, id_commande, numero_debut.
		// Les champs duree_echeance et prix_echeance sont déduits de l'offre d'abonnement.
		// 
		$abonnements_completer = charger_fonction('completer', 'abonnements');
		$champs_complets = $abonnements_completer($champs);
		
		// Vérifier et insérer
		if ($champs_complets) {
			
			// 
			// Ne retenir que les informations nécessaires à la création
			// de l'abonnement (à l'exclusion des infos relatives au bénéficiaire).
			// 
			$champs_exclus = array_flip(array('civilite', 'nom_inscription', 'prenom', 'mail_inscription', 'organisation', 'service', 'voie', 'complement', 'boite_postale', 'code_postal', 'ville', 'region', 'pays'));
			$champs_abonnement = array_diff_key($champs_complets, $champs_exclus);
		
			
			// Pipeline pre_insertion
			$champs_abonnement = pipeline('pre_insertion', 
				array(
					'args' => array('table' => 'spip_abonnements'),
					'data' => $champs_abonnement
				)
			);
			
			$id_abonnement = sql_insertq('spip_abonnements', $champs_abonnement);
			
			// Pipeline post_insertion
			pipeline('post_insertion',
				array(
					'args' => array(
						'table' => 'spip_abonnements',
						'id_objet' => $id_abonnement
					),
					'data' => $champs_abonnement
				)
			);
			
			return $id_abonnement;
		}
	}
	
	return '';
}


// TODO: ajouter fonctions abonnement_modifier et abonnement_instituer
