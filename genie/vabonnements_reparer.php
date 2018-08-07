<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

/**
 * Maintenance des abonnements
 * 
 * @param  int $t timestamp
 * @return int
 */
function genie_vabonnements_reparer($t) {
	$reparer = charger_fonction('reparer', 'abonnements');
	$reparer();
	return 1;
}
