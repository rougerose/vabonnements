<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Traduire la duree d'abonnement en info lisible, sous forme de periode
 * tous les x mois, tous les ans...
 *
 * Repris du plugin Abos https://github.com/nursit/abos
 * 
 * @param $duree
 * @return mixed|string
 */
function filtre_periodicite_en_clair($duree){
	$nb = intval($duree);
	$periode = trim(preg_replace(",^\d+\s+,", "", $duree));
	$periodicite = ($nb == 1 ? _T('abonnements_offre:periodicite_' . $periode) : _T('abonnements_offre:periodicite_tous_les_nb_' . $periode, array('nb' => $nb)));
	return $periodicite;
}



/**
 * Traduire la duree d'abonnement en nombre d'années.
 * La traduction n'est valable que si les abonnements sont annuels...
 *
 * @param  string $duree
 * @return string
 */
function filtre_duree_en_clair($duree) {
	$nombre = intval($duree);
	$nb = $nombre / 12; // Les données sont en mois, on converti en annee.
	$duree = ($nb == 1 ? _T('abonnements_offre:duree_annee') : _T('abonnements_offre:duree_annees', array('nb' => $nb)));
	return $duree;
}


/**
 * Filtre vacarme_saison_annee
 *
 * Le filtre saison_annee de Spip ne modifie pas l'année.
 * Or, si la date est le 22 décembre 2017 par exemple, le numéro
 * correspondant n'est pas Hiver 2017, mais Hiver 2018. 
 * 
 * @param  date $date
 * @return date
 */
function filtre_vacarme_saison_annee($date) {
	$date_array = recup_date($date, false);
	list($annee, $mois, $jour, $heures, $minutes, $secondes) = $date_array;
	
	if ($jour >= 21 AND $mois == 12) {
		$annee_1 = $annee + 1;
		$date = date("Y-m-d H:i:s", mktime($heures, $minutes, $secondes, $mois, $jour, $annee_1));
	}
	
	return affdate_base($date, 'saison_annee');
}


/**
 * Calculer un hash de sécurité pour l'abonnement offert 
 * 
 * @param  int $id_auteur
 * @param  int $id_abonnement
 * @param  int $date_abonnement
 * @return string
 */
function vabonnements_calcul_hash_abonnement($id_auteur, $id_abonnement, $date_abonnement) {
	$donnees = array($id_auteur, $id_abonnement, $date_abonnement);
	return md5(implode(';', array_values($donnees)));
}



function filtre_abonnements_reporting_decompte_dist(){
	include_spip('abonnements/compter');
	return abonnements_reporting_decompte(36); // sur 36 mois en affichage dans le site
}
