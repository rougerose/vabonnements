<?php


if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

/**
 * Maintenance des abonnements
 *
 * - Mettre à jour les statistiques
 * - Résilier les abonnements à échéance.
 *
 * Remarque : la résiliation à l'échéance adopte un principe simplifié. 
 * Les abonnements résiliés sont ceux qui arrivent à échéance. Si le paiement
 * récurrent est pris en compte, il faudra alors revoir ce fonctionnement. 
 * Voir https://github.com/nursit/abos/blob/master/abos/repair.php
 * 
 * @return void
 */
function abonnements_reparer_dist() {
	$compter = charger_fonction('compter', 'abonnements');
	$compter();
	
	$resilier = charger_fonction('resilier', 'abonnements');
	$date_jour = date('Y-m-d 23:59:59', strtotime('-1 day'));
	
	$abonnements = sql_allfetsel('id_abonnement', 'spip_abonnements', 'statut='.sql_quote('actif').' AND date_fin <='.sql_quote($date_jour).' AND date_fin >= date_debut');
	
	foreach ($abonnements as $abonnement) {
		$log = "Résiliation automatique de l'abonnement #".$abonnement['id_abonnement']." à sa date d'échéance";
		$resilier($abonnement['id_abonnement'], array('immediat' => true, 'message' => $log));
		spip_log($log, 'vabonnements_resiliations' . _LOG_INFO_IMPORTANTE);
	}
}
