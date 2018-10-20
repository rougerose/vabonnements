<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function notifications_abonnement_vendeur_erreur_beneficaire_dist($quoi, $id_auteur, $options) {
	include_spip('inc/config');
	$emails = lire_config('vprofils/vendeur');
	$destinataires = explode(',', $emails);
	$destinataires = array_map('trim', $destinataires);
	
	if (count($destinataires)) {
		$texte = recuperer_fond(
			'notifications/abonnement_vendeur_erreur_beneficaire', 
			array('id_auteur' => $id_auteur, 'datas' => $options));
		notifications_envoyer_mails($destinataires, $texte);
	}
}
