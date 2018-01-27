<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Traduit la duree d'abonnement en info lisible, sous forme de periode
 * tous les x mois, tous les ans...
 *
 * Reprise du plugin Abos
 * 
 * @param $duree
 * @return mixed|string
 */
function filtre_periode_en_clair($periodicite){
	$nb = intval($periodicite);
	$duree = trim(preg_replace(",^\d+\s+,", "", $periodicite));
	$duree = ($nb==1 ? _T('abonnements_offre:periodicite_' . $duree) : _T('abonnements_offre:periodicite_tous_les_nb_' . $duree, array('nb' => $nb)));
	return $duree;
}
