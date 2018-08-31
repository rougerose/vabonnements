<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

include_spip('inc/vabonnements');

function formulaires_offrir_abonnement_charger_dist() {
	$valeurs = array();
	// 1
	$valeurs['localisation'] = (_request('localisation')) ? _request('localisation') : '';
	$valeurs['duree'] = (_request('duree')) ? _request('duree') : '';
	$valeurs['id_abonnements_offre'] = (_request('id_abonnements_offre')) ? _request('id_abonnements_offre') : '';
	$valeurs['numero_debut'] = (_request('numero_debut')) ? _request('numero_debut') : '';
	$valeurs['cadeau'] = (_request('cadeau')) ? _request('cadeau') : '';
	$valeurs['id_rubrique'] = _request('id_rubrique');
	$valeurs['soutien_montant'] = (_request('soutien_montant')) ? _request('soutien_montant') : '';
	
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
	$valeurs['message'] = (_request('message')) ? _request('message') : '';
	$valeurs['date_message'] = (_request('date_message')) ? _request('date_message') : '';
	
	$valeurs['_etapes'] = 2;
	
	return $valeurs;
}



function formulaires_offrir_abonnement_verifier_1_dist() {
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
	
	$obligatoires = array('civilite', 'prenom', 'voie', 'code_postal', 'ville', 'pays', 'date_message');
	
	foreach ($obligatoires as $obligatoire) {
		if (!strlen(_request($obligatoire))) {
			$erreurs[$obligatoire] = _T('vprofils:erreur_' . $obligatoire . '_obligatoire');
		}
	}
	
	return $erreurs;
}



function formulaires_offrir_abonnement_traiter_dist() {
	$res = array();
	$ajouter_panier = true;
	
	$id_abonnements_offre = _request('id_abonnements_offre');
	
	// Options du panier 
	$options = array();
	$options[0]['numero_debut'] = _request('numero_debut');
	$options[0]['cadeau'] =_request('cadeau');
	$options[0]['prix_souscripteur'] = '';
	
	// un abonnement de soutien peut avoir un prix modifié par le souscripteur
	$offres_soutien = vabonnements_recuperer_offres_soutien();
	
	if (in_array($id_abonnements_offre, $offres_soutien)) {
		$inputs_soutien_montant = _request('soutien_montant');
		$prix_souscripteur = $inputs_soutien_montant[$id_abonnements_offre];
		
		if ($prix_souscripteur) {
			$options[0]['prix_souscripteur'] = $prix_souscripteur;
		}
	}
	
	$options[0]['civilite'] = _request('civilite');
	$options[0]['nom_inscription'] = _request('nom_inscription');
	$options[0]['prenom'] = _request('prenom');
	$options[0]['mail_inscription'] = _request('mail_inscription');
	$options[0]['organisation'] = _request('organisation');
	$options[0]['service'] = _request('service');
	$options[0]['voie'] = _request('voie');
	$options[0]['complement'] = _request('complement');
	$options[0]['boite_postale'] = _request('boite_postale');
	$options[0]['code_postal'] = _request('code_postal');
	$options[0]['ville'] = _request('ville');
	$options[0]['region'] = _request('region');
	$options[0]['pays'] = _request('pays');
	$options[0]['message'] = _request('message');
	$options[0]['date_message'] = _request('date_message');
	
	
	// Vérifier si le bénéficiaire n'est pas déjà connu
	$auteur = sql_fetsel('id_auteur, nom', 'spip_auteurs', 'email='.sql_quote($options[0]['mail_inscription']));
	
	if ($auteur) {
		// Comparer les nom et prénom avec suppression des accents et diacritiques
		$nom_base = strtolower(vprofils_supprimer_accents(nom($auteur['nom'])));
		$prenom_base = strtolower(vprofils_supprimer_accents(prenom($auteur['nom'])));
		$nom_saisie = strtolower(vprofils_supprimer_accents($options[0]['nom_inscription']));
		$prenom_saisie = strtolower(vprofils_supprimer_accents($options[0]['prenom']));
		
		// Si des différences existent au niveau du nom ou du prénom,
		// on refuse de poursuivre.
		if (strcasecmp($nom_base, $nom_saisie) != 0 or strcasecmp($prenom_base, $prenom_saisie) != 0) {
			$ajouter_panier = false;
			// 
			// NOTE: On envoie un mail vers Vacarme avec les données comparées
			// afin d'avoir une trace du problème si le souscripteur 
			// nous contacte comme on l'invite dans le message d'erreur.
			// 
			$datas = array(
				'nom' => $options[0]['nom_inscription'],
				'prenom' => $options[0]['prenom'],
				'email' => $options[0]['mail_inscription'],
				'cmp_nom' =>  $nom_base.' / '.$nom_saisie,
				'cmp_prenom' => $prenom_base.' / '.$prenom_saisie
			);
			
			$notifications = charger_fonction('notifications', 'inc');
			$notifications('abonnement_vendeur_erreur_beneficaire', $auteur['id_auteur'], $datas);
			
			// 
			// Afficher le message d'erreur et d'invitation à nous contacter
			// 
			$res['message_erreur'] = _T('abonnement:message_erreur_auteur_deja_enregistre_saisie_incoherente', array('urlcontact' => generer_url_public('contact')));
		}
	}
	
	// 
	// Tout va bien : soit l'auteur existe mais les données sont cohérentes, 
	// soit il n'existe pas. 
	// On continue.
	// 
	if (!$auteur or $ajouter_panier) {
		// Les options d'abonnement pour le panier
		$options = vpaniers_options_produire_options($options);
		
		// ajouter au panier
		$objet = 'abonnements_offre';
		$quantite = 1;
		$negatif = '';
		$ajouter = charger_fonction('remplir_panier', 'action');
		$ajouter("$objet-$id_abonnements_offre-$quantite-$negatif-$options");
		
		if (dans_panier($id_abonnements_offre, $objet)) {
			$res['message_ok'] = _T('abonnement:message_ok_abonnement_dans_panier');
			$res['editable'] = false;
		} else {
			$res['message_erreur'] = _T('abonnement:message_erreur_abonnement_dans_panier');
			$res['editable'] = true;
		}
	}
	return $res;
}
