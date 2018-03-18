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
 * 
 * @param  string $periodicite
 * @return string
 */
function filtre_numero_en_clair($periodicite) {
	$nombre = intval($periodicite);
	$nb = $nombre / 3;
	$numeros = ($nb == 1 ? _T('abonnements_offre:numero') : _T('abonnements_offre:numeros', array('nb' => $nb)));
	return $numeros;
}



/**
 * Calculer la référence ou le titre d'un prochain numéro
 * à partir de la référence du numéro en cours. 
 * Si rang = 1, on obtient le prochain numéro ; si rang = 2, on obtient
 * le numéro qui suit le prochain, etc.
 * 
 * @param  string  $reference v0000
 * @param  boolean $titre
 * @param  integer $rang
 * @return string La référence ou le titre : v00XX ou Vacarme XX
 */
function filtre_calculer_numero_prochain($reference, $titre = false, $rang = 1) {
	$reference_suivant = substr($reference, 1) + $rang;
	$titre_suivant = 'Vacarme '.str_pad($reference_suivant, 2, 0, STR_PAD_LEFT);
	
	if ($titre == false) {
		$numero_suivant = $titre_suivant;
	} else {
		$convertir = charger_fonction('vextras_convertir_titre_reference', 'inc');
		$numero_suivant = $convertir($titre_suivant);
	}
	
	return $numero_suivant;
}


/**
 * Calculer la date du prochain numéro à partir de la date de l'actuel.
 *
 * Compte tenu du fait que la date est "normalisée" avec la fonction
 * vabonnements_calculer_date_debut (on obtient donc le premier jour 
 * de la saison concernée), si on ajoute 3 mois on obtient le dernier
 * jour de la même saison. On compte plus large et on ajoute 4 mois.
 * 
 * @param  string $date_numero_actuel
 * @return string
 */
function filtre_calculer_date_numero_prochain($date_numero_actuel) {
	include_spip('inc/vabonnements_calculer_date');
	$date_actuel = vabonnements_calculer_date_debut($date_numero_actuel);
	$date_prochain = vabonnements_calculer_date_fin($date_actuel, 4);
	return $date_prochain;
}

/**
 * Trier un tableau par clé. 
 * fonction ksort de PHP utilisable dans un squelette SPIP.
 * @param  array $tableau
 * @return array
 */
function filtre_keysort($tableau) {
	ksort($tableau);
	return $tableau;
}



function filtre_numero_suivant_calculer_titre_reference($reference, $rang = 0, $titre = false) {
	$reference_chiffre = substr($reference, 1) + $rang;
	$titre_numero = 'Vacarme '.str_pad($reference_chiffre, 2, 0, STR_PAD_LEFT);
	
	if ($titre !== false) {
		$numero_suivant = $titre_numero;
	} else {
		$numero_suivant = filtre_numero_suivant_calculer_titre_reference($titre_numero);
	}
	
	return $numero_suivant;
}



function filtre_numero_titre_convertir_reference($titre) {
	if (preg_match('!\d+!', $titre, $m)) {
		$reference = 'v' . str_pad(intval($m[0]), 4, '0', STR_PAD_LEFT);
	} else {
		$reference = '';
	}
	return $reference;
}
