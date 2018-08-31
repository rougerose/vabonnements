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
	}
	include_spip('inc/vabonnements');
	$offres_obligatoires = vabonnements_offres_obligatoires();
	$offre = _request('id_abonnements_offre');
	$mode = _request('mode_paiement');
	
	if (in_array($offre, $offres_obligatoires) and $mode != 'gratuit') {
		$erreurs['message_erreur'] .= _T('abonnement:message_erreur_abonnement_obligatoire_paiement_gratuit');
	}
	
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
	/*
	$id_abonnement = intval($id_abonnement);
	$numero_debut = _request('numero_debut');
	$id_abonnements_offre = _request('id_abonnements_offre');
	$id_auteur = _request('id_auteur');
	$mode_paiement = _request('mode_paiement');
	
	// prix 
	if (!$fonction_prix OR !$fonction_prix_ht){
		$fonction_prix = charger_fonction('prix', 'inc/');
		$fonction_prix_ht = charger_fonction('ht', 'inc/prix');
	}
	
	$prix_ht = $fonction_prix_ht('abonnements_offre', $id_abonnements_offre, 6);
	$prix = $fonction_prix('abonnements_offre', $id_abonnements_offre, 6);
	
	if ($mode_paiement == 'gratuit') {
		$prix = 0;
		$prix_ht = 0;
	}
	
	if ($id_abonnement == 0) {
		
		// créer la commande
		include_spip('inc/commandes');
		$id_commande = creer_commande_encours(intval($id_auteur));
		$emplette = array(
			'objet' => 'abonnements_offre',
			'id_objet' => $id_abonnements_offre,
			'quantite' => 1
		);
		
		// créer la ligne de détail de commande
		if ($id_commande) {
			$id_commandes_detail = commandes_ajouter_detail($id_commande, $emplette);
			
			// ajouter au détail de commande : 
			// - numéro début
			// - le prix unitaire, si gratuit
			$set = array(
				'numero_debut' => $numero_debut,
				'prix_unitaire_ht' => $prix_ht
			);
			include_spip('action/editer_objet');
			objet_modifier('commandes_detail', $id_commandes_detail, $set);
		}
		
		// créer la transaction
		$options_transaction = array(
			'montant_ht' => $prix_ht,
			'id_auteur' => $id_auteur,
			'champs' => array(
				'id_commande' => $id_commande
			)
		);
		$inserer_transaction = charger_fonction('inserer_transaction', 'bank');
		$id_transaction = $inserer_transaction($prix, $options_transaction);
		
		// paiement de la transaction
		$transaction_hash = sql_getfetsel('transaction_hash', 'spip_transactions', 'id_transaction='.$id_transaction);
		
		// config du mode de paiement
		include_spip('inc/bank');
		$config = bank_config($mode_paiement);
		
		$contexte = array(
			'id_transaction' => $id_transaction,
			'transaction_hash' => $transaction_hash
		);
		
		// paiement chèque ou virement
		if (preg_match("/virement|cheque/", $mode_paiement)) {
			$contexte['autorisation_id'] = 'wait';
		}
		
		$paiement = charger_fonction("response", "presta/$mode_paiement/call");
		$reponse = $paiement($config, $contexte);
		
		// L'abonnement a été ajouté après l'enregistrement de la transaction
		// et via le pipeline pre_edition.
		$id_abonnement = sql_getfetsel('id_abonnement', 'spip_abonnements', 'id_commande='.$id_commande.' AND id_auteur='.$id_auteur);
		
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
		$res = formulaires_editer_objet_traiter('abonnements', $id_abonnement, '', $lier_trad, $retour, $config_fonc, $row, $hidden);
		return $res;
	}
	*/
}
