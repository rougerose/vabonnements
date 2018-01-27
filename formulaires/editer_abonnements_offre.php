<?php
/**
 * Gestion du formulaire de d'édition de abonnements_offre
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


/**
 * Identifier le formulaire en faisant abstraction des paramètres qui ne représentent pas l'objet edité
 *
 * @param int|string $id_abonnements_offre
 *     Identifiant du abonnements_offre. 'new' pour un nouveau abonnements_offre.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un abonnements_offre source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du abonnements_offre, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return string
 *     Hash du formulaire
 */
function formulaires_editer_abonnements_offre_identifier_dist($id_abonnements_offre = 'new', $retour = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	return serialize(array(intval($id_abonnements_offre)));
}

/**
 * Chargement du formulaire d'édition de abonnements_offre
 *
 * Déclarer les champs postés et y intégrer les valeurs par défaut
 *
 * @uses formulaires_editer_objet_charger()
 *
 * @param int|string $id_abonnements_offre
 *     Identifiant du abonnements_offre. 'new' pour un nouveau abonnements_offre.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un abonnements_offre source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du abonnements_offre, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Environnement du formulaire
 */
function formulaires_editer_abonnements_offre_charger_dist($id_abonnements_offre = 'new', $retour = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	$valeurs = formulaires_editer_objet_charger('abonnements_offre', $id_abonnements_offre, '', $lier_trad, $retour, $config_fonc, $row, $hidden);
	
	$duree = explode(" ", $valeurs['duree']);
	$valeurs['duree_valeur'] = reset($duree);
	$valeurs['duree_unite'] = end($duree);
	
	if (strlen($valeurs['taxe'])){
		$valeurs['taxe'] = 100*$valeurs['taxe'];
	}
	
	return $valeurs;
}

/**
 * Vérifications du formulaire d'édition de abonnements_offre
 *
 * Vérifier les champs postés et signaler d'éventuelles erreurs
 *
 * @uses formulaires_editer_objet_verifier()
 *
 * @param int|string $id_abonnements_offre
 *     Identifiant du abonnements_offre. 'new' pour un nouveau abonnements_offre.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un abonnements_offre source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du abonnements_offre, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Tableau des erreurs
 */
function formulaires_editer_abonnements_offre_verifier_dist($id_abonnements_offre = 'new', $retour = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	$erreurs = array();
	
	if (intval(_request('duree_valeur')) AND _request('duree_unite')){
		set_request('duree', intval(_request('duree_valeur')) . ' ' . _request('duree_unite'));
	}

	$erreurs = formulaires_editer_objet_verifier('abonnements_offre', $id_abonnements_offre, array('titre', 'duree'));
	
	$verifier = charger_fonction('verifier', 'inc');
	if ($err = $verifier(_request('taxe'), 'decimal', array('min' => 0, 'max' => 100))){
		$erreurs['taxe'] = $err;
	}

	return $erreurs;
}

/**
 * Traitement du formulaire d'édition de abonnements_offre
 *
 * Traiter les champs postés
 *
 * @uses formulaires_editer_objet_traiter()
 *
 * @param int|string $id_abonnements_offre
 *     Identifiant du abonnements_offre. 'new' pour un nouveau abonnements_offre.
 * @param string $retour
 *     URL de redirection après le traitement
 * @param int $lier_trad
 *     Identifiant éventuel d'un abonnements_offre source d'une traduction
 * @param string $config_fonc
 *     Nom de la fonction ajoutant des configurations particulières au formulaire
 * @param array $row
 *     Valeurs de la ligne SQL du abonnements_offre, si connu
 * @param string $hidden
 *     Contenu HTML ajouté en même temps que les champs cachés du formulaire.
 * @return array
 *     Retours des traitements
 */
function formulaires_editer_abonnements_offre_traiter_dist($id_abonnements_offre = 'new', $retour = '', $lier_trad = 0, $config_fonc = '', $row = array(), $hidden = '') {
	if ($taxe = _request('taxe')){
		set_request('taxe', $taxe / 100);
	}
	
	$retours = formulaires_editer_objet_traiter('abonnements_offre', $id_abonnements_offre, '', $lier_trad, $retour, $config_fonc, $row, $hidden);
	
	return $retours;
}
