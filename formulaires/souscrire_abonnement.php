<?php

if (!defined("_ECRIRE_INC_VERSION")) {
	return;
}
/*
function formulaires_souscrire_abonnement_saisies_dist($region = '') {
	$saisies = array(
		// France ou international ?
		array(
			'saisie' => 'fieldset',
			'options' => array(
				'nom' => 'localisation',
				'label' => _T('abonnement:formulaire_souscrire_localisation_titre'),
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
				'label' => _T('abonnement:formulaire_souscrire_duree_titre'),
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
				'label' => _T('abonnement:formulaire_souscrire_offres_abonnements_titre'),
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
				'label' => _T('abonnement:formulaire_souscrire_numero_debut_titre')
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
*/

function formulaires_souscrire_abonnement_charger_dist() {
	$valeurs = array();
	$valeurs['localisation'] = (_request('localisation')) ? _request('localisation') : '';
	$valeurs['duree'] = (_request('duree')) ? _request('duree') : '';
	$valeurs['abonnements_offre'] = (_request('abonnements_offre')) ? _request('abonnements_offre') : '';
	$valeurs['numero_debut'] = (_request('numero_debut')) ? _request('numero_debut') : '';
	$valeurs['cadeau'] = (_request('cadeau')) ? _request('cadeau') : '';
	$valeurs['id_rubrique'] = _request('id_rubrique');

	return $valeurs;
}

function formulaires_souscrire_abonnement_verifier_dist() {
	$erreurs = array();
	
	$obligatoires = array('localisation', 'duree', 'abonnements_offre', 'numero_debut', 'cadeau');
	
	foreach ($obligatoires as $obligatoire) {
		if (!strlen(_request($obligatoire))) {
			$erreurs['fs'.$obligatoire] = _T('abonnement:erreur_' . $obligatoire . '_obligatoire');
		}
	}
	
	return $erreurs;
}

function formulaires_souscrire_abonnement_traiter_dist() {
	$res = array();

	return $res;
}
