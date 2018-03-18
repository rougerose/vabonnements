<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Ajouter la référence à la rubrique d'un numéro 
 * en utilisant le nombre du titre : 
 * Vacarme XX aura pour référence v00XX
 * 
 */
function genie_vabonnements_referencer_numeros_dist() {
	include_spip('base/abstract_sql');

	// identifier les rubriques sans référence
	$numeros = sql_allfetsel('id_rubrique', 'spip_rubriques', 'id_parent=115 and reference='.sql_quote(''));

	if (count($numeros)) {
		include_spip('inc/vabonnements_numero');
		foreach ($numeros as $numero => $id_rubrique) {
			$res = vabonnements_numero_referencer($id_rubrique['id_rubrique']);
		
			if ($res) {
				spip_log("Erreur lors de la mise à jour automatique (cron) des références. Message d'erreur spip : " . $res, "vabonnements_cron" . _LOG_ERREUR);
			}
		}
		return 1;

	} else {
		// rien à faire
		return 0;
	}
}
