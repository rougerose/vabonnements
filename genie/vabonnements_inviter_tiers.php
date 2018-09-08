<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


/**
 * Envoi de l'email d'invitation destiné au bénéficiaire d'un abonnement offert.
 * 
 * @param  int $timestamp
 * @return int
 */
function genie_vabonnements_inviter_tiers_dist($timestamp) {
	
	include_spip('inc/vabonnements_relance');
	
	$date_jour = date('Y-m-d H:i:s');
	
	// 
	// Prendre les abonnements offerts pour lesquels le mail d'invitation
	// doit partir aujourd'hui. 
	// 
	// Important : cette invitation ne concerne que les abonnement *payés*
	// 
	$abonnements = sql_allfetsel(
		'*', 
		'spip_abonnements',
		'statut=' . sql_quote('paye')
		.' AND offert='.sql_quote('oui')
		.' AND coupon <> ' . sql_quote('')
		.' AND relance=' . sql_quote('')
		.' AND date_message<=' . sql_quote($date_jour)
	);
	
	if (count($abonnements)) {
		$notifications = charger_fonction('notifications', 'inc');
		include_spip('inc/vabonnements');
		include_spip('inc/autoriser');
		include_spip('action/editer_objet');
		
		foreach ($abonnements as $abonnement) {
			$id_auteur = intval($abonnement['id_auteur']);
			$id_abonnement = intval($abonnement['id_abonnement']);
			
			autoriser_exception('modifier', 'abonnement', $id_abonnement);
			
			$log_invitation = "Envoi du message d'invitation au bénéficiaire.";
			$log = $abonnement['log'];
			$log .= vabonnements_log($log_invitation);
			
			spip_log("genie_abonnements_inviter_tiers id_abonnement = ".$id_abonnement, 'vabonnements_inviter_tiers'._LOG_INFO_IMPORTANTE);
			
			// Ajouter log et marquer les relances à 0 (= invitation envoyée)
			$erreur = objet_modifier(
				'abonnement', 
				$id_abonnement, 
				array('log' => $log, 'relance' => sql_quote('0'))
			);
			
			if ($erreur) {
				spip_log("genie_abonnements_inviter_tiers abonnement #$id_abonnement. Message d'erreur à l'enregistrement de la première relance : ".$erreur, 'vabonnements_inviter_tiers'._LOG_ERREUR);
			}
			
			autoriser_exception('modifier', 'abonnement', $id_abonnement, false);
			
			$options = array('id_abonnement' => $id_abonnement);
			
			$notifications('abonnement_inviter_tiers', $id_auteur, $options);
		}
		return 1;
	}
	// rien à faire
	return 0;
}
