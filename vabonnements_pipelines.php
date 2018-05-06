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
	
	// Activer les abonnements payés et dont la date de début est celle du jour
	$taches_generales['vabonnements_activer_abonnements'] = 3600 * 12;
	
	return $taches_generales;
}
