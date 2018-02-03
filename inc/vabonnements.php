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
		. ' : ' . $abo_log . "\n--\n";
	
	return $abo_log;
}
