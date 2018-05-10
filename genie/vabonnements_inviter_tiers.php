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
	
	$date_jour = date('Y-m-d H:i:s', $timestamp);
	$relances = vabonnements_get_relances();
	$premiere_relance = reset($relances);
	
	// 
	// Prendre les abonnements offerts pour lesquels le mail d'invitation
	// doit partir aujourd'hui. 
	// 
	$abonnements = sql_allfetsel(
		'*', 
		'spip_abonnements',
		'statut=' . sql_quote('paye')
		.' AND coupon <> ' . sql_quote('')
		.' AND relance=' . sql_quote('')
		.' AND date_debut<=' . sql_quote($date_jour)
	);
	
	if (count($abonnements)) {
		$notifications = charger_fonction('notifications', 'inc');
		include_spip('inc/vabonnements');
		include_spip('inc/autoriser');
		include_spip('action/editer_objet');
		
		foreach ($abonnements as $abonnement) {
			$id_auteur = intval($abonnement['id_auteur']);
			$id_payeur = sql_getfetsel('id_auteur', 'spip_commandes', 'id_commande=' . intval($abonnement['id_commande']));
			$id_abonnement = intval($abonnement['id_abonnement']);
			$id_message = sql_getfetsel(
				'id_message', 
				'spip_messages', 
				'id_auteur=' . intval($id_payeur)
				. ' AND destinataires=' . intval($abonnement['id_auteur'])
				. ' AND statut=' . sql_quote('prepa')
				. ' AND type=' . sql_quote('kdo')
			);
			
			autoriser_exception('modifier', 'abonnement', $id_abonnement);
			
			$log_invitation = "Envoi du message d'invitation à activer l'abonnement.";
			$log = $abonnement['log'];
			$log .= vabonnements_log($log_invitation);
			
			spip_log("genie_abonnements_inviter_tiers id_abonnement = ".$id_abonnement, 'vabonnements_inviter_tiers'._LOG_INFO_IMPORTANTE);
			
			// Ajouter log et marquer en première relance dans X jours (selon config)
			$erreur = objet_modifier('abonnement', $id_abonnement, array('log' => $log, 'relance' => $premiere_relance));
			
			if ($erreur) {
				spip_log("genie_abonnements_inviter_tiers abonnement #$id_abonnement. Message d'erreur à l'enregistrement de la première relance : ".$erreur, 'vabonnements_inviter_tiers'._LOG_ERREUR);
			}
			
			autoriser_exception('modifier', 'abonnement', $id_abonnement, false);
			
			$options = array(
				'id_payeur' => $id_payeur,
				'id_abonnement' => $id_abonnement,
				'id_message' => ($id_message) ? $id_message : ''
			);
			
			$notifications('abonnement_inviter_tiers', $id_auteur, $options);
		}
		return 1;
	}
	// rien à faire
	return 0;
}
