<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function formulaires_offrir_abonnement_charger_dist() {
	$valeurs = array();
	// 1
	$valeurs['localisation'] = (_request('localisation')) ? _request('localisation') : '';
	$valeurs['duree'] = (_request('duree')) ? _request('duree') : '';
	$valeurs['abonnements_offre'] = (_request('abonnements_offre')) ? _request('abonnements_offre') : '';
	$valeurs['numero_debut'] = (_request('numero_debut')) ? _request('numero_debut') : '';
	$valeurs['cadeau'] = (_request('cadeau')) ? _request('cadeau') : '';
	$valeurs['id_rubrique'] = _request('id_rubrique');
	
	// 2
	$valeurs['civilite'] = (_request('civilite')) ? _request('civilite') : '';
	$valeurs['nom_inscription'] = (_request('nom_inscription')) ? _request('nom_inscription') : '';
	$valeurs['prenom'] = (_request('prenom')) ? _request('prenom') : '';
	$valeurs['mail_inscription'] = (_request('mail_inscription')) ? _request('mail_inscription') : '';
	$valeurs['organisation'] = (_request('organisation')) ? _request('organisation') : '';
	$valeurs['service'] = (_request('service')) ? _request('service') : '';
	$valeurs['voie'] = (_request('voie')) ? _request('voie') : '';
	$valeurs['complement'] = (_request('complement')) ? _request('complement') : '';
	$valeurs['boite_postale'] = (_request('boite_postale')) ? _request('boite_postale') : '';
	$valeurs['code_postal'] = (_request('code_postal')) ? _request('code_postal') : '';
	$valeurs['ville'] = (_request('ville')) ? _request('ville') : '';
	$valeurs['region'] = (_request('region')) ? _request('region') : '';
	$valeurs['pays'] = (_request('pays')) ? _request('pays') : '';
	
	$valeurs['_etapes'] = 2;
	
	return $valeurs;
}

function formulaires_offrir_abonnement_verifier_1_dist() {
	$erreurs = array();
	
	$obligatoires = array('localisation', 'duree', 'abonnements_offre', 'numero_debut', 'cadeau');
	
	foreach ($obligatoires as $obligatoire) {
		if (!strlen(_request($obligatoire))) {
			$erreurs['fs'.$obligatoire] = _T('abonnement:erreur_' . $obligatoire . '_obligatoire');
		}
	}

	return $erreurs;
}

function formulaires_offrir_abonnement_verifier_2_dist() {
	$erreurs = array();
	
	include_spip('inc/editer');
	include_spip('inc/filtres');
	
	if (!$nom = _request('nom_inscription')) {
		$erreurs['nom_inscription'] = _T('info_obligatoire');
	} elseif (!nom_acceptable(_request('nom_inscription'))) {
		$erreurs['nom_inscription'] = _T('ecrire:info_nom_pas_conforme');
	}
	
	if (!$mail = strval(_request('mail_inscription'))) {
		$erreurs['mail_inscription'] = _T('info_obligatoire');
	}
	
	if (!_request('organisation') and _request('service')) {
		$erreurs['organisation'] = _T('vprofils:erreur_si_service_organisation_nonvide');
	}
	
	$obligatoires = array('civilite', '_id_abonnement', 'prenom', 'voie', 'code_postal', 'ville', 'pays');
	
	foreach ($obligatoires as $obligatoire) {
		if (!strlen(_request($obligatoire))) {
			$erreurs[$obligatoire] = _T('vprofils:erreur_' . $obligatoire . '_obligatoire');
		}
	}
	
	return $erreurs;
}

function formulaires_offrir_abonnement_traiter_dist() {
	$res = array();
	return $res;
}
