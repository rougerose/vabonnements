<?php

if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}


include_spip('base/abstract_sql');

/**
 * Calculer et ajouter le champ référence d'une rubrique
 * correspondant à un numéro de Vacarme.
 * 
 * @param  int $id_rubrique
 * @return string Si $res est une chaîne vide, tout est ok. Sinon message d'erreur.
 */
function vabonnements_numero_referencer($id_rubrique) {
	$res = '';
	
	$titre = sql_getfetsel('titre', 'spip_rubriques', 'id_rubrique='.$id_rubrique);
	$reference = vabonnements_numero_convertir_titre_reference($titre);
	
	// Erreur ?
	if (strstr($reference, 'erreur !')) {
		$res = 'Le calcul de la référence pour la rubrique '.$titre.' numéro '.$id_rubrique.' a échoué';
	}
	
	// On enregistre la référence dans tous les cas.
	sql_updateq('spip_rubriques', array('reference' => $reference), 'id_rubrique='.$id_rubrique);
	
	return $res;
}


/**
 * Convertir le titre d'une rubrique en référence.
 *
 * Vacarme 80 => v0080
 * 
 * @param  string $titre
 * @return string
 */
function vabonnements_numero_convertir_titre_reference($titre) {
	if (preg_match('!\d+!', $titre, $m)) {
		$reference = 'v' . str_pad(intval($m[0]), 4, '0', STR_PAD_LEFT);
	} else {
		$reference = 'erreur !';
	}
	
	return $reference;
}
