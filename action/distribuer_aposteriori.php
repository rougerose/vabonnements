<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function action_distribuer_aposteriori_dist($arg=null) {
	if (is_null($arg)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}
	$id_commande = intval($arg);
	$distribuer_commande = charger_fonction('distribuer_commande', 'action');
	
	$distribuer_commande($id_commande);
}