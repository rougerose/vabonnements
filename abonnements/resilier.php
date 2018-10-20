<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

/**
 * [abonnements_resilier_dist description]
 *
 * Fonction reprise de https://github.com/nursit/abos/blob/master/abos/resilier.php
 * dans une version simplifiée (sans prendre en compte les paiements récurrents)
 * 
 * @param  int $id
 * @param  array  $options
 *   bool immediat
 *   string message
 *   bool notify_bank
 * @return bool
 */
function abonnements_resilier_dist($id, $options = array()) {
	$abo_log = (isset($options['message']) ? $options['message'] : '');
	$immediat = (isset($options['immediat']) ? $options['immediat'] : false);
	// $notify_bank = (isset($options['notify_bank']) ? $options['notify_bank'] : true);
	// $erreur = (isset($options['erreur']) ? $options['erreur'] : false);
	
	$id_abonnement = $id;
	$row = sql_fetsel('*', 'spip_abonnements', 'id_abonnement='.intval($id_abonnement));
	
	$ok = true;
	
	if ($ok){
		$set = array();
		$now = date('Y-m-d H:i:s');
		if ($immediat){
			$set['statut'] = sql_quote('resilie');
			if (!intval($row['date_fin']) OR $row['date_fin']>$now){
				$set['date_fin'] = sql_quote($now);
			}
		}

		// plus de relance pour un abonnement resilie
		$set['relance'] = sql_quote('off');

		if ($abo_log){
			include_spip('inc/vabonnements');
			$set["log"] = sql_quote($row['log'] . vabonnements_log($abo_log));
		}

		sql_update("spip_abonnements", $set, "id_abonnement=" . intval($id_abonnement));
		
		spip_log($log = "resiliation abonnement $id/$id_abonnement : " . var_export($set, true), 'vabonnements_resiliations' . _LOG_INFO_IMPORTANTE);
	}

	return $ok;
}
