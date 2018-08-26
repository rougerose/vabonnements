<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

include_spip('inc/vabonnements');


function formulaires_souscrire_abonnement_charger_dist() {
	$valeurs = array();
	$valeurs['localisation'] = (_request('localisation')) ? _request('localisation') : '';
	$valeurs['duree'] = (_request('duree')) ? _request('duree') : '';
	$valeurs['id_abonnements_offre'] = (_request('id_abonnements_offre')) ? _request('id_abonnements_offre') : '';
	$valeurs['numero_debut'] = (_request('numero_debut')) ? _request('numero_debut') : '';
	$valeurs['cadeau'] = (_request('cadeau')) ? _request('cadeau') : '';
	$valeurs['id_rubrique'] = _request('id_rubrique');
	$valeurs['soutien_montant'] = (_request('soutien_montant')) ? _request('soutien_montant') : '';
	return $valeurs;
}

function formulaires_souscrire_abonnement_verifier_dist() {
	$erreurs = array();
	
	$obligatoires = array('localisation', 'duree', 'id_abonnements_offre', 'numero_debut', 'cadeau');
	
	foreach ($obligatoires as $obligatoire) {
		if (!strlen(_request($obligatoire))) {
			$erreurs['fs'.$obligatoire] = _T('abonnement:erreur_' . $obligatoire . '_obligatoire');
		}
	}
	
	$id_abonnements_offre = intval(_request('id_abonnements_offre'));
	$erreur_montant_soutien = vabonnements_verifier_montant_soutien($id_abonnements_offre);
	
	if ($erreur_montant_soutien) {
		$erreurs['fsid_abonnements_offre'] = $erreur_montant_soutien;
	}
	
	return $erreurs;
}

function formulaires_souscrire_abonnement_traiter_dist() {
	$res = array();
	$id_abonnements_offre = intval(_request('id_abonnements_offre'));
	
	// Options du panier 
	$options = array();
	$options['numero_debut'] = _request('numero_debut');
	$options['cadeau'] =_request('cadeau');
	$options['prix_souscripteur'] = '';
	
	// les offres d'abonnement de soutien disponibles.
	$offres_soutien = vabonnements_recuperer_offres_soutien();
	
	if (in_array($id_abonnements_offre, $offres_soutien)) {
		$inputs_soutien_montant = _request('soutien_montant');
		$prix_souscripteur = $inputs_soutien_montant[$id_abonnements_offre];
		
		if ($prix_souscripteur) {
			$options['prix_souscripteur'] = $prix_souscripteur;
		}
	}
	
	$options = vpaniers_options_produire_options($options);
	
	$objet = 'abonnements_offre';
	$quantite = 1;
	$negatif = '';
	$ajouter = charger_fonction('remplir_panier', 'action');
	$ajouter("$objet-$id_abonnements_offre-$quantite-$negatif-$options");
	
	// if (dans_panier($id_abonnements_offre, $objet)) {
	// 	$res['message_ok'] = 'Abonnement ajouté';
	// }
	$res['message_ok'] = 'Abonnement ajouté';
	// $res['redirect'] = parametre_url($redirect, 'a', 'panier');
	$res['panier'] = 'oui';
	$res['editable'] = true;
	
	return $res;
}
