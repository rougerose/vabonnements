<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}

function formulaires_offrir_abonnement_saisies_dist() {
	$saisies = array(
		// France ou international ?
		array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'localisation',
				'label' => _T('abonnement:formulaire_offrir_localisation_titre'),
			),
			'saisies' => array(
				array(
					'saisie' => 'choix_abonnement',
					'options' => array(
						'nom' => 'region',
						'class' => 'fsa__choix fsa__choix--medium',
						'defaut' => '1077',
						'datas' => array(
							'1077' => _T('abonnement:formulaire_souscrire_localisation_choix_france'),
							'1078' => _T('abonnement:formulaire_souscrire_localisation_choix_international')
						)
					)
				)
			)
		),
		// Durée
		array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'duree',
				'label' => _T('abonnement:formulaire_offrir_duree_titre'),
			),
			'saisies' => array(
				array(
					'saisie' => 'choix_abonnement',
					'options' => array(
						'nom' => 'duree',
						'class' => 'fsa__choix fsa__choix--medium',
						'defaut' => '12',
						'datas' => array(
							'12' => _T('abonnement:formulaire_souscrire_duree_12'),
							'24' => _T('abonnement:formulaire_souscrire_duree_24')
						)
					)
				)
			)
		),
		// Offres
		array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'fsabonnements_offre',
				'label' => _T('abonnement:formulaire_offrir_offres_abonnements_titre'),
			),
			'saisies' => array(
				array(
					'saisie' => 'choix_abonnement_offres',
					'options' => array(
						'nom' => 'abonnements_offre',
						'class' => 'fsa__choix'
					)
				)
			)
		),
		// Numéro début
		array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'fsnumero_debut',
				'label' => _T('abonnement:formulaire_offrir_numero_debut_titre')
			),
			'saisies' => array(
				array(
					'saisie' => 'choix_numero_debut_abonnement',
					'options' => array(
						'nom' => 'numero_debut',
						'class' => 'fsa__choix',
					)
				)
			)
		),
		// Coordonnées du bénéficiaire
		array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'fstiers',
				'label' => _T('abonnement:formulaire_offrir_coordonnees_tiers_titre'),
			)
		),
		
		// Cadeau
		array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'fscadeau',
				'label' => _T('abonnement:formulaire_souscrire_cadeau_titre'),
				'explication' => _T('abonnement:formulaire_souscrire_cadeau_desc')
			),
			'saisies' => array(
				array(
					'saisie' => 'choix_cadeaux_abonnement',
					'options' => array(
						'nom' => 'cadeau',
						'id_rubrique' => _request('id_rubrique'),
						'class' => 'fsa__choix',
					)
				)
			)
		)
	);
	
	
	
	return $saisies;
}

function formulaires_offrir_abonnement_charger_dist() {
	$valeurs = array();
	return $valeurs;
}

function formulaires_offrir_abonnement_verifier_dist() {
	$erreurs = array();
	return $erreurs;
}

function formulaires_offrir_abonnement_traiter_dist() {
	$res = array();
	return $res;
}
