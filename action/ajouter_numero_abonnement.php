<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function action_ajouter_numero_abonnement_dist($arg = null) {
	if (is_null($arg)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}
	
	@list($objet, $id_objet, $numero_debut) = explode('-', $arg);
	
	if ($objet == 'abonnement') {
		$id_abonnement = intval($id_objet);
		
		$abonnement = sql_fetsel('*', 'spip_abonnements', 'id_abonnement='.$id_abonnement);
		
		include_spip('inc/vabonnements_calculer_numeros_debut_fin');
		
		$numeros = vabonnements_calculer_numeros_debut_fin($abonnement['id_abonnements_offre'], $numero_debut);
		
		// Log
		include_spip('inc/vabonnements');
		
		$log_numeros = "Activation de l'abonnement par l'auteur n°".$abonnement['id_auteur'].". ";
		$log_numeros .= "L'abonnement débute avec le numéro ".$numero_debut." jusqu'au numéro ".$numeros['numero_fin'].".";
		$log = $abonnement['log'];
		$log .= vabonnements_log($log_numeros);
		
		// Activation de l'abonnement : statut payé -> actif
		// Suppression du coupon (code cadeau)
		// Ajout de $numero_debut, $date_debut, $numero_fin et $date_fin
		// Ajout des log.
		// Suppression d'une éventuelle relance
		
		include_spip('inc/autoriser');
		include_spip('action/editer_objet');
		
		// autoriser les modifications
		autoriser_exception('modifier', 'abonnement', $id_abonnement);
		autoriser_exception('instituer', 'abonnement', $id_abonnement);
		
		$erreur = objet_modifier('abonnement', $id_abonnement, array(
			'statut' => 'actif', 
			'numero_debut' => $numero_debut,
			'numero_fin' => $numeros['numero_fin'],
			'date_debut' => $numeros['date_debut'],
			'date_fin' => $numeros['date_fin'],
			'relance' => '',
			'coupon' => '',
			'log' => $log,
		));
		
		if ($erreur) {
			spip_log("L'Abonnement n°$id_abonnement n'a pas pu être activé. Message d'erreur : " . $erreur, 'vabonnements_activer'._LOG_ERREUR);
		} else {
			spip_log("L'Abonnement n°$id_abonnement est activé", 'vabonnements_activer'._LOG_INFO_IMPORTANTE);
		}
		
		// lever les autorisations
		autoriser_exception('instituer', 'abonnement', $id_abonnement, false);
		autoriser_exception('modifier', 'abonnement', $id_abonnement, false);
		
		// 
		// Notifications vers l'abonné et vers Vacarme
		// 
		$notifications = charger_fonction('notifications', 'inc');
		$notifications('abonnement_client_confirmation_activation', $id_abonnement);
		$notifications('abonnement_vendeur_confirmation_activation', $id_abonnement);
	}
}
