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


/**
 * Page de rapport
 * @param  integer $nb_mois [description]
 * @return string html du rapport
 */
function abonnements_reporting_decompte($nb_mois = 6) {
	$now = $_SERVER['REQUEST_TIME'];
	$offres_vues = array();

	$texte = "";
	
	// 
	// Statistiques de la dernière semaine
	// 
	$j = date('Y-m-d', $now);
	$jm7 = date('Y-m-d', strtotime("-7 day", $now));
	
	
	$thead = "<thead>"
				."<tr>"
					."<th>"._T('public:date')."</th>"
					."<th colspan='2'>"._T('abonnement:abonnements_actifs')."</th>"
					."<th colspan='2'>"._T('abonnement:abonnements_nouveaux')."</th>"
					."<th>"._T('abonnement:abonnements_resilies')."</th>"
				."</tr><tr>"
					."<th></th>"
					."<th>"._T('abonnement:info_abonnes')."</th>"
					."<th>"._T('abonnement:info_abonnements')."</th>"
					."<th>"._T('abonnement:info_total')."</th>"
					."<th>"._T('abonnement:info_nouveaux_dont')."</th>"
					."<th></th>"
				."</tr>"
			."</thead>";

	$jours = sql_allfetsel('*', 'spip_abonnements_stats', 'date >='. sql_quote($jm7).' AND date <'.sql_quote($j), '', 'date DESC');
	
	$lignes = "";
	
	foreach ($jours as $jour) {
		$date = affdate_jourcourt($jour['date']);
		$lignes .= abonnements_une_ligne($date, $jour, $offres_vues);
	}
	
	$texte .= '<h2>'._T('abonnement:titre_derniers_jours_nb', array('nb' => 7)).'</h2>';
	
	$texte .= '<table class="spip">';
	$texte .= $thead;
	$texte .= $lignes;
	$texte .= '</table>';
	
	//
	// Statistiques des 4 dernières semaines
	// 
	$off = -date('w', strtotime('-1 day', $now));
	$lignes = '';
	
	for ($i = 0; $i < 4; $i++) {
		$j = date('Y-m-d', strtotime($off . ' day', $now));
		$off -= 7;
		$jm7 = date('Y-m-d', strtotime($off . ' day', $now));
		
		$jours = sql_allfetsel('*', 'spip_abonnements_stats', 'date >='.sql_quote($jm7).' AND date <'.sql_quote($j), '', 'date DESC');
		
		$total = abonnements_somme_lignes($jours);
		
		$lignes .= abonnements_une_ligne("Semaine du " . date('d/m', strtotime($jm7)), $total, $offres_vues);
	}
	
	$texte .= '<h2>'._T('abonnement:titre_dernieres_semaines_nb', array('nb' => 4)).'</h2>';
	$texte .= '<table class="spip">';
	$texte .= $thead;
	$texte .= $lignes;
	$texte .= '</table>';
	
	// 
	// Statistiques des $nb_mois derniers mois
	// 
	$lignes = "";
	$jm1 = date('Y-m-01', $now);
	
	for ($i = 0; $i<$nb_mois; $i++){
		$jm1 = date('Y-m-01', strtotime('-15 day', strtotime($jm1)));
		$jm31 = date('Y-m-31', strtotime($jm1));
		
		$jours = sql_allfetsel('*', 'spip_abonnements_stats', 'date >=' . sql_quote($jm1) . ' AND date <=' . sql_quote($jm31), '', 'date DESC');
		
		$total = abonnements_somme_lignes($jours);
		
		$lignes .= abonnements_une_ligne(ucfirst(affdate_mois_annee($jm1)), $total, $offres_vues);
	}
	
	$texte .= '<h2>'._T('abonnement:titre_derniers_mois_nb', array('nb' => $nb_mois)).'</h2>';
	$texte .= '<table class="spip">';
	$texte .= $thead;
	$texte .= $lignes;
	$texte .= '</table>';

	$t = '';
	ksort($offres_vues);
	
	foreach (array_keys($offres_vues) as $id_abonnements_offre){
		$reference = sql_getfetsel('reference', 'spip_abonnements_offres', 'id_abonnements_offre='.$id_abonnements_offre);
		$t .= "Offre n<sup>o</sup> $id_abonnements_offre&nbsp;: "._T('abonnement:abonnement_reference_traduction_'.$reference)."<br />";
	}
	
	if ($t){
		$t = "<p>$t</p>";
	}

	return $t . $texte;
}



function abonnements_une_ligne($titre, $row, &$seen) {
	$ligne = '<tr>'."\n"
				.'<td>'.$titre.'</td>'."\n"
				.'<td>'.(intval($row['nb_abonnes']) ? $row['nb_abonnes'] : '0').'</td>'."\n"
				.'<td>'.(intval($row['nb_abonnements']) ? $row['nb_abonnements'] : '0').'</td>'."\n";
	
	//
	$ventil = json_decode($row['ventil_abonnements_plus'], true);
	ksort($ventil);
	$t = '';
	
	foreach ($ventil as $id => $nb) {
		$t .= "<br />Offre n<sup>o</sup> $id&nbsp;: $nb";
		$seen[$id] = true;
	}
	
	$ligne .= '<td>'.(intval($row['nb_abonnements_plus']) ? '+'.$row['nb_abonnements_plus']."<small>$t</small>" : '0').'</td>'."\n";
	
	//
	$ventil = json_decode($row['ventil_abonnements_new'], true);
	ksort($ventil);
	$t = '';
	
	foreach ($ventil as $id => $nb) {
		$t .= "<br />Offre n<sup>o</sup> $id&nbsp;: $nb";
		$seen[$id] = true;
	}
	
	$ligne .= '<td>'.(intval($row['nb_abonnements_new']) ? '+ '.$row['nb_abonnements_new']."<small>$t</small>" : '0').'</td>'."\n";
	
	//
	$ventil = json_decode($row['ventil_abonnements_moins'], true);
	ksort($ventil);
	$t = '';
	
	foreach ($ventil as $id => $nb) {
		$t .= "<br />Offre n<sup>o</sup> $id&nbsp;: $nb";
		$seen[$id] = true;
	}
	
	$ligne .= '<td>'.(intval($row['nb_abonnements_moins']) ? '- '.$row['nb_abonnements_moins']."<small>$t</small>" : '0').'</td>'."\n";
	$ligne .= '</tr>'."\n";
	
	return $ligne;
}



function abonnements_somme_lignes($rows){
	$total = array(
		'nb_abonnes' => 0,
		'nb_abonnements' => 0,
		'nb_abonnements_new' => 0,
		'nb_abonnements_plus' => 0,
		'nb_abonnements_moins' => 0,
		'ventil_abonnements' => array(),
		'ventil_abonnements_new' => array(),
		'ventil_abonnements_plus' => array(),
		'ventil_abonnements_moins' => array(),
	);
	
	$first = reset($rows);
	$total['nb_abonnes'] = $first['nb_abonnes'];
	$total['nb_abonnements'] = $first['nb_abonnements'];
	$total['ventil_abonnements'] = $first['ventil_abonnements'];
	
	foreach ($rows as $row){
		foreach (array('abonnements_new', 'abonnements_plus', 'abonnements_moins') as $quoi){
			$total['nb_' . $quoi] += $row['nb_' . $quoi];
			
			if ($ventil = json_decode($row['ventil_' . $quoi], true)){
				foreach ($ventil as $id => $nb){
					
					if (!isset($total['ventil_' . $quoi])){
						$total['ventil_' . $quoi] = 0;
					}
					
					$total['ventil_' . $quoi][$id] += $nb;
				}
			}
		}
	}
	
	foreach (array('abonnements_new', 'abonnements_plus', 'abonnements_moins') as $quoi){
		$total['ventil_' . $quoi] = json_encode($total['ventil_' . $quoi]);
	}
	
	return $total;
}
