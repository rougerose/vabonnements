<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


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
	$valeurs['texte_message'] = (_request('texte_message')) ? _request('texte_message') : '';
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
	$erreur_montant_soutien = vabonnements_verifier_montant_soutien($id_abonnements_offre);
	
	if ($erreur_montant_soutien) {
		$erreurs['fsid_abonnements_offre'] = $erreur_montant_soutien;
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
	$options['numero_debut'] = _request('numero_debut');
	$options['cadeau'] =_request('cadeau');
	$options['prix_souscripteur'] = '';
	
	// un abonnement de soutien peut avoir un prix modifié par le souscripteur
	$offres_soutien = vabonnements_recuperer_offres_soutien();
	
	if (in_array($id_abonnements_offre, $offres_soutien)) {
		$inputs_soutien_montant = _request('soutien_montant');
		$prix_souscripteur = $inputs_soutien_montant[$id_abonnements_offre];
		
		if ($prix_souscripteur) {
			$options['prix_souscripteur'] = $prix_souscripteur;
		}
	}
	
	
	$beneficiaire = array();
	$beneficiaire['civilite'] = _request('civilite');
	$beneficiaire['nom_inscription'] = _request('nom_inscription');
	$beneficiaire['prenom'] = _request('prenom');
	$beneficiaire['mail_inscription'] = _request('mail_inscription');
	$beneficiaire['organisation'] = _request('organisation');
	$beneficiaire['service'] = _request('service');
	$beneficiaire['voie'] = _request('voie');
	$beneficiaire['complement'] = _request('complement');
	$beneficiaire['boite_postale'] = _request('boite_postale');
	$beneficiaire['code_postal'] = _request('code_postal');
	$beneficiaire['ville'] = _request('ville');
	$beneficiaire['region'] = _request('region');
	$beneficiaire['pays'] = _request('pays');
	$beneficiaire['pays'] = _request('pays');
	$beneficiaire['texte_message'] = _request('texte_message');
	$beneficiaire['date_message'] = _request('date_message');
	
	$options['beneficiaire'] = $beneficiaire;
	
	// Vérifier si le bénéficiaire n'est pas déjà connu
	$auteur = sql_fetsel('id_auteur, nom', 'spip_auteurs', 'email='.sql_quote($beneficiaire['mail_inscription']));
	
	if ($auteur) {
		// Comparer les nom et prénom avec suppression des accents et diacritiques
		$nom_base = strtolower(vprofils_supprimer_accents(nom($auteur['nom'])));
		$prenom_base = strtolower(vprofils_supprimer_accents(prenom($auteur['nom'])));
		$nom_saisie = strtolower(vprofils_supprimer_accents($beneficiaire['nom_inscription']));
		$prenom_saisie = strtolower(vprofils_supprimer_accents($beneficiaire['prenom']));
		
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
				'nom' => $beneficiaire['nom_inscription'],
				'prenom' => $beneficiaire['prenom'],
				'email' => $beneficiaire['mail_inscription'],
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
	}
	return $res;
}
