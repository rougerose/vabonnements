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
						'gratuit' => _T('abonnement:texte_paiement_gratuit'),
						'cheque' => _T('abonnement:texte_paiement_cheque'),
						'virement' => _T('abonnement:texte_paiement_virement')
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
				'saisie' => 'abonnement_numero_debut',
				'options' => array(
					'nom' => 'numero_debut',
					'label' => _T('abonnement:champ_numero_debut_label'),
					'obligatoire' => 'oui',
					'explication' => _T('abonnement:champ_numero_debut_explication')
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
	
	} else {
		$erreurs += formulaires_editer_objet_verifier('abonnement', $id_abonnement, array('numero_debut'));
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
	$id_abonnement = intval($id_abonnement);
	
	$abo_log = '';
	$modif = false;
	
	$numero_debut = _request('numero_debut');
	$id_abonnements_offre = _request('id_abonnements_offre');
	
	$row_abo = sql_fetsel('*', 'spip_abonnements', 'id_abonnement=' . $id_abonnement);
	
	if ($id_abonnement > 0 && $row_abo['numero_debut'] !== $numero_debut) {
		$modif = true;
		$id_abonnements_offre = $row_abo['id_abonnements_offre'];
	}
	
	if ($id_abonnement == 0 OR $modif) {
		// Calcul date de début d'abonnement
		include_spip('inc/vabonnements_calculer_date');
		$debut = sql_fetsel('id_rubrique, date_numero', 'spip_rubriques', 'reference='.sql_quote($numero_debut));
		
		if ($debut) {
			$date_debut = vabonnements_calculer_date_debut($debut['date_numero']);
			
		} else {
			// L'abonnement débute avec le prochain numéro : 
			// il n'est pas encore créé dans la base.
			// Il faut récupérer la date du numéro en cours et décaler de 3 mois.
			$encours_numero = sql_fetsel("id_rubrique, reference, date_numero", "spip_rubriques", "statut='publie' AND id_parent=115", "", "titre DESC");
			
			// la date début de saison.
			$encours_date = vabonnements_calculer_date_debut($encours_numero['date_numero']);
			
			// la date au début de la saison prochaine.
			$prochain_date = new DateTime($encours_date);
			$prochain_date->modify('+ 3 month');
			$prochain_date_debut = $prochain_date->format('Y-m-d H:i:s');
			
			$date_debut = $prochain_date_debut;
		}
		
		// Calcul durée
		$offre = sql_fetsel('duree, titre', 'spip_abonnements_offres', 'id_abonnements_offre='.$id_abonnements_offre);
		$duree = explode(" ", $offre['duree']);
		$duree_valeur = reset($duree);
		// $duree_unite = end($duree);
		
		// Calcul date de fin d'abonnement
		$date_fin = vabonnements_calculer_date_fin($date_debut, $duree_valeur);
		
		// Nombre de numéros à servir
		$numeros_quantite = $duree_valeur / 3; 
			
		// Calcul numéro de fin d'abonnement
		$numero_fin = filtre_calculer_numero_prochain($numero_debut, $titre = false, $rang = $numeros_quantite - 1);
	}
	
	include_spip('inc/vabonnements');
	
	if ($id_abonnement == 0) {
		
		// log
		$mode_paiement = _request('mode_paiement');
		$abo_log .= "Création de l'abonnement. 0ffre ". $offre['titre'] . ". Du numéro ". $numero_debut . " au numéro ". $numero_fin . ". Paiement ". _T($mode_paiement) . ".";
		
		$log = vabonnements_log($abo_log);
		
		set_request('date_debut', $date_debut);
		set_request('date_fin', $date_fin);
		set_request('numero_fin', $numero_fin);
		set_request('log', $log);
	}
	
	if ($modif) {
		$abo_log = "Modification de l'abonnement. Du numéro " . $numero_debut . " au numéro " . $numero_fin . "." ;
		$log = $row_abo['log'];
		$log .= vabonnements_log($abo_log);
		
		set_request('date_debut', $date_debut);
		set_request('date_fin', $date_fin);
		set_request('numero_fin', $numero_fin);
		set_request('log', $log);
	}
	
	$retours = formulaires_editer_objet_traiter('abonnement', $id_abonnement, '', $lier_trad, $retour, $config_fonc, $row, $hidden);
	return $retours;
}
