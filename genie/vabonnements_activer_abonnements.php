<?php 

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

/**
 * Activer les abonnements qui débutent aujourd'hui.
 * 
 * @return [type] [description]
 */
function genie_vabonnements_activer_abonnements($time) {
	
	$lister_activations = charger_fonction('lister_activations', 'abonnements');
	$abonnements = $lister_activations(time());
	
	spip_log(count($abonnements) . " abonnement(s) à activer", 'vabonnements_activer'._LOG_INFO_IMPORTANTE);
	
	if (count($abonnements)) {
		include_spip('inc/autoriser');
		include_spip('action/editer_objet');
		include_spip('inc/vabonnements');
		$notifications = charger_fonction("notifications", "inc");
		
		foreach ($abonnements as $id_abonnement => $abo) {
			autoriser_exception('modifier', 'abonnement', $id_abonnement);
			autoriser_exception('instituer', 'abonnement', $id_abonnement);
			
			$log_activation = "Activation automatique de l'abonnement";
			$log = $abo['log'];
			$log .= vabonnements_log($log_activation);
			
			$erreur = objet_modifier('abonnement', $id_abonnement, array('statut' => 'actif', 'log' => $log));
			
			spip_log("Abonnement n°$id_abonnement est activé", 'vabonnements_activer'._LOG_INFO_IMPORTANTE);
			
			autoriser_exception('instituer', 'abonnement', $id_abonnement, false);
			autoriser_exception('modifier', 'abonnement', $id_abonnement, false);
			
			if (!$erreur) {
				$notifications('abonnement_activation', $id_abonnement, $abo['id_auteur']);
			}
		}
	}
	
	return 1;
}
