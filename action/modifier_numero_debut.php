<?php


if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function action_modifier_numero_debut_dist($arg = null) {
	if (is_null($arg)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}
	
	@list($objet, $id_objet, $numero_debut) = explode('/', $arg);
	
	include_spip('inc/autoriser');
	if (autoriser('abonner', '', 0) 
		and $id_abonnement = intval($id_objet) 
		and $numero_debut) {
		
		include_spip('inc/vabonnements');
		
		$abonnement = sql_fetsel('*', 'spip_abonnements', 'id_abonnement='.$id_abonnement);
		
		// 
		// Si $numero_debut est identique à $abonnement['numero_debut'],
		// c'est une confirmation.
		// 
		// Dans le cas contraire, il faut modifier les champs 
		// numero_debut, numero_fin, date_debut et date_fin.
		// 
		// Dans les deux cas, l'abonnement est activé.
		// 
		if ($numero_debut == $abonnement['numero_debut']) {
			
			// 
			// Log
			// 
			$log_numeros = "Activation de l'abonnement par son bénéficiaire (auteur n° ".$abonnement['id_auteur']."). ";
			$log_numeros .= "Le premier numéro a été confirmé par l'abonné : son abonnement débute avec le numéro ".$numero_debut." jusqu'au numéro ".$abonnement['numero_fin'].".";
			$log = $abonnement['log'];
			$log .= vabonnements_log($log_numeros);
			
			$set = array(
				'statut' => 'actif',
				'coupon' => '',
				'relance' => '',
				'log' => $log,
			);
			
		} else {
			// Date_debut et date_fin d'abonnement
			include_spip('inc/vabonnements_calculer_date');
			$dates_abonnement = vabonnements_calculer_dates($abonnement['id_abonnements_offre'], $numero_debut);
			
			// Numero_fin
			include_spip('inc/vnumeros');
			$duree_nbre = intval($abonnement['duree_echeance']);
			$numero_fin = vnumeros_calculer_reference_numero_futur($duree_nbre, $numero_debut);
			
			// 
			// Log
			// 
			$log_numeros = "Activation de l'abonnement par son bénéficiaire (auteur n° ".$abonnement['id_auteur']."). ";
			$log_numeros .= "Le premier numéro a été modifié par l'abonné : son abonnement débute avec le numéro $numero_debut jusqu'au numéro $numero_fin.";
			$log = $abonnement['log'];
			$log .= vabonnements_log($log_numeros);
			
			$set = array(
				'numero_debut' => $numero_debut,
				'numero_fin' => $numero_fin,
				'date_debut' => reset($dates_abonnement),
				'date_fin' => end($dates_abonnement),
				'statut' => 'actif',
				'coupon' => '',
				// le champs relance devient vide, 
				// pour être identifié correctement lors des relances à l'échéance.
				'relance' => '',
				'log' => $log,
			);
		}
		
		// 
		// Modifier l'abonnement
		// 
		include_spip('action/editer_objet');
		autoriser_exception('modifier', 'abonnement', $id_abonnement);
		autoriser_exception('instituer', 'abonnement', $id_abonnement);
		
		objet_modifier('abonnement', $id_abonnement, $set);
		
		autoriser_exception('modifier', 'abonnement', $id_abonnement, false);
		autoriser_exception('instituer', 'abonnement', $id_abonnement, false);
		
		// 
		// Noter les envois à faire
		// 
		$noter_envoi = charger_fonction('noter_envoi', 'action');
		$noter_envoi(
			$abonnement['id_commande'],
			'abonnements_offre',
			$abonnement['id_abonnements_offre']
		);
		
		// 
		// Notifications vers l'abonné et vers Vacarme
		// 
		$options = array('id_auteur' => $abonnement['id_auteur']);
		$notifications = charger_fonction('notifications', 'inc');
		$notifications('abonnement_client_confirmation_activation', $id_abonnement, $options);
		$notifications('abonnement_vendeur_confirmation_activation', $id_abonnement, $options);
	}
}
