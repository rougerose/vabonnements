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


/*
 * Un fichier de pipelines permet de regrouper
 * les fonctions de branchement de votre plugin
 * sur des pipelines existants.
 */





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
