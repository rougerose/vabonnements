<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function notifications_abonnement_activation_vacarme_dist($quoi, $id_abonnement) {
	include_spip('inc/config');
	$config = lire_config('commandes');
	$destinataires = $config['vendeur_'.$config['vendeur']];
	$destinataires = is_array($destinataires) ? $destinataires : array($destinataires);
	
	if (count($destinataires)) {
		$texte = recuperer_fond('notifications/abonnement_activation_vacarme', array('id_abonnement' => $id_abonnement));
		notifications_envoyer_mails($destinataires, $texte);
	}
}
