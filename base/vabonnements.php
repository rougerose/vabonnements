<?php
/**
 * Déclarations relatives à la base de données
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
 * Déclaration des alias de tables et filtres automatiques de champs
 *
 * @pipeline declarer_tables_interfaces
 * @param array $interfaces
 *     Déclarations d'interface pour le compilateur
 * @return array
 *     Déclarations d'interface pour le compilateur
 */
function vabonnements_declarer_tables_interfaces($interfaces) {

	$interfaces['table_des_tables']['abonnements_offres'] = 'abonnements_offres';
	$interfaces['table_des_tables']['abonnements'] = 'abonnements';

	return $interfaces;
}


/**
 * Déclaration des objets éditoriaux
 *
 * @pipeline declarer_tables_objets_sql
 * @param array $tables
 *     Description des tables
 * @return array
 *     Description complétée des tables
 */
function vabonnements_declarer_tables_objets_sql($tables) {

	$tables['spip_abonnements_offres'] = array(
		'type' => 'abonnements_offre',
		'principale' => 'oui',
		'page' => false,
		'table_objet_surnoms' => array('abonnementsoffre'), // table_objet('abonnements_offre') => 'abonnements_offres' 
		'field'=> array(
			'id_abonnements_offre' => 'bigint(21) NOT NULL',
			'titre'              => 'varchar(25) NOT NULL DEFAULT ""',
			'descriptif'         => 'text NOT NULL DEFAULT ""',
			'duree'              => 'varchar(10) NOT NULL DEFAULT ""',
			'prix_ht'            => 'varchar(25) NOT NULL DEFAULT ""',
			'taxe'               => 'decimal(4,3) default null',
			'statut'             => 'varchar(20)  DEFAULT "0" NOT NULL',
			'maj'                => 'TIMESTAMP'
		),
		'key' => array(
			'PRIMARY KEY'        => 'id_abonnements_offre',
			'KEY statut'         => 'statut',
		),
		'titre' => 'titre AS titre, "" AS lang',
		 #'date' => '',
		'champs_editables'  => array('titre', 'descriptif', 'duree', 'prix_ht', 'taxe'),
		'champs_versionnes' => array('titre', 'descriptif', 'duree', 'prix_ht'),
		'rechercher_champs' => array("titre" => 10, "descriptif" => 5),
		'tables_jointures'  => array(),
		'statut_textes_instituer' => array(
			'prepa'    => 'texte_statut_en_cours_redaction',
			'prop'     => 'texte_statut_propose_evaluation',
			'publie'   => 'texte_statut_publie',
			'refuse'   => 'texte_statut_refuse',
			'poubelle' => 'texte_statut_poubelle',
		),
		'statut'=> array(
			array(
				'champ'     => 'statut',
				'publie'    => 'publie',
				'previsu'   => 'publie,prop,prepa',
				'post_date' => 'date',
				'exception' => array('statut','tout')
			)
		),
		'texte_changer_statut' => 'abonnements_offre:texte_changer_statut_abonnements_offre',


	);

	$tables['spip_abonnements'] = array(
		'type' => 'abonnement',
		'principale' => 'oui',
		'field'=> array(
			'id_abonnement'      => 'bigint(21) NOT NULL',
			'id_abonnements_offre' => 'bigint(21) NOT NULL DEFAULT 0',
			'id_auteur'          => 'bigint(21) NOT NULL DEFAULT 0',
			'id_commande'        => 'bigint(21) NOT NULL DEFAULT 0',
			'date'               => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'date_debut'         => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'date_fin'           => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'numero_debut'       => 'tinytext NOT NULL DEFAULT ""',
			'numero_fin'         => 'tinytext NOT NULL DEFAULT ""',
			'mode_paiement'      => 'varchar(25) NOT NULL DEFAULT ""',
			'log'                => 'text NOT NULL DEFAULT ""',
			'date'               => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'statut'             => 'varchar(20)  DEFAULT "0" NOT NULL',
			'maj'                => 'TIMESTAMP'
		),
		'key' => array(
			'PRIMARY KEY'        => 'id_abonnement',
			'KEY statut'         => 'statut',
		),
		'titre' => '"" AS titre, "" AS lang',
		'date' => 'date',
		'champs_editables'  => array('date_debut', 'date_fin', 'numero_debut', 'numero_fin', 'mode_paiement'),
		'champs_versionnes' => array('date_debut', 'date_fin', 'numero_debut', 'numero_fin', 'mode_paiement'),
		'rechercher_champs' => array(),
		'tables_jointures'  => array(),
		'statut_textes_instituer' => array(
			'prepa'    => 'texte_statut_en_cours_redaction',
			'prop'     => 'texte_statut_propose_evaluation',
			'publie'   => 'texte_statut_publie',
			'refuse'   => 'texte_statut_refuse',
			'poubelle' => 'texte_statut_poubelle',
		),
		'statut'=> array(
			array(
				'champ'     => 'statut',
				'publie'    => 'publie',
				'previsu'   => 'publie,prop,prepa',
				'post_date' => 'date',
				'exception' => array('statut','tout')
			)
		),
		'texte_changer_statut' => 'abonnement:texte_changer_statut_abonnement',


	);

	return $tables;
}
