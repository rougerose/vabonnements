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
	# quelques exemples
	# (que vous pouvez supprimer !)
	# 
	# $maj['create'] = array(array('creer_base'));
	#
	# include_spip('inc/config')
	# $maj['create'] = array(
	#	array('maj_tables', array('spip_xx', 'spip_xx_liens')),
	#	array('ecrire_config', 'vabonnements', array('exemple' => "Texte de l'exemple"))
	#);
	#
	# $maj['1.1.0']  = array(array('sql_alter','TABLE spip_xx RENAME TO spip_yy'));
	# $maj['1.2.0']  = array(array('sql_alter','TABLE spip_xx DROP COLUMN id_auteur'));
	# $maj['1.3.0']  = array(
	#	array('sql_alter','TABLE spip_xx CHANGE numero numero int(11) default 0 NOT NULL'),
	#	array('sql_alter','TABLE spip_xx CHANGE texte petit_texte mediumtext NOT NULL default \'\''),
	# );
	# ...

	$maj['create'][] = array('maj_tables', array(
		'spip_abonnements_offres',
		'spip_abonnements',
		'spip_commandes_details',
		'spip_rubriques')
	);
	
	cextras_api_upgrade(vabonnements_declarer_champs_extras(), $maj['create']);
	
	$maj['1.2.0'][] = array('sql_alter', 'TABLE spip_abonnements_offres CHANGE titre titre text NOT NULL DEFAULT \'\'');
	
	$maj['1.3.0'][] = array('sql_alter', 'TABLE spip_abonnements_offres ADD COLUMN reference tinytext NOT NULL DEFAULT \'\' AFTER descriptif');
	
	$maj['1.4.0'][] = array('maj_tables', array('spip_commandes_details'));
	
	$maj['1.5.0'][] = array('maj_tables', array('spip_rubriques'));

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
	# quelques exemples
	# (que vous pouvez supprimer !)
	# sql_drop_table('spip_xx');
	# sql_drop_table('spip_xx_liens');

	// champs du plugin
	// sql_drop_table('spip_abonnements_offres');
	// sql_drop_table('spip_abonnements');
	// sql_alter('TABLE spip_commandes_details DROP numero_debut');
	// sql_alter('TABLE spip_commandes_details DROP numero_fin');

	// champs extra du plugin
	cextras_api_vider_tables(vabonnements_declarer_champs_extras());

	# Nettoyer les liens courants (le génie optimiser_base_disparus se chargera de nettoyer toutes les tables de liens)
	// sql_delete('spip_documents_liens', sql_in('objet', array('abonnements_offre', 'abonnement')));
	// sql_delete('spip_mots_liens', sql_in('objet', array('abonnements_offre', 'abonnement')));
	// sql_delete('spip_auteurs_liens', sql_in('objet', array('abonnements_offre', 'abonnement')));
	
	# Nettoyer les versionnages et forums
	// sql_delete('spip_versions', sql_in('objet', array('abonnements_offre', 'abonnement')));
	// sql_delete('spip_versions_fragments', sql_in('objet', array('abonnements_offre', 'abonnement')));
	// sql_delete('spip_forum', sql_in('objet', array('abonnements_offre', 'abonnement')));

	effacer_meta($nom_meta_base_version);
}
