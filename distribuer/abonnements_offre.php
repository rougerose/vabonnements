<?php

if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}


/**
 * Modifier le statut d'un abonnement prop => paye
 *
 * La fonction est appelée par le plugin Commandes -- fonction instituer -- 
 * lorsque la commande est passée avec un statut payée. 
 * 
 * Il n'y a pas de vérification du statut ancien de la commande.
 * Par contre, on s'assure ici que chaque ligne détails de la commande est 
 * bien en attente, donc une commande nouvelle.
 *
 * Dans un premier temps, le statut de la ligne de détail de commande n'est 
 * pas modifié et laissé en attente (contrairement à ce que prévoit 
 * le workflow des commandes).
 * 
 * @param  int $id_abonnements_offre
 * @param  array $detail contenu commandes_details
 * @param  int $commande id_commande
 * @return bool|string false ou nouveau statut "envoye" pour l'article de la commande.
 */
function distribuer_abonnements_offre_dist($id_abonnements_offre, $detail, $commande) {
	
	if ($detail['statut'] == 'attente') {
		include_spip('inc/autoriser');
		include_spip('action/editer_objet');
		include_spip('inc/vabonnements');
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
		// 
		$id_commande = $commande['id_commande'];
		$mode = $commande['mode'];
		
		$abonnement = sql_fetsel(
			'id_abonnement, log', 
			'spip_abonnements', 
			'id_commande='.$id_commande.' AND id_auteur='.$commande['id_auteur']
		);
		
		$id_abonnement = $abonnement['id_abonnement'];
		
		$log_paiement = "Paiement de la commande n°$id_commande (mode de paiement : $mode). Le statut de l'abonnement est modifié également";
		$log = $abonnement['log'];
		$log .= vabonnements_log($log_paiement);
		
		autoriser_exception('modifier', 'abonnement', $id_abonnement);
		
		$set = array('statut' => 'paye', 'log' => $log);
		$res = objet_modifier('abonnement', $id_abonnement, $set);
		
		autoriser_exception('modifier', 'abonnement', $id_abonnement, false);
		
		return 'attente';
	}
}
