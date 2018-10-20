<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function notifications_abonnement_relancer_echeance_dist($quoi, $id_auteur, $options) {
	$email = sql_getfetsel('email', 'spip_auteurs', 'id_auteur='.intval($id_auteur));
	
	if ($email) {
		$texte = recuperer_fond('notifications/abonnement_relancer_echeance', array(
			'id_auteur' => $id_auteur,
			'id_abonnement' => $options['id_abonnement']
		));
		
		notifications_envoyer_mails($email, $texte);
	}
}
