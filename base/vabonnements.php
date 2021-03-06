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
			'titre'                => 'text NOT NULL DEFAULT ""',
			'descriptif'           => 'text NOT NULL DEFAULT ""',
			'reference'            => 'tinytext NOT NULL DEFAULT ""',
			'duree'                => 'varchar(10) NOT NULL DEFAULT ""',
			'prix_ht'              => 'decimal(10,2) NOT NULL DEFAULT 0',
			'taxe'                 => 'decimal(4,3) default null',
			'statut'               => 'varchar(20)  DEFAULT "0" NOT NULL',
			'maj'                  => 'TIMESTAMP'
		),
		'key' => array(
			'PRIMARY KEY'        => 'id_abonnements_offre',
			'KEY statut'         => 'statut',
		),
		'titre' => 'titre AS titre, "" AS lang',
		 #'date' => '',
		'champs_editables'  => array('titre', 'descriptif', 'reference', 'duree', 'prix_ht', 'taxe'),
		'champs_versionnes' => array('titre', 'descriptif', 'reference', 'duree', 'prix_ht'),
		'rechercher_champs' => array(
			"titre" => 10,
			"descriptif" => 5, 
			"reference" => 10),
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
		'page' => false,
		'field'=> array(
			'id_abonnement'        => 'bigint(21) NOT NULL',
			'id_abonnements_offre' => 'bigint(21) NOT NULL DEFAULT 0',
			'id_auteur'            => 'bigint(21) NOT NULL DEFAULT 0',
			'id_auteur_payeur'            => 'bigint(21) NOT NULL DEFAULT 0',
			'id_commande'          => 'bigint(21) NOT NULL DEFAULT 0',
			'date'                 => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'date_debut'           => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'date_fin'             => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'numero_debut'         => 'tinytext NOT NULL DEFAULT ""',
			'numero_fin'           => 'tinytext NOT NULL DEFAULT ""',
			'duree_echeance'       => 'varchar(10) NOT NULL DEFAULT ""',
			'prix_echeance'        => 'decimal(10,2) NOT NULL DEFAULT 0',
			'mode_paiement'        => 'varchar(25) NOT NULL DEFAULT ""',
			'log'                  => 'text NOT NULL DEFAULT ""',
			'coupon'               => 'varchar(25) NOT NULL DEFAULT ""',
			'offert' => 'varchar(3) DEFAULT "non" NOT NULL',
			'message'               => 'text NOT NULL DEFAULT ""',
			'date_message'                 => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'relance'              => 'varchar(3) NOT NULL DEFAULT ""',
			'date'                 => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'statut'               => 'varchar(20)  DEFAULT "0" NOT NULL',
			'maj'                  => 'TIMESTAMP'
		),
		'key' => array(
			'PRIMARY KEY' => 'id_abonnement',
			'KEY statut'  => 'statut'
		),
		'titre' => '"" AS titre, "" AS lang',
		'date' => 'date',
		'champs_editables'  => array('id_abonnements_offre', 'id_auteur', 'date_debut', 'date_fin', 'numero_debut', 'numero_fin', 'mode_paiement'),
		'champs_versionnes' => array('id_abonnements_offre', 'date_debut', 'date_fin', 'numero_debut', 'numero_fin', 'mode_paiement'),
		'rechercher_champs' => array(
			'id_abonnements_offre' => 1,
			'id_auteur' => 1,
			'log' => 10
		),
		'tables_jointures'  => array(),
		'statut_textes_instituer' => array(
			'prepa'    => 'abonnement:texte_statut_prepa',
			'prop'    => 'abonnement:texte_statut_prop',
			'paye'    => 'abonnement:texte_statut_paye',
			'actif'    => 'abonnement:texte_statut_actif',
			'resilie'  => 'abonnement:texte_statut_resilie',
			'poubelle' => 'texte_statut_poubelle',
		),
		'statut_images' => array(
			'prepa'    => 'puce-preparer-8.png',
			'prop'    => 'puce-proposer-8.png',
			'paye'    => 'puce-abo-paye-8.png',
			'actif'    => 'puce-publier-8.png',
			'resilie'  => 'puce-refuser-8.png',
			'poubelle' => 'puce-supprimer-8.png'
		),
		'statut' => array(
			array(
				'champ'     => 'statut',
				'publie'    => 'paye, actif',
				'previsu'   => 'actif,prepa,prop,paye',
				'post_date' => 'date',
				'exception' => array('statut','tout')
			)
		),
		'texte_changer_statut' => 'abonnement:texte_changer_statut_abonnement'
	);

	return $tables;
}


/**
 * Déclaration des tables secondaires
 * 
 * @pipeline declarer_tables_auxiliaires
 * @param  array $tables
 * @return array
 */
function vabonnements_declarer_tables_auxiliaires($tables) {
	$tables['spip_abonnements_stats'] = array(
		'field' => array(
			'date' => "DATE NOT NULL DEFAULT '0000-00-00'", // date du jour
			'nb_abonnes' => "bigint(21) DEFAULT '0' NOT NULL", // nombre d'abonnés uniques
			'nb_abonnements' => "bigint(21) DEFAULT '0' NOT NULL", // nombre d'abonnements actifs
			'nb_abonnements_new' => "bigint(21) DEFAULT '0' NOT NULL", // nombre d'abonnements souscrits par de nouveaux abonnés
			'nb_abonnements_plus' => "bigint(21) DEFAULT '0' NOT NULL", // nombre d'abonnements souscrits
			'nb_abonnements_moins' => "bigint(21) DEFAULT '0' NOT NULL", // nombre d'abonnements finis
			'ventil_abonnements' => "text NOT NULL DEFAULT ''", // par offre : nombre d'abonnements actifs
			'ventil_abonnements_new'    => "text NOT NULL DEFAULT ''", // par offre : nombre d'abonnements souscrits par de nouveaux abonnés
			'ventil_abonnements_plus'   => "text NOT NULL DEFAULT ''", // par offre : nombre d'abonnements souscrits
			'ventil_abonnements_moins'  => "text NOT NULL DEFAULT ''", // par offre : nombre d'abonnements finis
		),
		'key' => array(
			'PRIMARY KEY' => 'date',
		)
	);
	
	return $tables;
}


/**
 * Champ extra options dans les détails de commandes
 *
 * Ce champ permettra de stocker les options du panier
 *
 * @pipeline declarer_champs_extras
 * @param  array  $stables
 * @return array
 */
function vabonnements_declarer_champs_extras($champs = array()) {
	$champs['spip_commandes_details']['options'] = array(
		'saisie' => 'input',
		'options' => array(
			'nom' => 'options',
			'label' => "Options",
			'type' => 'text',
			'sql' => "text NOT NULL DEFAULT ''",
		)
	);
	return $champs;
}
