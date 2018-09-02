<?php

if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}


/**
 * Distribution d'un abonnement
 *
 * La fonction est appelée par le plugin Commandes -- fonction instituer -- 
 * lorsque la commande est passée avec un statut payée. 
 *
 * Si l'abonnement est personnel, le paiement de la commande entraîne 
 * l'activation de l'abonnement. 
 *
 * Si l'abonnement est offert, le statut de l'abonnement est modifié en "payé".
 * C'est le bénéficiaire qui déclenchera l'activation.
 *
 * Ensuite, les numéros qu'il est possible d'envoyer immédiatement, car 
 * déjà disponibles (précédent numéro ? numéro en cours ?)
 * sont notés dans la table envois_commandes.
 * 
 * Il n'y a pas de vérification du statut ancien de la commande.
 * Par contre, on s'assure ici que chaque ligne détails de la commande est 
 * bien en attente, donc une commande nouvelle.
 *
 * Contraitement à ce que prévoit le workflow Commandes,
 * le statut de la ligne de détail de commande n'est pas modifié 
 * et laissé en attente. Ce statut sera modifié par le plugin Envois.
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
		$envoi = false;
		
		
		// TODO: [02/09/2018] ici il faudra vérifier que l'abonnement n'existe 
		// pas déjà. En effet, si l'on créé un abonnement dans l'espace privé,
		// le formulaire créé la commande (en attente de paiement pour un 
		// virement ou un chèque ou payée si abonnement gratuit) et l'abonnement
		// dans la foulée.
		$abonnement = sql_fetsel(
			'id_abonnement, log', 
			'spip_abonnements', 
			'id_commande='.$id_commande
		);
		
		$id_abonnement = $abonnement['id_abonnement'];
		
		// Abonnement personnel ou offert ?
		if ($detail['numero_debut']) {
			$log_distribution = "L'abonnement est maintenant en statut «actif» après le paiement de la commande n°$id_commande (mode de paiement : $mode).";
			$statut = 'actif';
			$envoi = true;
		} else {
			$log_distribution = "L'abonnement est maintenant en statut «payé» après le paiement de la commande n°$id_commande (mode de paiement : $mode). Il est en attente d'activation par son bénéficiaire.";
			$statut = 'paye';
		}
		
		autoriser_exception('modifier', 'abonnement', $id_abonnement);
		$log = $abonnement['log'];
		$log .= vabonnements_log($log_distribution);
		
		$set = array('statut' => $statut, 'log' => $log);
		
		$res = objet_modifier('abonnement', $id_abonnement, $set);
		
		// noter les envois à faire
		if ($envoi) {
			$noter_envoi = charger_fonction('noter_envoi', 'action');
			$noter_envoi($id_commande, 'abonnements_offre', $id_abonnements_offre);
		}
		
		autoriser_exception('modifier', 'abonnement', $id_abonnement, false);
		
		return 'attente';
	}
}
