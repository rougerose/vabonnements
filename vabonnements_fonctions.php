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
function filtre_periode_en_clair($periodicite){
	$nb = intval($periodicite);
	$duree = trim(preg_replace(",^\d+\s+,", "", $periodicite));
	$duree = ($nb==1 ? _T('abonnements_offre:periodicite_' . $duree) : _T('abonnements_offre:periodicite_tous_les_nb_' . $duree, array('nb' => $nb)));
	return $duree;
}



/**
 * Traduire le mode de paiement enregistré en base
 * en infos texte.
 * 
 * @param  string $mode_paiement
 * @return string
 */
function filtre_paiement_en_clair($mode_paiement) {
	$texte_paiement = _T('abonnement:info_paiement_'.$mode_paiement);
	return $texte_paiement;
}



/**
 * Traduire la duree d'abonnement en nombre d'années.
 * La traduction n'est valable que si les abonnements sont annuels...
 *
 * @param  string $periodicite
 * @return string
 */
function filtre_duree_en_clair($periodicite) {
	$nombre = intval($periodicite);
	$nb = $nombre / 12; // Les données sont en mois, on converti en annee.
	$duree = ($nb == 1 ? _T('abonnements_offre:duree_annee') : _T('abonnements_offre:duree_annees', array('nb' => $nb)));
	return $duree;
}



/**
 * Traduire la durée d'abonnement en nombre de numéros
 * La fonction retourne également la chaîne "numéro(s)".
 * 
 * @param  string $periodicite
 * @return string
 */
function filtre_numeros_nombre_en_clair($periodicite) {
	$nombre = intval($periodicite);
	$nb = $nombre / 3;
	$numeros = ($nb == 1 ? _T('abonnements_offre:numero') : _T('abonnements_offre:numeros', array('nb' => $nb)));
	return $numeros;
}



/**
 * Calculer la référence d'un futur numéro
 * 
 * @param  string  $reference	La référence d'un numéro existant
 * @param  integer $rang		Le rang du numéro souhaité. 
 * 								0 est le numéro en cours. 1 le numéro qui suit. Etc. 
 * @return string La référence du numéro demandé, formatée v0000 
 */
function filtre_calculer_numero_futur_reference($reference, $rang = 1) {
	return calculer_numero_futur($reference, $rang);
}



/**
 * Calculer le titre d'un futur numéro. 
 *
 * @param  string  $reference	La référence d'un numéro existant.
 * @param  integer $rang		Le rang du numéro souhaité. 
 * 								0 est le numéro en cours. 1 le numéro qui suit. Etc. 
 * @return string Le titre du numéro demandé, formaté Vacarme 00
 */
function filtre_calculer_numero_futur_titre($reference, $rang = 1) {
	return calculer_numero_futur($reference, $rang, $titre=true);
}



/**
 * Calculer la date d'un futur numéro. 
 *
 * @param  date  $date		La date de sortie d'un numéro existant.
 * @param  integer $rang 	Le rang du numéro souhaité.
 * 							0 est le numéro en cours. 1 le numéro qui suit. Etc. 
 * @return date
 */
function filtre_calculer_numero_futur_date($date, $rang = 1) {
	include_spip('inc/vabonnements_calculer_date');
	$date_numero_actuel = vabonnements_calculer_date_debut($date);
	// le numéro souhaité en nombre de mois : 
	// - le numéro actuel + 1 est dans 3 mois
	// - le numéro actuel + 2 est dans 6 mois
	// - etc.
	$decalage = $rang * 3;
	$date_numero_futur = vabonnements_calculer_date_duree($date_numero_actuel, $decalage);
	return $date_numero_futur;
}



/**
 * Calculer le titre ou la référence d'un futur numéro à partir des
 * données d'un numéro existant. 
 * 
 * @param  string  $reference	La référence d'un numéro existant.
 * @param  integer $rang		Le rang du numéro souhaité.
 * 								0 est le numéro en cours. 1 le numéro qui suit. Etc. 
 * @param  boolean $titre
 * @return string				La référence ou le titre
 */
function calculer_numero_futur($reference, $rang = 1, $titre = false) {
	// référence du numéro suivant : extraire référence actuelle + rang souhaité
	// exemple : [v]0080 + 1
	$reference_suivant = substr($reference, 1) + $rang;
	
	// le titre de ce numéro
	$titre_suivant = 'Vacarme ' . str_pad($reference_suivant, 2, 0, STR_PAD_LEFT);
	
	if ($titre) {
		$numero = $titre_suivant;
	} else {
		include_spip('inc/vabonnements_numero');
		$numero = vabonnements_numero_convertir_titre_reference($titre_suivant);
	}
	
	return $numero;
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
