<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

function formulaires_valider_code_cadeau_charger_dist($retour = '') {
	$valeurs = array();
	$valeurs['code_cadeau'] = (_request('code')) ? _request('code') : '';
	$valeurs['offrir'] = (_request('offrir')) ? _request('offrir') : '';
	return $valeurs;
}


function formulaires_valider_code_cadeau_verifier_dist($retour = '') {
	include_spip('inc/vabonnements_code');
	$erreurs = array();
	
	$code_cadeau = _request('code_cadeau');
	if (!$code_cadeau) {
		$erreurs['code_cadeau'] = _T('abonnement:erreur_code_cadeau_obligatoire');
	}
	
	$offrir = _request('offrir');
	if (!$offrir) {
		$erreurs['message_erreur'] = _T('abonnement:message_erreur_valider_code_cadeau');
	}
	
	return $erreurs;
}


function formulaires_valider_code_cadeau_traiter_dist($retour = '') {
	$res = array();
	include_spip('inc/vabonnements_code');
	
	$code_cadeau = _request('code_cadeau');
	$offrir = _request('offrir');
	
	if (
		is_string($code_cadeau)
		&& $code_cadeau  
		&& $abonnement = sql_fetsel('*', 'spip_abonnements', 'coupon='.sql_quote($code_cadeau))
	) {
		$code = vabonnements_lire_code($code_cadeau);
		
		$date_commande = sql_getfetsel('date', 'spip_commandes', 'id_commande='.intval($abonnement['id_commande']));
		$date_commande = date('Y-n-j', strtotime($date_commande));
		
		// 
		// Vérifier le code et sa cohérence
		// 
		if (
			$code 
			&& is_array($code)
			&& $code['id'] == $abonnement['id_auteur']
			&& $date_commande == $code['date']
			&& $offrir == 'abonnement'
		) {
			// 
			// L'abonné est identifié automatiquement
			// 
			include_spip('inc/auth');
			$auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur='.intval($abonnement['id_auteur']));
			auth_loger($auteur);
			
			$res['message_ok'] = _T('abonnement:message_succes_valider_code_cadeau');
			
			if ($retour) {
				$hash = vabonnements_calcul_hash_abonnement($abonnement['id_auteur'], $abonnement['id_abonnement'], $abonnement['date']);
				$res['redirect'] = parametre_url($retour, 'cadeau_hash', $hash);
			}
			
			return $res;
		}
		
		
	}
	
	$res['message_erreur'] = _T('abonnement:message_echec_valider_code_cadeau');
	return $res;
}
