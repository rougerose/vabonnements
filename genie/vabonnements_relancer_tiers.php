<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

/**
 * Relancer le bénéficiaire d'un abonnement offert qui n'aurait pas répondu
 * au premier mail d'invitation.
 *
 * Le rythme des relances est défini dans le configuration du plugin.
 *
 * La fonction est copiée depuis le plugin Abos de Nursit et en particulier
 * https://github.com/nursit/abos/blob/master/genie/abos_relancer.php
 * 
 * @param  int $timestamp
 * @return int
 */
function genie_vabonnements_relancer_tiers_dist($timestamp) {
	$d = strtotime('+1 day');
	$now =  date('U', $d);
	
	include_spip('inc/vabonnements_relance');
	$relances = vabonnements_get_relances('tiers');
	
	if (!$relances) {
		return 0;
	}
	
	$premiere_relance = reset($relances);
	$date = vabonnements_date_relance($premiere_relance, $now);
	
	// marquer en première relance les abonnements offerts dont la date_message 
	// est antérieure à la date de référence et qui ont été marqués à relance = 0
	// 
	// Important : on ne relance que les abonnements *payés*
	// 
	$abonnements_relances = sql_allfetsel(
		'*',
		'spip_abonnements',
		'statut='.sql_quote('paye')
			.' AND offert='.sql_quote('oui')
			.' AND relance='.sql_quote('0')
			.' AND relance <>'.sql_quote('off')
			.' AND date_message <'.sql_quote($date)
			.' AND coupon <> '.sql_quote('')
	);
	
	foreach ($abonnements_relances as $abonnement) {
		sql_updateq('spip_abonnements', array('relance' => $premiere_relance), 'id_abonnement='.intval($abonnement['id_abonnement']));
	}

	$rappels = sql_allfetsel(
		'DISTINCT relance', 
		'spip_abonnements', 
		'statut='.sql_quote('paye')
			.' AND offert='.sql_quote('oui')
			.' AND relance <> '.sql_quote('off')
			.' AND relance <> '.sql_quote('')
			.' AND relance > ' .sql_quote('0')
		);
	
	if (count($rappels)) {
		$rappels = array_map('reset', $rappels);
		
		$where = array();
		
		foreach ($rappels as $rappel) {
			$where[] = '(relance='.sql_quote($rappel, '', 'text') 
				.' AND date_message < '.sql_quote(vabonnements_date_relance($rappel, $now)).')';
		}
		
		$where = "(".implode(") OR (", $where).")";
		$where = "(($where) AND (statut=".sql_quote('paye')."))";
		
		$nb = _ABONNEMENTS_RELANCE_POOL;
		
		$notifications = charger_fonction('notifications', 'inc');
		include_spip('inc/vabonnements');
		include_spip('inc/autoriser');
		include_spip('action/editer_objet');
		
		while ($nb--){
			if ($row = sql_fetsel('id_abonnement, id_commande, id_auteur, date_message, relance, log', 'spip_abonnements', $where, '', 'date_message', '0,1')) {
				$relance = vabonnements_prochaine_relance('tiers', $row['date_message'], $now);
				
				$id_abonnement = intval($row['id_abonnement']);
				
				autoriser_exception('modifier', 'abonnement', $id_abonnement);
				
				$log_rappel = "Envoi de l'email de rappel. Relance après ".$row['relance']." jour(s)";
				$log = $row['log'];
				$log .= vabonnements_log($log_rappel);
				
				// 
				// Ajouter log et noter que le rappel est fait 
				// 
				$erreur = objet_modifier('abonnement', $id_abonnement, array('log' => $log, 'relance' => $relance));
				
				spip_log("genie_vabonnements_relancer id_abonnement : " . $id_abonnement . ", date : " . $row['date_debut'] . ", relance : " . $row['relance'], 'vabonnements_relancer_tiers'._LOG_INFO_IMPORTANTE);
				
				if ($erreur) {
					spip_log("genie_abonnements_relancer_tiers abonnement #$id_abonnement. Message d'erreur à l'enregistrement de la relance suivante : ".$erreur, 'vabonnements_relancer_tiers'._LOG_ERREUR);
				}
				
				autoriser_exception('modifier', 'abonnement', $id_abonnement, false);
				
				$options = array(
					'id_abonnement' => $id_abonnement,
				);
				
				$notifications('abonnement_relancer_tiers', $row['id_auteur'], $options);

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
