<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

define('ACTION_OFFRIR_ABONNEMENT', 1);

/**
 * Caculer un coupon/bon cadeau
 *
 * Source : http://bdelespierre.fr/article/bien-plus-quun-simple-jeton
 *
 * @param  [type] $id_auteur   [description]
 * @param  [type] $date        [description]
 * @param  [type] $code_action [description]
 * @return [type]              [description]
 */
// function vabonnements_coupon_encode($id_auteur, $date, $code_action) {
// 
// }


// function vabonnements_coupon_decode() {
// 
// }


/**
 * Encoder une date AAAAMMDD sur 2 octets
 *
 * Source : http://bdelespierre.fr/article/compacter-une-date-sur-2-octets
 * 
 * @param  int  $annee
 * @param  int  $mois
 * @param  int  $jour
 * @param  int $ref année de référence. 
 * @return string
 */
function date16_encode ($annee, $mois, $jour, $ref = 2018) {
	// le bit de signature détermine si l'année est avant ou après l'année de référence
	$sig = (int)($annee - $ref) < 0;
	
	// l'année devient relative à l'année de référence (en valeur absolue)
	$annee = abs($annee - $ref);

	$jour  &= 0b00011111; // ne garder que les 5 premiers bits
	$mois  &= 0b00001111; // ne garder que les 4 premiers bits
	$annee &= 0b01111111; // ne garder que les 7 premiers bits

	return ($sig << 15) | ($annee << 9) | ($mois << 5) | $jour;
}


/**
 * Décoder une date sur 2 octects
 *
 * Source : http://bdelespierre.fr/article/compacter-une-date-sur-2-octets
 * 
 * @param  string  $date 
 * @param  integer $ref  année de référence
 * @return [type]        [description]
 */
function date16_decode ($date, $ref = 2018)
{
	$sig    = ($date >> 15) & 0b00000001;
	$annee  = ($date >> 9)  & 0b00111111;
	$mois   = ($date >> 5)  & 0b00001111;
	$jour   = ($date)       & 0b00011111;

	// on recalcule l'année en fonction de la date de référence
	$annee = $sig ? $ref - $annee : $ref + $annee;

	return [$annee, $mois, $jour];
}
