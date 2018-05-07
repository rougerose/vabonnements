<?php
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

$GLOBALS[$GLOBALS['idx_lang']] = array(

	// V
	'vabonnements_titre' => 'Vacarme Abonnements',

	// C
	'cfg_relance_tiers_label' => "Relance des abonnements offerts",
	'cfg_relance_tiers_explication' => "Pour les abonnements offerts et qui n'ont pas été activés après le premier mail d'invitation : saisir les seuils de relance par mail, en nombre de jours, séparés par des virgules (Ex: 10, 15, 30 pour relancer à 10 jours puis 15 jours, puis 30 jours après la date d'envoi du premier mail).",
	
	// Saisir les seuils de relance, en nombre de jours, séparés par des virgules (Ex: -10,-3,0 pour relancer 10 jours puis 3 jours avant échéance et enfin le jour de l\'échéance).
	'cfg_taxe_defaut_explication' => 'TVA par défaut applicable aux offres d\'abonnement. Saisie de la forme 0.20 pour une TVA à 20% par exemple.',
	'cfg_taxe_defaut_label' => 'TVA',
	'cfg_titre_parametrages' => 'Paramétrages',
	
	// R
	'reference_label' => 'Référence',

	// T
	'titre_config_reference_update' => 'Mise à jour des références des numéros de Vacarme',
	'titre_page_configurer_vabonnements' => 'Configurer les offres d\'abonnements',
	
	// U
	'update_bouton_label' => "Mettre à jour les références de numéros",
);
