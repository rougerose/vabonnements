<?php


if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function notifications_abonnement_inviter_tiers_dist($quoi, $id_auteur, $options) {
	$email = sql_getfetsel('email', 'spip_auteurs', 'id_auteur='.intval($id_auteur));
	
	if ($email) {
		$texte = recuperer_fond('notifications/abonnement_inviter_tiers', array(
			'id_auteur' => $id_auteur,
			'id_payeur' => $options['id_payeur'],
			'id_abonnement' => $options['id_abonnement'],
			'id_message' => $options['id_message']
		));
		
		notifications_envoyer_mails($email, $texte);
	}
}
