<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function notifications_abonnement_vendeur_confirmation_activation_dist($quoi, $id_abonnement, $options) {
	include_spip('inc/config');
	$config = lire_config('vprofils');
	$emails = $config['vendeur'];
	$destinataires = explode(',', $emails);
	$destinataires = array_map('trim', $destinataires);
	
	if (count($destinataires)) {
		$texte = recuperer_fond(
			'notifications/abonnement_vendeur_confirmation_activation', 
			array('id_abonnement' => $id_abonnement, 'id_auteur' => $options['id_auteur']));
		notifications_envoyer_mails($destinataires, $texte);
	}
}
