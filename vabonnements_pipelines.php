<?php

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

function vabonnements_pre_edition($flux) {
	// 
	// Une commande change de statut, 
	// de 'encours' à 'attente' (réglement chèque ou virement) 
	// ou 'paye' (réglement CB ou gratuit) : 
	// il faut alors créer l'abonnement.
	// 
	if ($flux['args']['table'] == 'spip_commandes'
		&& $flux['args']['action'] == 'instituer'
		&& $flux['args']['statut_ancien'] == 'encours'
		&& in_array($flux['data']['statut'], array('attente', 'paye'))
	) {
		$id_commande = $flux['args']['id_objet'];
		$commande = sql_fetsel('*', 'spip_commandes', 'id_commande='.$id_commande);
		
		if ($commande) {
			if ($details = sql_allfetsel(
				'*',
				'spip_commandes_details',
				'id_commande='.$id_commande .' AND objet='.sql_quote('abonnements_offre')
			)) {
				foreach ($details as $detail) {
					if ($ajouter = charger_fonction('inserer_abonnement', 'abonnements')) {
						$ajouter($detail['id_objet'], $detail, $commande);
					}
				}
			}
		}
	}
	
	return $flux;
}



/**
 * Activation des abonnements personnels
 *
 * Lorsqu'une commande passe du statut "payée" à "envoyée", il s'agit alors
 * de vérifier si un abonnement est présent dans la commande. Dans cette 
 * hypothèse, il est également modifié pour passer du statut "payé" à "actif". 
 *
 * Attention : ce traitement automatique ne concerne que les abonnements
 * personnels (pris pour soi). Les abonnements offerts font l'objet
 * d'un traitement spécifique (via le cron).
 * 
 * @param  array $flux
 * @return array
 */
// function vabonnements_post_edition($flux) {
// 	if ($flux['args']['table'] == 'spip_commandes'
// 		&& $flux['args']['action'] == 'instituer'
// 		&& $flux['args']['statut_ancien'] == 'paye'
// 		&& $flux['data']['statut'] == 'envoye')
// 	{
// 		include_spip('inc/autoriser');
// 		include_spip('action/editer_objet');
// 		include_spip('inc/vabonnements');
// 
// 		$id_commande = intval($flux['args']['id_objet']);
// 
// 		// 
// 		// prendre uniquement les abonnements "perso" (champ numero_debut est
// 		// vide) et non les abonnements offerts
// 		// 
// 		$abos = sql_allfetsel('*', 'spip_commandes_details', 
// 			'id_commande=' . $id_commande
// 			. ' AND objet=' . sql_quote('abonnements_offre')
// 			. ' AND numero_debut <> ' . sql_quote('')
// 		);
// 
// 		foreach ($abos as $abo) {
// 			$abonnement = sql_fetsel('*', 'spip_abonnements',
// 				'id_abonnements_offre=' . intval($abo['id_objet'])
// 				. ' AND id_commande=' . $id_commande
// 				. ' AND statut=' . sql_quote('paye')
// 				. ' AND coupon=' . sql_quote('')
// 			);
// 
// 			if ($abonnement) {
// 				$id_abonnement = intval($abonnement['id_abonnement']);
// 
// 				autoriser_exception('modifier', 'abonnement', $id_abonnement);
// 				autoriser_exception('instituer', 'abonnement', $id_abonnement);
// 
// 				$log_activation = "La commande n°$id_commande est envoyée : ";
// 				$log_activation .= "activation automatique de l'abonnement";
// 				$log = $abonnement['log'];
// 				$log .= vabonnements_log($log_activation);
// 
// 				$erreur = objet_modifier('abonnement', $id_abonnement, array('statut' => 'actif', 'log' => $log));
// 
// 				if ($erreur) {
// 					spip_log("L'Abonnement n°$id_abonnement n'a pas pu être activé. Message d'erreur : " . $erreur, 'vabonnements_activer'._LOG_ERREUR);
// 				} else {
// 					spip_log("L'Abonnement n°$id_abonnement est activé", 'vabonnements_activer'._LOG_INFO_IMPORTANTE);
// 				}
// 
// 				autoriser_exception('instituer', 'abonnement', $id_abonnement, false);
// 				autoriser_exception('modifier', 'abonnement', $id_abonnement, false);
// 			}
// 		}
// 	}
// 	return $flux;
// }



/**
 * Compter le nombre d'abonnements d'un auteur
 *
 * @param array $flux
 * @return array
 * 
 */
function vabonnements_compter_contributions_auteur($flux) {
	$in = sql_in('statut', array('actif', 'prepa'));
	if ($id_auteur = intval($flux['args']['id_auteur'])
		AND $cpt = sql_countsel("spip_abonnements AS A", "A.id_auteur=" . intval($id_auteur) . ' AND ' . $in)
	){
		$contributions = singulier_ou_pluriel($cpt, 'abonnement:info_1_abonnement', 'abonnement:info_nb_abonnements');
		$flux['data'][] = $contributions;
	}
	return $flux;
}

/**
 * Afficher les abonnements d'un auteur sur sa page privée
 *
 * Code repris du plugin Abos
 * 
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 * 
 */
function vabonnements_affiche_auteurs_interventions($flux) {
	if ($id_auteur = intval($flux['args']['id_auteur'])){

		$ins = recuperer_fond('prive/squelettes/inclure/abonnements-auteur', array(
			'id_auteur' => $id_auteur,
			'titre' => _T('abonnement:info_abonnements_auteur')
		), array('ajax' => true));
		$mark = '<!--bank-->';
		if (($p = strpos($flux['data'], $mark))!==false){
			$flux['data'] = substr_replace($flux['data'], $ins, $p+strlen($mark), 0);
		} else {
			$flux['data'] .= $ins;
		}

	}
	return $flux;
}



/**
 * Optimiser la base de données
 *
 * Supprime les objets à la poubelle.
 * Supprime les objets à la poubelle.
 *
 * @pipeline optimiser_base_disparus
 * @param  array $flux Données du pipeline
 * @return array       Données du pipeline
 */
function vabonnements_optimiser_base_disparus($flux) {

	sql_delete('spip_abonnements_offres', "statut='poubelle' AND maj < " . $flux['args']['date']);

	sql_delete('spip_abonnements', "statut='poubelle' AND maj < " . $flux['args']['date']);

	return $flux;
}


function vabonnements_taches_generales_cron($taches_generales) {
	
	// Inviter les bénéficiaires d'un abonnement offert à activer leur abonnement
	// Action déclenchées toutes les 12 heures.
	$taches_generales['vabonnements_inviter_tiers'] = 3600 * 12;
	
	// Relancer les bénéficaires qui tardent à activer leur abonnement
	$taches_generales['vabonnements_relancer_tiers'] = 3600 * 12;
	
	// Relance des abonnements à échéance
	$taches_generales['vabonnements_relancer_echeances'] = 3600 * 12;
	
	// Maintenance des abonnements 
	$taches_generales['vabonnements_reparer'] = 3600 * 12;
	
	return $taches_generales;
}
