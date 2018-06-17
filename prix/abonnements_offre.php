<?php

if (!defined("_ECRIRE_INC_VERSION")) return;

function prix_abonnements_offre_dist($id_objet, $prix_ht){
	
	include_spip('inc/config');
	$taxe = lire_config('vabonnements/taxe', 0.2);
	
	$prix = $prix_ht * (1 + $taxe);

	return $prix;
}
