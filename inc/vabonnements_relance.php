<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

/**
 * Définir le calendrier des relances d'abonnement
 *
 * Fonction reprise du plugin Abos de Nursit et en particulier
 * https://github.com/nursit/abos/blob/master/genie/abos_relancer.php
 * 
 * @return array
 */
function vabonnements_get_relances($type = 'tiers') {
	include_spip('inc/config');
	
	$relances = lire_config('vabonnements/relances_'.$type, '');
	$relances = explode(",", $relances);
	$relances = array_map("trim", $relances);
	$relances = array_map("intval", $relances);
	$relances = array_unique($relances);
	sort($relances);
	
	return $relances;
}


/**
 * Calculer une date à partir d'un délai de relance
 *
 * Fonction reprise du plugin Abos de Nursit et en particulier
 * https://github.com/nursit/abos/blob/master/genie/abos_relancer.php
 * 
 * @param  int $relance 
 * @param  int $now  timestamp
 * @return string date format SQL
 */
function vabonnements_date_relance($relance, $now){
	$days = -$relance;
	return date('Y-m-d H:i:s', strtotime(($days>=0 ? "+" : "") . "$days days", $now));
}


/**
 * Calculer une date de relance à venir à partir d'une date d'échéance
 *
 * Fonction reprise du plugin Abos de Nursit et en particulier
 * https://github.com/nursit/abos/blob/master/genie/abos_relancer.php
 * 
 * @param  string $date_debut Date début d'abonnement
 * @param  int $now     Timestamp
 * @return string
 */
function vabonnements_prochaine_relance($type = 'tiers', $date, $now = null){
	if (!$now){
		$now = time();
	}

	$relances = vabonnements_get_relances($type);
	rsort($relances);

	$next = 'off';
	while (count($relances)){
		$jours = array_shift($relances);
		$date_relance = vabonnements_date_relance($jours, $now);
		if (
			$type == 'tiers' and date('Y-m-d', strtotime($date_relance)) > date('Y-m-d', strtotime($date)) 
			or $type == 'echeances' and $date < $date_relance) {
				return $next;
		}
		// if ($date_fin<abos_date_fin($jours, $now)){
		// 	return $next;
		// }
		// if (date('Y-m-d', strtotime($date_relance)) > date('Y-m-d', strtotime($date))){
		// 	return $next;
		// }
		$next = $jours;
	}

	return 'off'; // on n'arrive jamais la
}
