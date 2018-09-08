<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function notifications_abonnement_client_confirmation_activation_dist($quoi, $id_abonnement, $options) {
	$email = sql_getfetsel('email', 'spip_auteurs', 'id_auteur='.intval($options['id_auteur']));
	
	if ($email) {
		$texte = recuperer_fond('notifications/abonnement_client_confirmation_activation', array(
			'id_abonnement' => $id_abonnement,
			'id_auteur' => $options['id_auteur'],
		));
		notifications_envoyer_mails($email, $texte);
	}
}
