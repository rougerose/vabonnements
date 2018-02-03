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
 * @param  [type] $mode_paiement [description]
 * @return [type]                [description]
 */
function filtre_paiement_en_clair($mode_paiement) {
	switch ($mode_paiement) {
		case 'cheque':
			$texte_paiement = 'abonnement:info_paiement_cheque';
			break;
		case 'gratuit':
			$texte_paiement = 'abonnement:info_paiement_gratuit';
			break;
		case 'virement':
			$texte_paiement = 'abonnement:info_paiement_virement';
			break;
		case 'paypal':
			$texte_paiement = 'abonnement:info_paiement_paypal';
	}
	
	return $texte_paiement;
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
