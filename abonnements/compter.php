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



function abonnements_compter_jour($jour) {
	$jour = date('Y-m-d', strtotime($jour));
	$jour_debut = date('Y-m-d 00:00:00', strtotime($jour));
	$jour_fin = date('Y-m-d 23:59:59', strtotime($jour));
	
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
	
	$where_abos = array();
	// $where_abos[] = 'date_debut <='.sql_quote($jour_fin);
	// $where_abos[] = '(date_fin < date_debut OR date_fin >='.sql_quote($jour_fin).')';
	// $where_abos[] = '(statut='.sql_quote('actif').')'
	$where_abos[] = '(date_fin IS NULL)';
	$row = sql_allfetsel('id_auteur', 'spip_abonnements', $where_abos);
	//$row = sql_fetsel('COUNT(DISTINCT id_auteur) AS N', 'spip_abonnements', $where_abos);
}
