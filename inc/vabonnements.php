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
