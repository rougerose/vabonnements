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
 * Les données minimales et obligatoires de l'abonnement :
 *  - id_commande
 *  - id_abonnements_offre
 *  - id_auteur
 *  - date
 *  - statut
 *  - numero_debut
 *
 * Si l'abonnement est offert, données supplémentaires nécessaires :
 *  - id_auteur (qui devient id_auteur_payeur)
 *  - date_message
 *  - message
 *  - offert = oui
 *
 * Les données minimales et obligatoires pour l'auteur tiers :
 *  - civilite
 *  - nom_inscription
 *  - mail_inscription
 *  - organisation
 *  - service
 *  - voie
 *  - complement
 *  - boite_postale
 *  - code_postale
 *  - ville
 *  - region
 *  - pays
 * 
 * @param  array $champs 
 * @return array|boolean
 */
function abonnements_completer_dist($champs) {
	// 
	// Dissocier les champs relatifs à l'auteur tiers 
	// et ceux relatifs à l'abonnement
	// 
	$champs_abonnement = array();
	$champs_auteur = array(
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
	
	// Supprimer les données inutiles pour l'abonnement
	$champs_abonnement = array_diff_key($champs, $champs_auteur);
	if (isset($champs_abonnement['cadeau'])) {
		unset($champs_abonnement['cadeau']);
	}
	
	// 
	// Champs en commun pour tous les abonnements
	// 
	
	// Calculer numero_fin et date_debut|fin
	// 
	// Il s'agit d'une insertion, ces informations n'ont pas été déjà calculées.
	// 
	include_spip('inc/vabonnements_calculer_debut_fin');
	$numeros = vabonnements_calculer_debut_fin($champs_abonnement['id_abonnements_offre'], $champs_abonnement['numero_debut']);
	$champs_abonnement['numero_fin'] = $numeros['numero_fin'];
	$champs_abonnement['date_debut'] = $numeros['date_debut'];
	$champs_abonnement['date_fin'] = $numeros['date_fin'];
	
	
	// La durée
	if (!isset($champs_abonnement['duree_echeance']) or !strlen($champs_abonnement['duree_echeance'])) {
		$duree = generer_info_entite($champs_abonnement['id_abonnements_offre'], 'abonnements_offre', 'duree', '*');
		$champs_abonnement['duree_echeance'] = $duree;
	} else {
		$duree = $champs_abonnement['duree_echeance'];
	}
	
	
	// Le prix
	if (isset($champs_abonnement['prix_souscripteur']) and strlen($champs_abonnement['prix_souscripteur'])) {
		include_spip('inc/config');
		$tva_abonnements = lire_config('vabonnements/taxe');
		$champs_abonnement['prix_echeance'] = ($champs_abonnement['prix_souscripteur'] / (1 + $tva_abonnements)) * 1;
	}
	
	if (!isset($champs_abonnement['prix_echeance']) or !strlen($champs_abonnement['prix_echeance'])) {
		$fonction_prix_ht = charger_fonction('ht', 'inc/prix');
		$prix_ht = $fonction_prix_ht('abonnements_offre', $champs_abonnement['id_abonnements_offre'], 3);
		$champs_abonnement['prix_echeance'] = $prix_ht;
	}
	
	unset($champs_abonnement['prix_souscripteur']);
	
	
	// Completer les champs selon le type d'abonnement (personnel ou offert)
	if ((isset($champs_abonnement['offert']) and $champs_abonnement['offert'] == oui) or (isset($champs_auteur['nom_inscription']) and strlen($champs_auteur['nom_inscription']))) {
		$champs_abonnement['offert'] = 'oui';
		$champs_abonnement = completer_abonnement_offert($champs_auteur, $champs_abonnement);
	} else {
		$champs_abonnement['offert'] = 'non';
		$champs_abonnement = completer_abonnement_personnel($champs_abonnement);
	}
	
	return $champs_abonnement;
}


/**
 * Compléter un abonnement offert
 * 
 * @param  array $champs_auteur
 * @param  array $champs_abonnement
 * @return array|boolean
 */
function completer_abonnement_offert($champs_auteur, $champs_abonnement) {
	include_spip('inc/vprofils');
	include_spip('inc/vabonnements_code');
	include_spip('inc/vabonnements_calculer_debut_fin');
	
	// Créer ou récupérer id_auteur, id_contact et id_adresse
	$id_auteur_tiers = vprofils_verifier_ou_creer_auteur_tiers($champs_auteur);
	
	// Les compléments pour créer l'abonnement de cet auteur
	if ($id_auteur_tiers 
		AND $id_contact_tiers = vprofils_verifier_ou_creer_contact_auteur_tiers($id_auteur_tiers, $champs_auteur)
		AND $id_adresse_tiers = vprofils_verifier_ou_creer_coordonnees_tiers($id_auteur_tiers, $champs_auteur)
	) {
		$champs_abonnement['id_auteur_payeur'] = $champs_abonnement['id_auteur'];
		$champs_abonnement['id_auteur'] = $id_auteur_tiers;
		
		// La date d'envoi du message est un timestamp,
		// convertir au format mysql
		$champs_abonnement['date_message'] = date('Y-m-d H:i:s', $champs_abonnement['date_message']);
		
		// Calculer le code (coupon)
		$date_abonnement = $champs_abonnement['date'];
		$code_action = _ACTION_OFFRIR_ABONNEMENT;
		$champs_abonnement['coupon'] = vabonnements_creer_code($id_auteur_tiers, $date_abonnement, $code_action);
		
		// 
		// Ajout du log
		// 
		// Texte du log : 
		// Commande n°[] : Création de l'abonnement [titre] [duree] ([prix]), du numéro [numero_debut] au numéro [numero_fin], par [Nom souscripteur][Prénom souscripteur] (auteur n°[id_auteur_payeur]). Code cadeau destiné au bénéficiaire : [coupon]. L'email d'invitation sera envoyé le [date_message]. Message personnalisé du souscripteur ? [oui|non].
		// 
		$titre = generer_info_entite($champs_abonnement['id_abonnements_offre'], 'abonnements_offre', 'titre');
		$duree_en_clair = filtre_duree_en_clair($champs_abonnement['duree_echeance']);
		$prix_en_clair = (intval($champs_abonnement['prix_echeance']) == 0) ? 'Gratuit' : $champs_abonnement['prix_echeance'].' euros HT';
		$commande_en_clair = 'Commande n°'.$champs_abonnement['id_commande'].' : ';
		$date_envoi_en_clair = affdate($champs_abonnement['date_message']);
		$nom_payeur = prenom_nom(generer_info_entite($champs_abonnement['id_auteur_payeur'], 'auteur', 'nom'));
		$message_perso_payeur = (strlen($champs_abonnement['message'])) ? "Oui." : "Non.";
		
		$log_abo = $commande_en_clair."création de l'abonnement $titre $duree_en_clair ($prix_en_clair), ";
		$log_abo .= "du numéro ".$champs_abonnement['numero_debut']." au numéro ".$champs_abonnement['numero_fin'];
		$log_abo .= "par $nom_payeur (auteur n°" . $champs_abonnement['id_auteur_payeur']."). ";
		$log_abo .= 'Code cadeau destiné au bénéficiaire : '.$champs_abonnement['coupon'].'. ';
		$log_abo .= "L'email d'invitation pour activer l'abonnement sera envoyé le $date_envoi_en_clair. ";
		$log_abo .=  "Message personnalisé par le payeur ? $message_perso_payeur";
		$champs_abonnement['log'] = vabonnements_log($log_abo);
		
		return $champs_abonnement;
	}
	return false;
}


/**
 * Compléter un abonnement personnel
 * 
 * @param  array $champs_abonnement
 * @return array
 */
function completer_abonnement_personnel($champs_abonnement) {
	// 
	// Ajout du log
	// 
	// Texte du log : 
	// Commande n°[] : Création de l'abonnement [titre] [duree] ([prix]), du numéro [numero_debut] au numéro [numero_fin].
	// 
	$titre = generer_info_entite($champs_abonnement['id_abonnements_offre'], 'abonnements_offre', 'titre');
	$duree_en_clair = filtre_duree_en_clair($champs_abonnement['duree_echeance']);
	$prix_en_clair = (intval($champs_abonnement['prix_echeance']) == 0) ? 'Gratuit' : $champs_abonnement['prix_echeance'].' euros HT';
	$commande_en_clair = 'Commande n°'.$champs_abonnement['id_commande'].' : ';
	
	$log_abo = $commande_en_clair."création de l'abonnement $titre $duree_en_clair ($prix_en_clair), ";
	$log_abo .= "du numéro ".$champs_abonnement['numero_debut']." au numéro ".$champs_abonnement['numero_fin'].".";
	$champs_abonnement['log'] = vabonnements_log($log_abo);
	
	return $champs_abonnement;
}
