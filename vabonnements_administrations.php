<?php
/**
 * Fichier gérant l'installation et désinstallation du plugin Vacarme Abonnements
 *
 * @plugin     Vacarme Abonnements
 * @copyright  2018
 * @author     Le Drean*Christophe
 * @licence    GNU/GPL
 * @package    SPIP\Vabonnements\Installation
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/cextras');
include_spip('base/vabonnements');

/**
 * Fonction d'installation et de mise à jour du plugin Vacarme Abonnements.
 *
 * Vous pouvez :
 *
 * - créer la structure SQL,
 * - insérer du pre-contenu,
 * - installer des valeurs de configuration,
 * - mettre à jour la structure SQL 
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @param string $version_cible
 *     Version du schéma de données dans ce plugin (déclaré dans paquet.xml)
 * @return void
**/
function vabonnements_upgrade($nom_meta_base_version, $version_cible) {
	$maj = array();
	
	$maj['create'][] = array('maj_tables', array(
		'spip_abonnements_offres',
		'spip_abonnements',
		'spip_commandes_details',
		'spip_rubriques')
	);
	
	cextras_api_upgrade(vabonnements_declarer_champs_extras(), $maj['create']);
	
	// importer les données abonnements_offres
	include_spip('base/importer_spip_abonnements_offres');
	$maj['create'][] = array('importer_spip_abonnements_offres');

	include_spip('base/upgrade');
	maj_plugin($nom_meta_base_version, $version_cible, $maj);
}


/**
 * Fonction de désinstallation du plugin Vacarme Abonnements.
 * 
 * Vous devez :
 *
 * - nettoyer toutes les données ajoutées par le plugin et son utilisation
 * - supprimer les tables et les champs créés par le plugin. 
 *
 * @param string $nom_meta_base_version
 *     Nom de la meta informant de la version du schéma de données du plugin installé dans SPIP
 * @return void
**/
function vabonnements_vider_tables($nom_meta_base_version) {
	// champs du plugin
	sql_drop_table('spip_abonnements_offres');
	sql_drop_table('spip_abonnements');
	sql_alter('TABLE spip_commandes_details DROP numero_debut');
	sql_alter('TABLE spip_commandes_details DROP numero_fin');

	// champs extra du plugin
	cextras_api_vider_tables(vabonnements_declarer_champs_extras());

	# Nettoyer les liens courants (le génie optimiser_base_disparus se chargera de nettoyer toutes les tables de liens)
	sql_delete('spip_documents_liens', sql_in('objet', array('abonnements_offre', 'abonnement')));
	sql_delete('spip_mots_liens', sql_in('objet', array('abonnements_offre', 'abonnement')));
	sql_delete('spip_auteurs_liens', sql_in('objet', array('abonnements_offre', 'abonnement')));
	
	# Nettoyer les versionnages et forums
	sql_delete('spip_versions', sql_in('objet', array('abonnements_offre', 'abonnement')));
	sql_delete('spip_versions_fragments', sql_in('objet', array('abonnements_offre', 'abonnement')));
	sql_delete('spip_forum', sql_in('objet', array('abonnements_offre', 'abonnement')));

	effacer_meta($nom_meta_base_version);
}
