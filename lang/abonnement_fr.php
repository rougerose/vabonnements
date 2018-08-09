<?php
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}


$GLOBALS[$GLOBALS['idx_lang']] = array(

	// A
	'abonnements_actifs' => "Actifs",
	'abonnements_nouveaux' => "Nouveaux",
	'abonnements_resilies' => "Résiliés",
	'abonnement_reference_traduction_A1T1F' => 'abonnement 1 an, tarif réduit, France',
	'abonnement_reference_traduction_A1T2F' => 'abonnement 1 an, tarif standard, France',
	'abonnement_reference_traduction_A1T3' => 'abonnement 1 an, tarif soutien',
	'abonnement_reference_traduction_A1T3F' => 'abonnement 1 an, tarif soutien France',
	'abonnement_reference_traduction_A2T1F' => 'abonnement 2 ans, tarif réduit, France',
	'abonnement_reference_traduction_A2T2F' => 'abonnement 2 ans, tarif standard, France',
	'abonnement_reference_traduction_A2T3' => 'abonnement 2 ans, tarif soutien',
	'abonnement_reference_traduction_A2T3F' => 'abonnement 2 ans, tarif soutien France',
	'abonnement_reference_traduction_A1T1I' => 'abonnement 1 an, tarif réduit, International',
	'abonnement_reference_traduction_A1T2I' => 'abonnement 1 an, tarif standard, International',
	'abonnement_reference_traduction_A1T3' => 'abonnement 1 an, tarif soutien',
	'abonnement_reference_traduction_A1T3I' => 'abonnement 1 an, tarif soutien International',
	'abonnement_reference_traduction_A2T1I' => 'abonnement 2 ans, tarif réduit, International',
	'abonnement_reference_traduction_A2T2I' => 'abonnement 2 ans, tarif standard, International',
	'abonnement_reference_traduction_A2T3I' => 'abonnement 2 ans, tarif soutien International',
	'abonnement_reference_traduction_A2T3' => 'abonnement 2 ans, tarif soutien',
	'abonnement_reference_traduction_A3' => 'abonnement permanent',
	'abonnement_reference_traduction_A4' => 'abonnement obligatoire',
	'abonnement_formules' => "Voir les formules d'abonnement",
	'abonner' => "S'abonner",
	'ajouter_abonnement_message_ok'         => 'Abonnement ajouté',
	'ajouter_abonnement_message_erreur'     => "Une erreur est survenue. L'abonnement n'a pas pu être ajouté.",
	'ajouter_lien_abonnement'               => 'Ajouter cet abonnement',

	// C
	'champ_code_cadeau_label' => "Votre code cadeau",
	'champ_date_debut_label'           => 'Date de début',
	'champ_date_fin_label'             => 'Date de fin',
	'champ_date_label'                 => 'date',
	'champ_duree_echeance_label'       => "Durée de l'abonnement",
	'champ_id_abonnements_offre_label' => 'Id abonnements offre',
	'champ_id_auteur_label'            => 'Id Auteur',
	'champ_id_commande_label'          => 'Commande',
	'champ_log_label'                  => 'Log',
	'champ_mode_paiement_label'        => 'Paiement',
	'champ_numero_debut_explication'   => "Le dernier numéro d'abonnement sera calculé automatiquement en fonction de la durée et du premier numéro choisis",
	'champ_numero_debut_label'         => 'Premier numéro',
	'champ_numero_fin_label'           => 'Dernier numéro',
	'champ_prix_echeance_label'        => "Prix HT de l'abonnement",
	'confirmer_supprimer_abonnement'   => 'Confirmez-vous la suppression de cet abonnement ?',
	
	// E
	'editer_abonnement_explication' => "La modification d'un abonnement n'est pas possible.",
	'erreur_code_cadeau_obligatoire' => 'Un code cadeau est obligatoire',

	// I
	'icone_creer_abonnement'    => 'Ajouter un abonnement',
	'icone_modifier_abonnement' => 'Modifier cet abonnement',
	'info_abonnes' => "Abonnés",
	'info_abonnements' => "Abonnements",
	'info_1_abonnement'         => '1 abonnement',
	'info_nb_abonnements'       => '@nb@ abonnements',
	'info_1_abonnement_actif'         => '1 abonnement actif',
	'info_nb_abonnements_actifs'       => '@nb@ abonnements actifs',
	'info_abonnements_auteur'   => 'Les abonnements de cet auteur',
	'info_afficher'             => 'Voir',
	'info_aucun_abonnement'     => 'Aucun abonnement',
	'info_aucune_commande'      => 'Aucune commande liée',
	'info_filtre_actif'         => "Actifs",
	'info_filtre_paye'         => "Payés",
	'info_filtre_prepa'         => "En commande",
	'info_filtre_prop'         => "En attente",
	'info_filtre_resilie'       => "Résiliés",
	'info_filtre_tous'          => "Tous",
	'info_1_gratuit' => "1 gratuit",
	'info_nb_gratuit' => "@nb@ gratuits",
	'info_dont' => "dont",
	
	'info_nom_auteur'           => "Nom",
	'info_nouveaux_dont' => "Dont 1<sup>er</sup> abonnement",
	'info_total' => "Total",
	'info_paiement_cheque'      => 'Chèque',
	'info_paiement_gratuit'     => 'Gratuit',
	'info_paiement_paypal'      => 'Carte bleue - Paypal',
	'info_paiement_simu'    => 'Simulation - Développement',
	'info_paiement_virement'    => 'Virement',
	
	// M
	'message_erreur_abonnement_obligatoire_paiement_gratuit' => "Un abonnement obligatoire ou gratuit est nécessairement en paiement gratuit",
	'message_erreur_valider_code_cadeau' => "Des informations sont erronées ou manquantes, il n'est pas possbile de poursuivre.",
	'message_erreur_contact_adresse' => "Cet auteur doit être lié à un contact et à une adresse postale avant de pouvoir lui ajouter un abonnement",
	'message_erreur_contact' => "Cet auteur doit être lié à un contact avant de pouvoir lui ajouter un abonnement",
	'message_erreur_adresse' => "Cet auteur doit être lié à une adresse postale avant de pouvoir lui ajouter un abonnement",
	'message_echec_valider_code_cadeau' => "Nous sommes désolés, mais le code est incorrect",
	'message_succes_valider_code_cadeau' => "Le code est correct",
	
	// N
	'numero_encours_info'   => " (Numéro actuel)",
	'numero_precedent_info' => " (Numéro précédent)",
	'numero_prochain_info'  => " (Prochain numéro)",
	
	// O
	'offrir' => "<span>Offrir</span> un abonnement",

	// R
	'renouveler' => "<span>Renouveler</span> votre abonnement",
	'retirer_lien_abonnement' => 'Retirer ce abonnement',
	'retirer_tous_liens_abonnements' => 'Retirer tous les abonnements',

	// S
	'souscrire' => "<span>Souscrire</span> un nouvel abonnement",
	'supprimer_abonnement' => 'Supprimer cet abonnement',

	// T
	'texte_abonnements_en_cours' => "Abonnements en cours",
	'texte_ajouter_abonnement'                  => 'Ajouter un abonnement',
	'texte_changer_statut_abonnement'           => 'Ce abonnement est :',
	'texte_creer_associer_abonnement'           => 'Créer et associer un abonnement',
	'texte_definir_comme_traduction_abonnement' => 'Ce abonnement est une traduction du abonnement numéro :',
	'texte_statut_paye'                        => "Payé",
	'texte_statut_prepa'                        => "En commande",
	'texte_statut_prop'                        => "En attente",
	'texte_statut_actif'                        => "Actif",
	'texte_statut_resilie'                      => "Résilié",
	'titre_abonnement'                          => 'Abonnement',
	'titre_abonnements'                         => 'Abonnements',
	'titre_abonnements_rubrique'                => 'Abonnements de la rubrique',
	'titre_abonnements_statistiques' => "Statistiques des abonnements",
	'titre_derniers_jours_nb' => "@nb@ derniers jours",
	'titre_derniers_mois_nb' => "@nb@ derniers mois",
	'titre_dernieres_semaines_nb' => "@nb@ dernières semaines",
	'titre_langue_abonnement'                   => 'Langue de ce abonnement',
	'titre_logo_abonnement'                     => 'Logo de ce abonnement',
	'titre_nombre' => "Nombre",
	'titre_objets_lies_abonnement'              => 'Liés à ce abonnement',
	'titre_repartition_des_offres' => "Répartition des offres d'abonnement",
);
