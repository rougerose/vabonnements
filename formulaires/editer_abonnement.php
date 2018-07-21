<?php
/**
 * Gestion du formulaire de d'édition de abonnement
 *
 * @plugin     Vacarme Abonnements
 * @copyright  2018
 * @author     Le Drean*Christophe
 * @licence    GNU/GPL
 * @package    SPIP\Vabonnements\Formulaires
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

include_spip('inc/actions');
include_spip('inc/editer');

function formulaires_editer_abonnement_saisies_dist($id_abonnement='new', $retour='', $lier_trad=0, $config_fonc='', $row=array(), $hidden='') {
	$id_auteur = _request('id_auteur');
	$id_abonnement = intval($id_abonnement);
	
	// création d'abonnement
	if ($id_abonnement == 0) {
		$saisies = array(
			array(
				'saisie' => 'hidden',
				'options' => array(
					'nom' => 'id_auteur',
					'defaut' => $id_auteur
				)
			),
			array(
				'saisie' => 'abonnements_offres',
				'options' => array(
					'nom' => 'id_abonnements_offre',
					'label' => _T('abonnements_offre:titre_abonnements_offre'),
					'obligatoire' => 'oui'
				)
			),
			array(
				'saisie' => 'abonnement_numero_debut',
				'options' => array(
					'nom' => 'numero_debut',
					'label' => _T('abonnement:champ_numero_debut_label'),
					'obligatoire' => 'oui',
					'explication' => _T('abonnement:champ_numero_debut_explication')
				)
			),
			array(
				'saisie' => 'selection',
				'options' => array(
					'nom' => 'mode_paiement',
					'label' => _T('abonnement:champ_mode_paiement_label'),
					'obligatoire' => 'oui',
					'datas' => array(
						'gratuit' => _T('abonnement:info_paiement_gratuit'),
						'cheque' => _T('abonnement:info_paiement_cheque'),
						'virement' => _T('abonnement:info_paiement_virement')
					)
				)
			)
		);
	} else {
		// une modification d'abonnement, on ne peut modifier
		// que le numéro de départ d'abonnement. 
		// $id_abonnements_offre = _request('id_abonnements_offre');
		
		$saisies = array(
			array(
				'saisie' => 'explication',
				'options' => array(
					'nom' => 'edit_abonnement_explication',
					'texte' => _T('abonnement:editer_abonnement_explication')
				)
			)
			// aucun champ modifiable ?
			// array(
			// 	'saisie' => 'abonnement_numero_debut',
			// 	'options' => array(
			// 		'nom' => 'numero_debut',
			// 		'label' => _T('abonnement:champ_numero_debut_label'),
			// 		'obligatoire' => 'oui',
			// 		'explication' => _T('abonnement:champ_numero_debut_explication')
			// 	)
			// )
		);
	}
	
	return $saisies;
}


/**
 * Identifier le formulaire en faisant abstraction des paramètres qui ne représentent pas l'objet edité
 *
 * @param int|string $id_abonnement
 *     Identifiant du abonnement. 'new' pour un nouveau abonnement.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un abonnement source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du abonnement, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return string
 *     Hash du formulaire
 */
function formulaires_editer_abonnement_identifier_dist($id_abonnement = 'new', $retour = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	return serialize(array(intval($id_abonnement)));
}

/**
 * Chargement du formulaire d'édition de abonnement
 *
 * Déclarer les champs postés et y intégrer les valeurs par défaut
 *
 * @uses formulaires_editer_objet_charger()
 *
 * @param int|string $id_abonnement
 *     Identifiant du abonnement. 'new' pour un nouveau abonnement.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un abonnement source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du abonnement, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Environnement du formulaire
 */
function formulaires_editer_abonnement_charger_dist($id_abonnement = 'new', $retour = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	$valeurs = formulaires_editer_objet_charger('abonnement', $id_abonnement, '', $lier_trad, $retour, $config_fonc, $row, $hidden);

	return $valeurs;
}

/**
 * Vérifications du formulaire d'édition de abonnement
 *
 * Vérifier les champs postés et signaler d'éventuelles erreurs
 *
 * @uses formulaires_editer_objet_verifier()
 *
 * @param int|string $id_abonnement
 *     Identifiant du abonnement. 'new' pour un nouveau abonnement.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un abonnement source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du abonnement, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Tableau des erreurs
 */
function formulaires_editer_abonnement_verifier_dist($id_abonnement = 'new', $retour = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	$erreurs = array();
	$id_abonnement = intval($id_abonnement);
	
	if ($id_abonnement == 0) {
		$erreurs += formulaires_editer_objet_verifier('abonnement', $id_abonnement, array('id_abonnements_offre', 'id_auteur', 'numero_debut', 'mode_paiement'));
	
	} // else {
	// 	$erreurs += formulaires_editer_objet_verifier('abonnement', $id_abonnement, array('numero_debut'));
	// }
	
	return $erreurs;
}

/**
 * Traitement du formulaire d'édition de abonnement
 *
 * Traiter les champs postés
 *
 * @uses formulaires_editer_objet_traiter()
 *
 * @param int|string $id_abonnement
 *     Identifiant du abonnement. 'new' pour un nouveau abonnement.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un abonnement source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du abonnement, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Retours des traitements
 */
function formulaires_editer_abonnement_traiter_dist($id_abonnement = 'new', $retour = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	$id_abonnement = intval($id_abonnement);
	$numero_debut = _request('numero_debut');
	$id_abonnements_offre = _request('id_abonnements_offre');
	$id_auteur = _request('id_auteur');
	$mode_paiement = _request('mode_paiement');
	
	if ($id_abonnement == 0) {
		$abonner = charger_fonction("abonner", "abonnements");
		$options = array(
			'id_auteur' => $id_auteur,
			'statut' => 'prepa',
			'numero_debut' => $numero_debut,
			'mode_paiement' => $mode_paiement
		);
		
		$id_abonnement = $abonner($id_abonnements_offre, $options);
		
		if (intval($id_abonnement)) {
			return array(
				'message_ok' => _T('abonnement:ajouter_abonnement_message_ok'),
				'redirect' => generer_url_ecrire("abonnement", "id_abonnement=$id_abonnement")
			);
		} else {
			return array(
				'message_erreur' => _T('abonnement:ajouter_abonnement_message_erreur')
			);
		}
	} else {
		$retours = formulaires_editer_objet_traiter('abonnements', $id_abonnement, '', $lier_trad, $retour, $config_fonc, $row, $hidden);
		return $retours;
	} 
}
