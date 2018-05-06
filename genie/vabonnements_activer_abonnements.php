<?php 

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

/**
 * Activer les abonnements qui débutent à la date du jour.
 * 
 * @return [type] [description]
 */
function genie_vabonnements_activer_abonnements($time) {
	// TODO: Conserver le déport de la constitution de la liste dans une 
	// fonction externe ?
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
			
			//Activation de l'abonnement
			$erreur = objet_modifier('abonnement', $id_abonnement, array('statut' => 'actif'));
			
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
