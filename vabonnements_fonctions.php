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
	
	if ($titre) {
		$numero_suivant = $titre_suivant;
	} else {
		$convertir = charger_fonction('vextras_convertir_titre_reference', 'inc');
		$numero_suivant = $convertir($titre_suivant);
	}
	
	return $numero_suivant;
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
