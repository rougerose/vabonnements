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
	
	// les paiements disponibles (mais on ne garde que gratuit/virement/chèque)
	include_spip('inc/bank');
	$bank_configs = bank_lister_configs('acte');
	$bank_config_gratuit = bank_config('gratuit');
	$paiements = array($bank_config_gratuit['presta'] => _T('abonnement:info_paiement_gratuit'));
	
	foreach ($bank_configs as $k => $bank_config) {
		if (preg_match("/virement|cheque/", $k) and $bank_config['actif'] == 1) {
			$paiements[$k] = bank_titre_type_paiement($bank_config['presta']);
		}
	}
	
	// les cadeaux disponibles
	//$cadeaux = array(0 => _T('abonnement:formulaire_abonnement_sans_cadeau_titre'));	
	$cadeaux = array(0 => _T('abonnement:formulaire_abonnement_sans_cadeau_titre'));
	
	$cadeaux_l = sql_allfetsel('id_produit, titre', 'spip_produits', 'statut='.sql_quote('publie').' AND id_rubrique='.sql_quote('517'));
	
	foreach ($cadeaux_l as $cadeau) {
		$cadeaux[$cadeau['id_produit']] = $cadeau['titre'];
	}
	
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
					'datas' => $paiements,
				)
			),
			array(
				'saisie' => 'selection',
				'options' => array(
					'nom' => 'cadeau',
					'label' => _T('abonnement:champ_cadeau_label'),
					'obligatoire' => 'oui',
					'data' => $cadeaux,
				)
			),
		);
	} else {
		// Aucune modification des abonnements
		$saisies = array(
			array(
				'saisie' => 'explication',
				'options' => array(
					'nom' => 'edit_abonnement_explication',
					'texte' => _T('abonnement:editer_abonnement_explication')
				)
			)
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
	
	$res = array();
	
	$id_abonnement = intval($id_abonnement);
	$numero_debut = _request('numero_debut');
	$id_abonnements_offre = _request('id_abonnements_offre');
	$id_auteur = _request('id_auteur');
	$mode_paiement = _request('mode_paiement');
	$cadeau = _request('cadeau');
	
	if ($mode_paiement == 'gratuit') {
		$prix = 0;
		$prix_ht = 0;
	} else {
		$fonction_prix = charger_fonction('prix', 'inc/');
		$fonction_prix_ht = charger_fonction('ht', 'inc/prix');
		
		$prix_ht = $fonction_prix_ht('abonnements_offre', $id_abonnements_offre, 6);
		$prix = $fonction_prix('abonnements_offre', $id_abonnements_offre, 6);
	}
	
	// 
	// Ajout d'un abonnement : 
	// - créer la commande
	// - créer l'abonnement
	// - créer la transaction
	// 
	if ($id_abonnement == 0) {
		
		// 
		// Créer la commande
		// 
		include_spip('inc/commandes');
		$id_commande = creer_commande_encours(intval($id_auteur));
		
		// 
		// Créer la ligne de détail de commande
		// 
		if ($id_commande) {
			include_spip('action/editer_objet');
			
			// Préciser dans le champ Source de la commande qu'elle a été créée
			// par un admin. Ce qui permettra de ne pas envoyer de notifications
			// automatiques.
			$id_admin = session_get('id_auteur');
			objet_modifier('commandes', $id_commande, array('source' => "admin#$id_admin"));
			
			$options = array(
				0 => array(
					'numero_debut' => $numero_debut,
					'cadeau' => $cadeau
				),
			);
			
			$options = vpaniers_options_produire_options($options);
			
			$set = array(
				'objet' => 'abonnements_offre',
				'id_objet' => $id_abonnements_offre,
				'quantite' => 1,
			);
			
			// nouvelle ligne de commande et données attendues.
			$id_commandes_detail = commandes_ajouter_detail($id_commande, $set);
			
			unset($set);
			
			$set = array(
				'prix_unitaire_ht' => $prix_ht,
				'options' => $options, 
			);
			
			$err = objet_modifier('commandes_detail', $id_commandes_detail, $set);
			
			if ($err) {
				$res['message_erreur'] = $err;
				$res['editable'] = true;
				return $res;
			}
			
			// Ajouter le cadeau
			if ($cadeau > 0) {
				unset($set);
				
				$set = array(
					'id_commande' => $id_commande,
					'objet' => 'produit',
					'id_objet' => $cadeau,
					'quantite' => 1,
					'statut' => 'attente',
					'descriptif' => generer_info_entite($cadeau, 'produit', 'titre').' cadeau@abonnement',
					'prix_unitaire_ht' => 0 // c'est un cadeau
				);
				
				$id_commandes_detail_cadeau = objet_inserer('commandes_detail');
				
				$err = objet_modifier('commandes_detail', $id_commandes_detail_cadeau, $set);
				
				if ($err) {
					$res['message_erreur'] = $err;
					$res['editable'] = true;
					return $res;
				}
			}
		}
		
		// 
		// Créer l'abonnement
		// 
		$champs_abonnement = array(
			'id_auteur' => $id_auteur,
			'id_abonnements_offre' => $id_abonnements_offre,
			'id_commande' => $id_commande,
			'numero_debut' => $numero_debut,
			'prix_echeance' => $prix_ht,
			'offert' => 'non',
		);
		
		include_spip('action/editer_abonnement');
		$id_abonnement = abonnement_inserer($id_parent = null, $champs_abonnement);
		
		if (!$id_abonnement) {
			spip_log("Auteur $id_auteur : la création de l'abonnement a échoué pendant l'enregistrement du formulaire.", 'vabonnements_prive'._LOG_ERREUR);
			$res['editable'] = true;
			$res['message_erreur'] = "La création de l'abonnement a échoué.";
			return $res;
		}
		
		
		// 
		// Créer la transaction
		// 
		$options_transaction = array(
			'montant_ht' => $prix_ht,
			'id_auteur' => $id_auteur,
			'champs' => array(
				'id_commande' => $id_commande,
			),
		);
		
		$inserer_transaction = charger_fonction('inserer_transaction', 'bank');
		$id_transaction = $inserer_transaction($prix, $options_transaction);
		
		if ($id_transaction) {
			
			// 
			// Traiter le paiement de cette transaction
			// 
			$transaction_hash = sql_getfetsel('transaction_hash', 'spip_transactions', 'id_transaction='.intval($id_transaction));
			
			// 
			// Config du mode de paiement, y compris gratuit.
			// 
			include_spip('inc/bank');
			$config = bank_config($mode_paiement);
			$config_id = bank_config_id($config);
			
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
			
			if ($reponse[0] == 0 and $reponse[1] == false) {
				spip_log("Auteur $id_auteur : la création de la transaction liée a échoué pendant l'enregistrement du formulaire de création de l'abonnement.", 'vabonnements_prive'._LOG_ERREUR);
				
				$res['message_erreur'] = "La création de la transaction liée à l'abonnement a échoué. Impossible de créer un abonnement.";
				$res['editable'] = true;
				return $res;
			}
		}
	}
	
	$res['editable'] = false;
	$res['message_ok'] = "L'abonnement a bien été créé. La commande et la transaction liées à cet abonnement ont été également créées.";
	
	return $res;
}
