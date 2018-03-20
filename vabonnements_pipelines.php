<?php
/**
 * Utilisations de pipelines par Vacarme Abonnements
 *
 * @plugin     Vacarme Abonnements
 * @copyright  2018
 * @author     Le Drean*Christophe
 * @licence    GNU/GPL
 * @package    SPIP\Vabonnements\Pipelines
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

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
 * Ajouter les options d'abonnement (numéro de départ et cadeau)
 * au moment de la création de la commande.
 *
 * Pipeline déclenché par action_commandes_panier()
 * 
 * @param  array $flux Flux du pipeline
 * @return array       Le flux modifié
 */
function vabonnements_post_edition($flux) {
	if (isset($flux['args']['table']) 
		AND $flux['args']['table'] == 'spip_commandes' 
		AND $flux['args']['action'] === 'remplir_commande') {
		
		$id_commande = intval($flux['args']['id_objet']);
		$commande = sql_fetsel('statut, source', 'spip_commandes', 'id_commande='.$id_commande);
		
		// La commande est 'encours' et un panier (panier#X) est dans la source
		if (strpos($commande['source'], 'panier') !== false AND $commande['statut'] == 'encours') {
			
			// Récupérer l'identifiant du panier qui n'a pas encore été effacé
			$panier = explode('#', $commande['source']);
			$id_panier = $panier[1];
			
			// Et les offres d'abonnements que le panier contient
			$offres = sql_allfetsel('*', 'spip_paniers_liens', 'id_panier='.intval($id_panier).' and objet='.sql_quote('abonnements_offre'));
			
			include_spip('action/editer_objet');
			include_spip('inc/config');
			$taxe = lire_config('vabonnements/taxe', 0.2);
			
			if (count($offres)) {
				foreach($offres as $offre) {
					$id_abonnements_offre = $offre['id_objet'];
					$options = unserialize($offre['options']);
					
					$set = array('numero_debut' => $options['numero']);
					
					// Récupérer l'offre d'abonnement qui a été 
					// enregistrée dans la commande
					$id_commandes_detail = sql_getfetsel('id_commandes_detail', 'spip_commandes_details', 'id_commande='.$id_commande.' and id_objet='.$id_abonnements_offre.' and objet='.sql_quote($offre['objet']));
					
					if ($id_commandes_detail) {
						objet_modifier('commandes_detail', $id_commandes_detail, $set);
					}
					
					// Si le cadeau existe dans le panier,
					// ajouter le produit correspondant dans la commande.
					$id_produit = intval($options['cadeau']);
					
					if ($id_produit > 0 AND $titre_produit = sql_getfetsel('titre', 'spip_produits', 'id_produit='.$id_produit)) {
						$set_produit = array(
							'id_commande' => $id_commande,
							'objet' => 'produit',
							'id_objet' => $id_produit,
							'descriptif' => $titre_produit . ' abonnements_offre#' . $id_abonnements_offre,
							'quantite' => 1,
							'prix_unitaire_ht' => 0, // c'est un cadeau, le prix "réel" n'est pas utilisé.
							'taxe' => $taxe,
							'reduction' => 0,
							'statut' => 'attente'
						);
						
						// Le produit n'a pas déjà été ajouté à la commande ?
						$where = array();
						foreach ($set_produit as $k => $w) {
							if (in_array($k, array('id_commande', 'objet', 'id_objet'))) {
								$where[] = "$k=" . sql_quote($w);
							}
						}
						
						if (!$id_commandes_detail = sql_getfetsel('id_commandes_detail', 'spip_commandes_details', $where)) {
							// créer la ligne relative au cadeau
							$id_commandes_detail = objet_inserer('commandes_detail');
							// ajouter toutes les données du produit
							objet_modifier('commandes_detail', $id_commandes_detail, $set_produit);
						}
					}
				}
			}
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
	// référencer les rubriques correspondant aux numéros, 1 fois par heure. 
	$taches_generales['vabonnements_referencer_numeros'] = 3600;
	return $taches_generales;
}
