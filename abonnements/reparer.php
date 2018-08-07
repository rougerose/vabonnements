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
 * @return void
 */
function abonnements_reparer_dist() {
	$compter = charger_fonction('compter', 'abonnements');
	$compter();
}
