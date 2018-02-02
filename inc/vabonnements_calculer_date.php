<?php

if (!defined("_ECRIRE_INC_VERSION")) return;


/**
 * Calculer la date de début d'abonnement
 * de façon à ce qu'elle débute au début de la saison :
 * les 21 mars, 21 juin, 21 septembre ou 21 décembre.
 *
 * Cela permet de normaliser les dates de départ d'abonnement
 * car les dates relatives aux numéros ("date_numero" dans la base)
 * ne le sont pas.
 * 
 * @param  string $date
 * @return string
 */
function vabonnements_calculer_date_debut($date) {
	$date_array = recup_date($date, false);
	
	list($annee, $mois, $jour, $heures, $minutes, $secondes) = $date_array;
	
	$mois_debut = vabonnements_calculer_mois_saison($mois);
	$jour_debut = 21;
	$date_debut = date("Y-m-d H:i:s", mktime($heures, $minutes, $secondes, $mois_debut, $jour_debut, $annee));
	return $date_debut;
}


/**
 * Calcule la date de fin d'abonnement.
 *
 * La date de fin est la celle de la veille du changement de saison,
 * par exemple 20 mars si l'abonnement s'arrête au printemps,
 * 20 juin pour une fin d'abonnement en été, etc. 
 * 
 * Code repris de https://stackoverflow.com/a/24014541
 * 
 * @param  string $date_debut 
 * 		Date au format de la fonction vabonnements_calculer_date_debut
 * @param  int $duree Durée de l'abonnement en mois, 12 ou 24
 * @return string
 */
function vabonnements_calculer_date_fin($date_debut, $duree) {
	$debut = new DateTime($date_debut);
	
	// Ajouter la durée de l'abonnement
	$fin = $debut->add(vabonnements_ajouter_mois($duree, $debut));

	// Le jour précédent (le 20)
	$fin->sub(new DateInterval('P1D'));
	
	$date_fin = $fin->format('Y-m-d H:i:s');
	
	return $date_fin;
}


/**
 * Calculer une saison à partir d'un mois donné
 * et retourner le premier mois de la saison. 
 * @param  int  $mois
 * @return int
 */
function vabonnements_calculer_mois_saison($mois) {
	$saison = 12; // décembre = hiver
	
	if (($mois == 3 and $jour >= 21) or $mois > 3) {
		$saison = 03; // mars = printemps
	}
	
	if (($mois == 6 and $jour >=21) or $mois > 6) {
		$saison = 06; // juin = été
	}
	
	if (($mois == 9 and $jour >=21) or $mois > 9) {
		$saison = 09; // septembre = automne
	}
	
	if (($mois == 12 and $jour >=21) or $mois > 12) {
		$saison = 12; // décembre = hiver
	}
	
	return $saison;
}


/**
 * Ajouter un nombre de mois à une date
 * Code repris de https://stackoverflow.com/a/24014541
 *
 * @param  int   $duree
 *         Durée à ajouter en nombre de mois
 * @param  DateTime $objetDate
 *         Date de début
 * @return boolean
 */
function vabonnements_ajouter_mois($duree, DateTime $objetDate) {
	$date_futur = new DateTime($objetDate->format('Y-m-d'));
	$date_futur->modify('last day of +' . $duree . 'month');

	if ($objetDate->format('d') > $date_futur->format('d')) {
		return $objetDate->diff($date_futur);
	} else {
		return new DateInterval('P' . $duree . 'M');
	}
}
