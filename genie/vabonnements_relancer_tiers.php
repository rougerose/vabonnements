<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

/**
 * Relancer le bénéficiaire d'un abonnement offert qui n'aurait pas répondu
 * au premier mail d'invitation (est-ce possible ???).
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
	include_spip('inc/vabonnements_relance');
	
	//$timestamp = strtotime('+3 days');
	$check = date('Y-m-d', $timestamp);
	
	$relances = vabonnements_get_relances();
	$premiere_relance = reset($relances);
	$date = vabonnements_date_relance($premiere_relance, $timestamp);
	
	// marquer en première relance les abonnements offerts dont la date_debut 
	// est antérieure à la date calculée et qui ont été marqués à relance = 0
	sql_updateq("spip_abonnements", array('relance' => $premiere_relance), 'statut=' . sql_quote('paye') . ' AND relance=' . sql_quote('0') . ' AND date_debut <' . sql_quote($date) . ' AND coupon <> ' .sql_quote(''));
	
	$rappels = sql_allfetsel('DISTINCT relance', 'spip_abonnements', 'statut=' . sql_quote('paye') . ' AND relance <> ' . sql_quote('off') . ' AND relance <> ' . sql_quote('') . ' AND relance > ' .sql_quote('0'));
	
	if (count($rappels)) {
		$rappels = array_map('reset', $rappels);
		
		$where = array();
		
		foreach ($rappels as $rappel) {
			$where[] = '(relance=' . sql_quote($rappel, '', 'text') 
				. ' AND date_debut < ' . sql_quote(vabonnements_date_relance($rappel, $timestamp)) . ')';
		}
		
		$where = "(" . implode(") OR (", $where) . ")";
		$where = "(($where) AND (statut=" . sql_quote('paye') . "))";
		
		$nb = _ABONNEMENTS_RELANCE_POOL;
		
		$notifications = charger_fonction('notifications', 'inc');
		include_spip('inc/vabonnements');
		include_spip('inc/autoriser');
		include_spip('action/editer_objet');
		
		while ($nb--){
			if ($row = sql_fetsel('id_abonnement, id_commande, id_auteur, date_debut, relance, log', 'spip_abonnements', $where, '', 'date_debut', '0,1')) {
				$relance = vabonnements_prochaine_relance($row['date_debut'], $timestamp);
				
				$id_payeur = sql_getfetsel('id_auteur', 'spip_commandes', 'id_commande=' . intval($row['id_commande']));
				$id_abonnement = intval($row['id_abonnement']);
				$id_message = sql_getfetsel(
					'id_message', 
					'spip_messages', 
					'id_auteur=' . intval($id_payeur)
					. ' AND destinataires=' . intval($row['id_auteur'])
					. ' AND statut=' . sql_quote('prepa')
					. ' AND type=' . sql_quote('kdo')
				);
				
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
					'id_payeur' => $id_payeur,
					'id_abonnement' => $id_abonnement,
					'id_message' => ($id_message) ? $id_message : ''
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
			return -($timestamp-3600);
		}
	}
	return 0;
}
