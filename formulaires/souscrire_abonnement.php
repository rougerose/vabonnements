<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function formulaires_souscrire_abonnement_charger_dist() {
	$valeurs = array();
	$valeurs['localisation'] = (_request('localisation')) ? _request('localisation') : '';
	$valeurs['duree'] = (_request('duree')) ? _request('duree') : '';
	$valeurs['abonnements_offre'] = (_request('abonnements_offre')) ? _request('abonnements_offre') : '';
	$valeurs['numero_debut'] = (_request('numero_debut')) ? _request('numero_debut') : '';
	$valeurs['cadeau'] = (_request('cadeau')) ? _request('cadeau') : '';
	$valeurs['id_rubrique'] = _request('id_rubrique');

	return $valeurs;
}

function formulaires_souscrire_abonnement_verifier_dist() {
	$erreurs = array();
	
	$obligatoires = array('localisation', 'duree', 'abonnements_offre', 'numero_debut', 'cadeau');
	
	foreach ($obligatoires as $obligatoire) {
		if (!strlen(_request($obligatoire))) {
			$erreurs['fs'.$obligatoire] = _T('abonnement:erreur_' . $obligatoire . '_obligatoire');
		}
	}
	
	return $erreurs;
}

function formulaires_souscrire_abonnement_traiter_dist() {
	$res = array();
	
	// $id_abonnements_offre = _request('abonnements_offre');
	// 
	// $remplir_panier = charger_fonction('remplir_panier', 'action');
	// 
	// $remplir_panier("abonnements_offre-$id_abonnements_offre-1");

	return $res;
}
