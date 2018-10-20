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
	
	$maj['create'] = array(
		array('maj_tables', array(
			'spip_abonnements_offres',
			'spip_abonnements',
			'spip_abonnements_stats')
		)
	);
	
	// importer les données abonnements_offres
	include_spip('base/importer_spip_abonnements_offres');
	$maj['create'][] = array('importer_spip_abonnements_offres');
	
	// champs extra commandes_details
	cextras_api_upgrade(vabonnements_declarer_champs_extras(), $maj['create']);
	
	// prix en decimal
	$maj['1.0.5'] = array(
		array('sql_alter', 'TABLE spip_abonnements_offres CHANGE prix_ht prix_ht DECIMAL(10,2) NOT NULL DEFAULT 0'),
		array('sql_alter', 'TABLE spip_abonnements CHANGE prix_echeance prix_echeance DECIMAL(10,2) NOT NULL DEFAULT 0')
	);
	
	$maj['1.0.6'] = array(
		array('sql_alter', 'TABLE spip_abonnements_offres CHANGE prix_ht prix_ht DECIMAL(10,2) NOT NULL DEFAULT 0')
	);
	
	// ajout des statistiques
	$maj['1.0.7'] = array(
		array('maj_tables', array('spip_abonnements_stats'))
	);
	
	$maj['1.1.0'] = array(
		array('sql_alter', 'TABLE spip_abonnements ADD offert varchar(3) default "non" not null')
	);
	
	$maj['1.1.1'] = array(
		array('sql_alter', 'TABLE spip_abonnements_stats ADD nb_abonnements bigint(21) default "0" not null')
	);
	
	$maj['1.1.2'] = array(
		array('sql_alter', 'TABLE spip_abonnements_offres CHANGE prix_ht prix_ht DECIMAL(10,3) NOT NULL DEFAULT 0'),
		array('sql_alter', 'TABLE spip_abonnements CHANGE prix_echeance prix_echeance DECIMAL(10,3) NOT NULL DEFAULT 0')
	);
	
	$maj['1.1.3'] = array(
		array('sql_alter', 'TABLE spip_abonnements ADD id_auteur_payeur bigint(21) not null default 0 AFTER id_auteur'),
		array('sql_alter', 'TABLE spip_abonnements CHANGE offert offert varchar(3) default "non" not null AFTER coupon'),
		array('sql_alter', 'TABLE spip_abonnements ADD message text not null default "" AFTER coupon'),
		array('sql_alter', 'TABLE spip_abonnements ADD date_message datetime not null default "0000-00-00 00:00:00" AFTER message')
	);
	
	$maj['1.1.4'] = array(
		array('sql_alter', 'TABLE spip_commandes_details DROP COLUMN numero_debut'),
		array('sql_alter', 'TABLE spip_commandes_details ADD options text not null default "" AFTER id_objet'),
	);
	
	// maj['1.1.5'] : Oubli de déclaration conforme du champ extra options à la mise à jour précédente
	cextras_api_upgrade(vabonnements_declarer_champs_extras(), $maj['1.1.5']);

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
	//sql_drop_table('spip_abonnements_offres');
	//sql_drop_table('spip_abonnements');
	sql_drop_table('spip_abonnements_stats');
	
	cextras_api_vider_tables(vabonnements_declarer_champs_extras());

	# Nettoyer les liens courants (le génie optimiser_base_disparus se chargera de nettoyer toutes les tables de liens)
	//sql_delete('spip_documents_liens', sql_in('objet', array('abonnements_offre', 'abonnement')));
	//sql_delete('spip_mots_liens', sql_in('objet', array('abonnements_offre', 'abonnement')));
	//sql_delete('spip_auteurs_liens', sql_in('objet', array('abonnements_offre', 'abonnement')));
	
	# Nettoyer les versionnages et forums
	//sql_delete('spip_versions', sql_in('objet', array('abonnements_offre', 'abonnement')));
	//sql_delete('spip_versions_fragments', sql_in('objet', array('abonnements_offre', 'abonnement')));
	//sql_delete('spip_forum', sql_in('objet', array('abonnements_offre', 'abonnement')));

	effacer_meta($nom_meta_base_version);
}
