<?php

if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}


/**
 * Abonner un auteur
 *
 * La fonction est appelée par le plugin Commandes
 * @param  int $id_abonnements_offre
 * @param  array $detail contenu commandes_details
 * @param  int $commande id_commande
 * @return bool|string false ou nouveau statut "envoye" pour l'article de la commande.
 */
function distribuer_abonnements_offre_dist($id_abonnements_offre, $detail, $commande) {
	if ($detail['statut'] == 'attente') {
		// TODO: Ce qui suit est nécessaire pour un abonnement en paiement récurrent. 
		// Le code est laissé ici pour mémoire, quand ce sera utile. 
		// 
		// $transaction = sql_fetsel('*','spip_transactions','statut='.sql_quote('ok').' AND id_commande='.intval($commande['id_commande']),'','id_transaction','0,1');
		// $abonne_uid = '';
		// if ($transaction) {
		// 	if (isset($transaction['abo_uid']) and $transaction['abo_uid']) {
		// 		$abonne_uid = $transaction['abo_uid'];
		// 	}
		// 	elseif (isset($transaction['id_transaction']) and $transaction['id_transaction']) {
		// 		$abonne_uid = $transaction['id_transaction'];
		// 	}
		// }
		
		$options = array(
			'id_commande' => $commande['id_commande'],
			'id_auteur' => $commande['id_auteur'],
			'statut' => 'actif',
			'mode_paiement' => $commande['mode'],
			// 'abonne_uid' => $abonne_uid, // TODO: paiement récurrent
			'prix_ht_initial' => $detail['prix_unitaire_ht'], // reprendre le prix qui a ete enregistre dans la commande
			'numero_debut' => $detail['numero_debut']
		);
		
		//
		// 2 options possibles : 
		// - numero_debut = "coupon", alors l'abonnement est offert. Il s'agit
		// d'appeler la fonction abonnements/offrir.php
		// - numero_debut est égal à une chaîne du type v0000, alors
		// l'abonnement est un "régulier" et souscrit pour l'auteur lui-même.
		// Il s'agit d'appeler la fonction abonnements/abonner.php
		
		if ($detail['numero_debut'] == 'coupon') {
			$abonnement = charger_fonction("offrir", "abonnements");
		} else {
			$abonnement = charger_fonction("abonner", "abonnements");
		}
		
		// // TODO: Paiement récurrent
		// if (isset($commande['echeances_date_debut']) and intval($commande['echeances_date_debut'])){
		// 	$options['date_debut'] = $commande['echeances_date_debut'];
		// }
		
		$nb = $detail['quantite'];
		
		while ($nb-->0) {
			$abonnement($id_abonnements_offre, $options);
		}
		
		return 'envoye';
	}
	
	return false;
}
