<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

function abonnements_lister_activations($date) {
	include_spip('base/abstract_sql');
	
	$abonnements = array();
	
	$fin = date('Y-m-d H:i:s', $date);
	
	$res = sql_select(
		'*', 
		'spip_abonnements', 
		'statut='.sql_quote('paye')
		.' AND numero_debut <> ' . sql_quote('')
		.' AND numero_fin <> ' . sql_quote('')
		.' AND coupon=' . sql_quote('')
		.' AND date_debut < ' . sql_quote($fin)
		.' AND date_debut < date_fin'
		.' AND date_fin > ' . sql_quote($fin) 
	);
	
	while ($row = sql_fetch($res)) {
		$abonnements[$row['id_abonnement']] = array(
			'id_auteur' => $row['id_auteur'],
			'log' => $row['log']
		);
	}
	
	return $abonnements;
}
