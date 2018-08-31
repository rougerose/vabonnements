<?php 

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


include_spip('inc/vabonnements');
include_spip('inc/filtres');


/**
 * Compléter les informations relatives à un abonnement et son bénéficiaire
 * lors de la création de la commande.
 * 
 * @param  array $champs 
 * @return array
 */
function abonnements_completer_dist($champs) {
	$champs_abonnement = array();
	
	if (isset($champs['nom_inscription']) and strlen($champs['nom_inscription'])) {
		$champs['offert'] = 'oui';
	} else {
		$champs['offert'] = 'non';
	}
	
	// 
	// Champs en commun pour tous les abonnements
	// 
	// Calculer numero_fin et date_debut|fin
	include_spip('inc/vabonnements_calculer_debut_fin');
	$numeros = vabonnements_calculer_debut_fin($champs['id_abonnements_offre'], $champs['numero_debut']);
	$champs['numero_fin'] = $numeros['numero_fin'];
	$champs['date_debut'] = $numeros['date_debut'];
	$champs['date_fin'] = $numeros['date_fin'];
	
	// La durée
	$duree = generer_info_entite($champs['id_abonnements_offre'], 'abonnements_offre', 'duree', '*'); 
	
	$champs['duree_echeance'] = $duree;
	
	// Completer les champs selon le type d'abonnement (personnel ou offert)
	if ($champs['offert'] == 'oui') {
		$champs_abonnement = completer_abonnement_offert($champs);
	} else {
		$champs_abonnement = completer_abonnement_personnel($champs);
	}
	
	return $champs_abonnement;
}


/**
 * Compléter un abonnement offert
 * 
 * @param  array $champs
 * @return array
 */
function completer_abonnement_offert($champs) {
	include_spip('inc/vprofils');
	include_spip('inc/vabonnements_code');
	include_spip('inc/vabonnements_calculer_debut_fin');
	
	$auteur_tiers = array(
		'civilite' => $champs['civilite'],
		'nom_inscription' => $champs['nom_inscription'],
		'prenom' => $champs['prenom'],
		'mail_inscription' => $champs['mail_inscription'],
		'organisation' => $champs['organisation'],
		'service' => $champs['service'],
		'voie' => $champs['voie'],
		'complement' => $champs['complement'],
		'boite_postale' => $champs['boite_postale'],
		'code_postal' => $champs['code_postal'],
		'ville' => $champs['ville'],
		'region' => $champs['region'],
		'pays' => $champs['pays']
	);
	
	// Créer ou récupérer id_auteur, id_contact et id_adresse
	$id_auteur_tiers = vprofils_verifier_ou_creer_auteur_tiers($auteur_tiers);
	
	if ($id_auteur_tiers 
		AND $id_contact_tiers = vprofils_verifier_ou_creer_contact_auteur_tiers($id_auteur_tiers, $auteur_tiers)
		AND $id_adresse_tiers = vprofils_verifier_ou_creer_coordonnees_tiers($id_auteur_tiers, $auteur_tiers)
	) {
		$champs['id_auteur_payeur'] = $champs['id_auteur'];
		$champs['id_auteur'] = $id_auteur_tiers;
		
		// La date d'envoi du message est un timestamp,
		// convertir au format mysql
		$champs['date_message'] = date('Y-m-d H:i:s', $champs['date_message']);
		
		// Calculer le code (coupon)
		$date_abonnement = $champs['date'];
		$code_action = _ACTION_OFFRIR_ABONNEMENT;
		$champs['coupon'] = vabonnements_creer_code($id_auteur_tiers, $date_abonnement, $code_action);
		
		// 
		// Ajout du log
		// 
		// Texte du log : 
		// Commande n°[] : Création de l'abonnement [titre] [duree] ([prix]), du numéro [numero_debut] au numéro [numero_fin], par [Nom souscripteur][Prénom souscripteur] (auteur n°[id_auteur_payeur]). Code cadeau destiné au bénéficiaire : [coupon]. L'email d'invitation sera envoyé le [date_message]. Message personnalisé du souscripteur ? [oui|non].
		// 
		$titre = generer_info_entite($champs['id_abonnements_offre'], 'abonnements_offre', 'titre');
		$duree_en_clair = filtre_duree_en_clair($champs['duree_echeance']);
		$prix_en_clair = $champs['prix_echeance'];
		$commande_en_clair = 'Commande n°'.$champs['id_commande'].' : ';
		$date_envoi_en_clair = affdate($champs['date_message']);
		$nom_payeur = prenom_nom(generer_info_entite($champs['id_auteur_payeur'], 'auteur', 'nom'));
		$message_perso_payeur = (strlen($champs['message'])) ? "Oui." : "Non.";
		
		$log_abo = $commande_en_clair."création de l'abonnement $titre $duree_en_clair ($prix_en_clair), ";
		$log_abo .= "du numéro ".$champs['numero_debut']." au numéro ".$champs['numero_fin'];
		$log_abo .= "par $nom_payeur (auteur n°" . $champs['id_auteur_payeur']."). ";
		$log_abo .= 'Code cadeau destiné au bénéficiaire : '.$champs['coupon'].'. ';
		$log_abo .= "L'email d'invitation pour activer l'abonnement sera envoyé le $date_envoi_en_clair. ";
		$log_abo .=  "Message personnalisé par le payeur ? $message_perso_payeur";
		$champs['log'] = vabonnements_log($log_abo);
		
		return $champs;
	}
	return false;
}


/**
 * Compléter un abonnement personnel
 * 
 * @param  array $champs
 * @return array
 */
function completer_abonnement_personnel($champs) {
	// 
	// Ajout du log
	// 
	// Texte du log : 
	// Commande n°[] : Création de l'abonnement [titre] [duree] ([prix]), du numéro [numero_debut] au numéro [numero_fin].
	// 
	$titre = generer_info_entite($champs['id_abonnements_offre'], 'abonnements_offre', 'titre');
	$duree_en_clair = filtre_duree_en_clair($champs['duree_echeance']);
	$prix_en_clair = $champs['prix_echeance'];
	$commande_en_clair = 'Commande n°'.$champs['id_commande'].' : ';
	
	$log_abo = $commande_en_clair."création de l'abonnement $titre $duree_en_clair ($prix_en_clair), ";
	$log_abo .= "du numéro ".$champs['numero_debut']." au numéro ".$champs['numero_fin'].".";
	$champs['log'] = vabonnements_log($log_abo);
	
	return $champs;
}
