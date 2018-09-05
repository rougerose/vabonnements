<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

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
function vabonnements_creer_code($id_auteur, $date = '', $code_action = '') {
	if (!$code_action) {
		return;
	}
	
	if (!$date) {
		$date = date('Y-m-d');
	}
	
	@list($annee, $mois, $jour) = explode('-', $date);
	// date du jour sous forme d'entier
	$date16 = date16_encode($annee, $mois, $jour);

	// nombre aléatoire pour l'entropie
	$entropy = mt_rand();

	// représentation binaire de notre jeton
	$binary_token = pack('ISSS', $id_auteur, $code_action, $date16, $entropy);
	
	return $token = base64url_encode($binary_token);
}


/**
 * Décoder un code coupon/bon cadeau 
 * @param  [type] $token [description]
 * @return [type]        [description]
 */
function vabonnements_lire_code($token) {
	if (!$token) {
		return false;
	}

	// retrouver la version binaire du jeton
	$binary_token = base64url_decode($token);

	if (!$binary_token) {
		return false;
	}

	// extraire les informations du bitfield
	$data = @unpack('Iid/Scode_action/Sdate/Sentropy', $binary_token);

	if (!$data) {
		return false;
	}

	list($year, $month, $day) = date16_decode($data['date']);

	// populer les variables correspondantes
	$id          = $data['id'];
	$code_action = $data['code_action'];
	$date        = "$year-$month-$day";
	$entropy     = $data['entropy'];
	
	return $res = array(
		'id' => $id,
		'code_action' => $code_action,
		'date' => $date
	);
}


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


function base64url_encode($data) {
  return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data) {
  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
}
