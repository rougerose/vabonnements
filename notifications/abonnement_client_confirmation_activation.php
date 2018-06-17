<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function notifications_abonnement_client_confirmation_activation_dist($quoi, $id_abonnement) {
	$id_auteur = sql_getfetsel('id_auteur', 'spip_abonnements', 'id_abonnement='.intval($id_abonnement));
	$email = sql_getfetsel('email', 'spip_auteurs', 'id_auteur='.intval($id_auteur));
	
	if ($email) {
		$texte = recuperer_fond('notifications/abonnement_client_confirmation_activation', array('id_abonnement' => $id_abonnement));
		notifications_envoyer_mails($email, $texte);
	}
}
