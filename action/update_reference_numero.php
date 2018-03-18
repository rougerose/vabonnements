<?php

if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}

/**
 * Mettre à jour le champ référence des numéros de Vacarme
 *
 * Cette action peut être faite depuis la page de configuration du plugin.
 * 
 * @return [type] [description]
 */
function action_update_reference_numero_dist($arg = null) {
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();
	
	include_spip('inc/autoriser');
	if (!autoriser('configurer', 'vabonnements')) {
		include_spip('inc/minipres');
		echo minipres();
		exit;
	}
	
	$rubriques = json_decode($arg, true);

	include_spip('inc/vabonnements_numero');

	foreach ($rubriques as $id_rubrique) {
		$res = 	vabonnements_numero_referencer(intval($id_rubrique));
		
		if ($res) {
			spip_log("Erreur lors de la mise à jour automatique (cron) des références. Message d'erreur spip : " . $res, "vabonnements_config" . _LOG_ERREUR);
		}
	}
}
