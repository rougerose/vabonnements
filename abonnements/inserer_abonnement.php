<?php 

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}


function abonnements_inserer_abonnement($id_abonnements_offre, $detail, $commande) {
	// 
	// Différencier les abonnements personnels (avec le champ numero_debut 
	// déjà renseigné) et les abonnements offerts.
	// 
	if ($detail['numero_debut']) {
		$abonnement = charger_fonction("abonner", "abonnements");
	} else {
		$abonnement = charger_fonction("offrir", "abonnements");
	}
	
	$options = array(
		'id_commande' => $commande['id_commande'],
		'id_commandes_detail' => $detail['id_commandes_detail'],
		'id_auteur' => $commande['id_auteur'],
		'mode_paiement' => $commande['mode'],
		'prix_ht_initial' => $detail['prix_unitaire_ht'], // reprendre le prix qui a ete enregistre dans la commande
		'numero_debut' => $detail['numero_debut']
	);
	
	$nb = $detail['quantite'];
	
	while ($nb-->0) {
		$id_abonnement = $abonnement($id_abonnements_offre, $options);
	}
}
