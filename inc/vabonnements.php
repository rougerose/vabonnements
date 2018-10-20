<?php

if (!defined("_ECRIRE_INC_VERSION")) return;


/**
 * Mise en forme des logs relatifs aux abonnements.
 *
 * Repris du plugin Abos https://github.com/nursit/abos
 * 
 * @param  string $abo_log
 * @return string
 */
function vabonnements_log($abo_log) {
	$par = "";
	
	if (isset($GLOBALS['visiteur_session']['id_auteur'])){
		$par = _T('public:par_auteur') . ' #' . $GLOBALS['visiteur_session']['id_auteur'] . ' ' . $GLOBALS['visiteur_session']['nom'];
	} else {
		$par = _T('public:par_auteur') . ' ' . $GLOBALS['ip'];
	}

	$abo_log = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . " | "
		. $par
		. " :\n" . $abo_log . "\n--\n";
	
	return $abo_log;
}


/**
 * Récupérer dans la configuration du plugin les identifiants des offres
 * d'abonnement permanent et obligatoire.
 *
 * Ces offres ne font pas l'objet de notification client lors de la souscription
 * et de la relance. 
 * @return array
 */
function vabonnements_offres_obligatoires() {
	include_spip('inc/config');
	$offres_obligatoires = lire_config('vabonnements/abonnements_obligatoires_permanents', '');
	$offres_obligatoires = explode(",", $offres_obligatoires);
	$offres_obligatoires = array_map("trim", $offres_obligatoires);
	$offres_obligatoires = array_map("intval", $offres_obligatoires);
	$offres_obligatoires = array_unique($offres_obligatoires);
	
	return $offres_obligatoires;
}



function vabonnements_recuperer_offres_soutien() {
	$inner = 'INNER JOIN spip_mots_liens AS L1 ON (L1.id_objet = abonnements_offres.id_abonnements_offre AND L1.objet ='.sql_quote('abonnements_offre').')';
	$where = '(abonnements_offres.statut = '.sql_quote('publie').') AND (L1.id_mot = 1076)';
	$offres = sql_allfetsel('id_abonnements_offre', 'spip_abonnements_offres AS abonnements_offres '.$inner, $where);
	
	$offres_soutien = array();
	
	if ($offres) {
		foreach ($offres as $offre) {
			$offres_soutien[] = $offre['id_abonnements_offre'];
		}
	}
	
	return $offres_soutien;
}


function vabonnements_verifier_montant_soutien($id_abonnements_offre) {
	// les offres d'abonnement de soutien disponibles.
	$offres_soutien = vabonnements_recuperer_offres_soutien();
	
	// si le souscripteur a choisi une de ces offres, vérifier le montant saisi.
	if (in_array($id_abonnements_offre, $offres_soutien)) {
		$inputs_soutien_montant = _request('soutien_montant');
		$prix_souscripteur = $inputs_soutien_montant[$id_abonnements_offre];
		
		// le prix Vacarme
		$prix_ttc = prix_objet($id_abonnements_offre, 'abonnements_offre');
		
		if ($prix_souscripteur and $prix_souscripteur < $prix_ttc) {
			return _T('abonnement:erreur_offre_soutien_prix_souscripteur_inferieur', array('prix_ttc' => $prix_ttc));
		}
	} else {
		return '';
	}
}
