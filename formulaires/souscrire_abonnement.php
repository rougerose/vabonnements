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
	
	if ($err = vabonnements_verifier_montant_soutien($id_abonnements_offre)) {
		$erreurs['fsid_abonnements_offre'] = $err;
	}
	
	return $erreurs;
}

function formulaires_souscrire_abonnement_traiter_dist() {
	$res = array();
	$id_abonnements_offre = intval(_request('id_abonnements_offre'));
	
	// Options du panier 
	$options = array();
	$options[0]['numero_debut'] = _request('numero_debut');
	$options[0]['cadeau'] =_request('cadeau');
	$options[0]['prix_souscripteur'] = '';
	// Les options doivent contenir toutes les données possibles.
	$options[0]['civilite'] = '';
	$options[0]['nom_inscription'] = '';
	$options[0]['prenom'] = '';
	$options[0]['mail_inscription'] = '';
	$options[0]['organisation'] = '';
	$options[0]['service'] = '';
	$options[0]['voie'] = '';
	$options[0]['complement'] = '';
	$options[0]['boite_postale'] = '';
	$options[0]['code_postal'] = '';
	$options[0]['ville'] = '';
	$options[0]['region'] = '';
	$options[0]['pays'] = '';
	$options[0]['texte_message'] = '';
	$options[0]['date_message'] = '';
	
	// les offres d'abonnement de soutien disponibles.
	$offres_soutien = vabonnements_recuperer_offres_soutien();
	
	if (in_array($id_abonnements_offre, $offres_soutien)) {
		$inputs_soutien_montant = _request('soutien_montant');
		$prix_souscripteur = $inputs_soutien_montant[$id_abonnements_offre];
		
		if ($prix_souscripteur) {
			$options[0]['prix_souscripteur'] = $prix_souscripteur;
		}
	}
	
	$options = vpaniers_options_produire_options($options);
	
	$objet = 'abonnements_offre';
	$quantite = 1;
	$negatif = '';
	$ajouter = charger_fonction('remplir_panier', 'action');
	$ajouter("$objet/$id_abonnements_offre/$quantite/$negatif/$options");
	
	if (dans_panier($id_abonnements_offre, $objet)) {
		$res['message_ok'] = _T('abonnement:message_ok_abonnement_dans_panier');
	} else {
		$res['message_erreur'] = _T('abonnement:message_erreur_abonnement_dans_panier');
	}
	
	$res['editable'] = true;
	
	return $res;
}
