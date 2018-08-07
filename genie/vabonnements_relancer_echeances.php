<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

/**
 * Relancer les abonnements à échéance selon le planning défini 
 * dans la configuration du plugin.
 * @param  int $t timestamp
 * @return int
 */
function genie_vabonnements_relancer_echeances_dist($t) {
	$now = time();
	
	include_spip('inc/vabonnements_relance');
	$relances = vabonnements_get_relances('echeances');
	
	if (!$relances) {
		return 0;
	}
	
	$premiere_relance = reset($relances);
	$date_fin = vabonnements_date_relance($premiere_relance, $now);
	
	
	// Noter les abonnements actifs qui ont une date d'échéance dans N jours et
	// qui n'ont pas encore été relancés.
	sql_updateq('spip_abonnements', array('relance' => $premiere_relance), 'date_fin > date_debut AND coupon='.sql_quote('').' AND relance='.sql_quote('').' AND date_fin <='.sql_quote($date_fin).' AND statut='.sql_quote('actif'));
	
	// $abo_premiere_relance = sql_allfetsel('id_abonnement', 'spip_abonnements', 'date_fin > date_debut AND coupon='.sql_quote('').' AND relance='.sql_quote('').' AND date_fin <='.sql_quote($date_fin).' AND statut='.sql_quote('actif'));
	
	// Tous les rappels en cours sur les abonnements actifs 
	// (hors abonnements offerts à des tiers et qui sont en relance)
	$rappels = sql_allfetsel('DISTINCT relance', 'spip_abonnements', 'statut='.sql_quote('actif').' AND coupon='.sql_quote('').' AND relance <>'.sql_quote('off').' AND relance <>'.sql_quote(''));
	
	if (count($rappels)) {
		$rappels = array_map('reset', $rappels);
		
		$where = array();
		
		foreach ($rappels as $rappel){
			$where[] = "(relance=" . sql_quote($rappel, '', 'text') . " AND date_fin > date_debut AND date_fin < " .sql_quote(vabonnements_date_relance($rappel, $now)) . ")";
		}
		
		$where = "(" . implode(") OR (", $where) . ")";
		$where = "(($where) AND (statut=" . sql_quote('actif') . "))";
		
		$nb = _ABONNEMENTS_RELANCE_POOL;
		
		$notifications = charger_fonction('notifications', 'inc');
		include_spip('inc/vabonnements');
		include_spip('inc/autoriser');
		include_spip('action/editer_objet');
		
		while ($nb--){
			if ($row = sql_fetsel('id_abonnement, id_auteur, date_fin, relance', 'spip_abonnements', $where, '', 'date_debut', '0,1')) {
				$relance = vabonnements_prochaine_relance('echeances', $row['date_fin'], $now);
				
				$id_abonnement = intval($row['id_abonnement']);
				$id_auteur = intval($row['id_auteur']);
				
				autoriser_exception('modifier', 'abonnement', $id_abonnement);
				$log_rappel = "Envoi de l'email de relance avant échéance (date de fin d'abonnement : ".$row['date_fin']."). Prochaine relance à ".$relance." jour(s) de l'échéance";
				$log = $row['log'];
				$log .= vabonnements_log($log_rappel);
				
				// 
				// Ajouter log et noter que le rappel est fait 
				// 
				$erreur = objet_modifier('abonnement', $id_abonnement, array('log' => $log, 'relance' => $relance));
				
				if ($erreur) {
					spip_log("genie_abonnements_relancer_echeance abonnement #$id_abonnement. Message d'erreur à l'enregistrement de la relance suivante : ".$erreur, 'vabonnements_relancer_echeance'._LOG_ERREUR);
				}
				autoriser_exception('modifier', 'abonnement', $id_abonnement, false);
				
				$options = array('id_abonnement' => $id_abonnement);
				$notifications('abonnement_relancer_echeance', $id_auteur, $options);
				
			} else {
				$nb = 0;
			}
		}
		
		// 
		// Si trop de relances, demander la main a nouveau
		// 
		if (($n = sql_countsel('spip_abonnements', $where)) > 2 * _ABONNEMENTS_RELANCE_POOL) {
			return -($now-3600);
		}
	}
	return 0;
}
