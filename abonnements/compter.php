<?php


if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


/**
 * Etablir les statistiques des abonnements,
 * depuis la dernière fois jusqu'à aujourd'hui.
 *
 * Fonction reprise de https://github.com/nursit/abos/blob/master/abos/compter.php
 * 
 * @return bool True, c'est terminé ; false dans le cas contraire.
 */
function abonnements_compter_dist() {
	$now = $_SERVER['REQUEST_TIME'];
	
	$hier = date('Y-m-d', strtotime('-1 day', $now));
	
	$last = '';
	
	// dernieres stats faites ?
	$last = sql_getfetsel('date', 'spip_abonnements_stats', 'date <=' . sql_quote($hier), '', 'date DESC', '0,1');
	
	// a moins que ca ne soit la premiere fois ?
	if (!$last){
		$last = sql_getfetsel('date', 'spip_abonnements', sql_in('statut', array('actif', 'resilie')), '', 'date', '0,1');
		
		if (!$last OR !intval($last) OR !strtotime($last)){
			// rien a faire, on a fini
			return true;
		}
		// il faut partir de la veille
		$last = date('Y-m-d', strtotime('-1 day', strtotime($last)));
	}
	
	// ok faisons les stats de jour en jour jusqu'a $yesterday
	$nmax = 10;
	while ($last < $hier AND $nmax-->0){
		$jour = date('Y-m-d', strtotime('+1 day', strtotime($last)));
		abonnements_compter_jour($jour);
		$last = $jour;
	}
	
	return (($last == $hier) ? true : false);
}


/**
 * Statistiques d'abonnement
 *
 * Remarque : les critères de sélection des abonnements actifs ne prennent
 * pas en compte les abonnements résiliés mais actifs jusqu'à leur date d'échéance
 * (fonctionnement prévu lorsque des paiements récurrents sont possibles).
 * Il faudra corriger si ce type de paiement est mis en place.
 * 
 * @param  string $jour date du jour
 * @return void
 */
function abonnements_compter_jour($jour) {
	$jour = date('Y-m-d', strtotime($jour));
	$jour_debut = date('Y-m-d 00:00:00', strtotime($jour));
	$jour_fin = date('Y-m-d 23:59:59', strtotime($jour));
	
	// TODO: ressortir les abonnements offerts ?
	// TODO: ressortir les abonnement payés mais non actif (abonnements offerts) ?
	$set = array(
		'date' => $jour,
		'nb_abonnes' => 0,
		'nb_abonnements' => 0, // nombre d'abonnements actifs
		'nb_abonnements_new' => 0, // nombre d'abonnements souscrits par de nouveaux abonnes (conquete)
		'nb_abonnements_plus' => 0, // nombre d'abonnements souscrits
		'nb_abonnements_moins' => 0, // nombre d'abonnements finis
		'ventil_abonnements' => '', // par offre : nombre d'abonnements actifs
		'ventil_abonnements_new' => '', // par offre : nombre d'abonnements souscrits par de nouveaux abonnes (conquete)
		'ventil_abonnements_plus' => '', // par offre : nombre d'abonnements souscrits
		'ventil_abonnements_moins' => '', // par offre : nombre d'abonnements finis
	);
	
	// 
	// Le nombre d'abonnés uniques à ce jour.
	// Les abonnements qui finissent dans la journée ne sont pas comptabilisés, 
	// ils seront intégrés dans les abonnements finis ce jour.
	// Les abonnements gratuits sont exclus.
	// 
	$where_abos = array();
	$where_abos[] = 'date_debut <='.sql_quote($jour_fin);
	$where_abos[] = '(date_fin >'.sql_quote($jour_fin).')';
	$where_abos[] = '(statut='.sql_quote('actif').')';
	$where_abos[] = sql_in('mode_paiement', array('gratuit'), 'NOT');

	$row = sql_fetsel('COUNT(DISTINCT id_auteur) AS N', 'spip_abonnements', $where_abos);
	$set['nb_abonnes'] = reset($row);
	
	// 
	// Les abonnements actifs ventilés par offre, à ce jour
	// 
	$rows = sql_allfetsel('id_abonnements_offre, count(id_abonnement) AS N', 'spip_abonnements', $where_abos, 'id_abonnements_offre');
	abonnements_compte_ventilation('', $set, $rows);
	
	// 
	// Les abonnements en plus de la journée. 
	// Ce sont des abonnements payés (offerts), actifs.
	// Les abonnements gratuits sont exclus.
	// 
	$where_abos = array();
	$where_abos[] = '(date >='.sql_quote($jour_debut).' AND date <='.sql_quote($jour_fin).')';
	$where_abos[] = '(statut='.sql_quote('paye').' OR (statut='.sql_quote('actif').' AND date_fin > date_debut))';
	$where_abos[] = sql_in('mode_paiement', array('gratuit'), 'NOT');
	$rows = sql_allfetsel('id_abonnements_offre, count(id_abonnement) AS N', 'spip_abonnements', $where_abos, 'id_abonnements_offre');
	abonnements_compte_ventilation('plus', $set, $rows);
	
	//
	// Les nouveaux abonnés de la journée.
	// Ce sont des abonnements -- payés (offerts), actifs et résiliés --
	// par des abonnés qui n'ont jamais souscrit d'abonnement à ce jour.
	// Les abonnements gratuits sont exclus.
	// 
	$id_auteurs = sql_allfetsel('DISTINCT id_auteur', 'spip_abonnements', $where_abos);
	$id_auteurs = array_map('reset', $id_auteurs);
	
	// Exclure du résultat précédent les auteurs qui avaient déjà un abonnement
	$exclus = sql_allfetsel('DISTINCT id_auteur', 'spip_abonnements', 'date <'.sql_quote($jour_debut).' AND '.sql_in('statut', array('paye', 'actif')).' AND '.sql_in('id_auteur', $id_auteurs).' AND '.sql_in('mode_paiement', array('gratuit'), 'NOT'));
	$exclus = array_map('reset', $exclus);
	$id_auteurs = array_diff($id_auteurs, $exclus);
	
	$where_abos[] = sql_in('id_auteur', $id_auteurs);
	$rows = sql_allfetsel('id_abonnements_offre, count(id_abonnement) AS N', 'spip_abonnements', $where_abos, 'id_abonnements_offre');
	abonnements_compte_ventilation('new', $set, $rows);
	
	//
	// Les abonnements perdus à ce jour
	// Ce sont les abonnements actifs et résiliés dont la date de fin est à la date
	// du jour.
	// Les abonnements gratuits sont exclus.
	// 
	$where_abos = array();
	$where_abos[] = 'date_fin > date_debut';
	$where_abos[] = 'date_fin >='.sql_quote($jour_debut);
	$where_abos[] = 'date_fin <='.sql_quote($jour_fin);
	$where_abos[] = sql_in('statut', array('actif', 'resilie'));
	$where_abos[] = sql_in('mode_paiement', array('gratuit'), 'NOT');
	
	$rows = sql_allfetsel('id_abonnements_offre, count(id_abonnement) AS N', 'spip_abonnements', $where_abos, 'id_abonnements_offre');
	abonnements_compte_ventilation('moins', $set, $rows);
	
	//var_dump($set);
	sql_insertq('spip_abonnements_stats', $set);
}



function abonnements_compte_ventilation($quoi, &$set, $rows){
	$_quoi = ($quoi ? '_' . $quoi : '');
	
	$set['ventil_abonnements' . $_quoi] = array();
	
	foreach ($rows as $row){
		$set['ventil_abonnements' . $_quoi][$row['id_abonnements_offre']] = $row['N'];
	}
	
	$set['nb_abonnements' . $_quoi] = array_sum($set['ventil_abonnements' . $_quoi]);
	
	$set['ventil_abonnements' . $_quoi] = json_encode($set['ventil_abonnements' . $_quoi]);
}
