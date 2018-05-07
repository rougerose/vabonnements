<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function genie_vabonnements_informer_tiers($timestamp) {
	$now = $timestamp;
	$now_date = date('Y-m-d', $now);
	$relances = vabonnements_get_relances();
	$premiere_relance = reset($relances);
	
	$date_fin = vabonnements_date_fin($premiere_relance, $now);
	
	sql_updateq("spip_abonnements", array('relance' => $premiere_relance), 'statut=' . sql_quote('actif') . ' AND relance=' . sql_quote('') . ' AND date_fin>date_debut AND date_fin<=' . sql_quote($date_fin));
	
	$rappels = sql_allfetsel("DISTINCT relance", "spip_abonnements", 'statut=' . sql_quote('actif') . ' AND relance<>' . sql_quote('off') . ' AND relance<>' . sql_quote(''));
	
	// $abos = sql_allfetsel('*', 'spip_abonnements', 'statut=' . sql_quote('actif') . ' AND relance=' . sql_quote('') . ' AND date_fin>date_debut AND date_fin<=' . sql_quote($date_fin));
	
	return 0;
}


function vabonnements_get_relances($type = 'normal') {
	include_spip('inc/config');
	
	// $relances = lire_config('vabonnements/relances_normal', '');
	// // ex : -30, -15, -7, 0
	// 
	// if ($type == 'tiers') {
	// 	$relances = lire_config('vabonnements/relances_tiers', '');
	// 	// ex : +10, +20, +30
	// }
	
	$relances = "-10, -2";
	$relances = explode(",", $relances);
	$relances = array_map("trim", $relances);
	$relances = array_map("intval", $relances);
	$relances = array_unique($relances);
	sort($relances);
	
	return $relances;
}


function vabonnements_date_fin($relance, $timestamp){
	$jours = -$relance;
	return date('Y-m-d H:i:s', strtotime(($jours>=0 ? "+" : "") . "$jours days", $timestamp));
}
