<?php

if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}


/**
 * Abonner un auteur
 *
 * La fonction est appelée par le plugin Commandes -- fonction instituer -- 
 * lorsque la commande est passée avec un statut payée. 
 * 
 * Attention : il n'y a pas de vérification du statut ancien de la commande.
 * Par contre, on s'assure ici que chaque ligne détails de la commande est 
 * bien en attente, donc une commande nouvelle.
 *
 * A l'issue de la distribution de l'abonnement, le statut de la ligne détails
 * est passé en 'envoyé', choix par défaut, car il n'existe pas de statut
 * intermédiaire. Ce statut ne confirme pas que l'abonnement est activé.
 * Il le sera plus tard, soit lorsque le bénéficiaire de l'abonnement offert
 * aura fait la démarche, soit à la date du début de l'abonnement.
 * 
 * 
 * @param  int $id_abonnements_offre
 * @param  array $detail contenu commandes_details
 * @param  int $commande id_commande
 * @return bool|string false ou nouveau statut "envoye" pour l'article de la commande.
 */
function distribuer_abonnements_offre_dist($id_abonnements_offre, $detail, $commande) {
	
	if ($detail['statut'] == 'attente') {
		
		// 
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
			'id_commandes_detail' => $detail['id_commandes_detail'],
			'id_auteur' => $commande['id_auteur'],
			'statut' => '',
			'mode_paiement' => $commande['mode'],
			'prix_ht_initial' => $detail['prix_unitaire_ht'], // reprendre le prix qui a ete enregistre dans la commande
			'numero_debut' => $detail['numero_debut']
			// 'abonne_uid' => $abonne_uid, // TODO: paiement récurrent
		);
		
		
		//
		// Si numero_debut contient la référence au premier numéro,
		// il s'agit alors d'un abonnement "courant".
		// Sinon c'est un abonnement offert.
		// 
		if ($detail['numero_debut']) {
			$abonnement = charger_fonction("abonner", "abonnements");
		} else {
			$abonnement = charger_fonction("offrir", "abonnements");
		}
		
		// // TODO: Paiement récurrent
		// if (isset($commande['echeances_date_debut']) and intval($commande['echeances_date_debut'])){
		// 	$options['date_debut'] = $commande['echeances_date_debut'];
		// }
		
		$nb = $detail['quantite'];
		
		while ($nb-->0) {
			$id_abonnement = $abonnement($id_abonnements_offre, $options);
		}
		// Statut "envoyé", à défaut d'être plus précis : "payé" aurait été préférable. 
		if ($id_abonnement) return 'envoye';
	}
	return false;
}
