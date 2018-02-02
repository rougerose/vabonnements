<?php
/**
 * Utilisation de l'action supprimer pour l'objet abonnement
 *
 * @plugin     Vacarme Abonnements
 * @copyright  2018
 * @author     Le Drean*Christophe
 * @licence    GNU/GPL
 * @package    SPIP\Vabonnements\Action
 */

if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}



/**
 * Action pour supprimer un·e abonnement
 *
 * Vérifier l'autorisation avant d'appeler l'action.
 *
 * @example
 *     ```
 *     [(#AUTORISER{supprimer, abonnement, #ID_ABONNEMENT}|oui)
 *         [(#BOUTON_ACTION{<:abonnement:supprimer_abonnement:>,
 *             #URL_ACTION_AUTEUR{supprimer_abonnement, #ID_ABONNEMENT, #URL_ECRIRE{abonnements}},
 *             danger, <:abonnement:confirmer_supprimer_abonnement:>})]
 *     ]
 *     ```
 *
 * @example
 *     ```
 *     [(#AUTORISER{supprimer, abonnement, #ID_ABONNEMENT}|oui)
 *         [(#BOUTON_ACTION{
 *             [(#CHEMIN_IMAGE{abonnement-del-24.png}|balise_img{<:abonnement:supprimer_abonnement:>}|concat{' ',#VAL{<:abonnement:supprimer_abonnement:>}|wrap{<b>}}|trim)],
 *             #URL_ACTION_AUTEUR{supprimer_abonnement, #ID_ABONNEMENT, #URL_ECRIRE{abonnements}},
 *             icone s24 horizontale danger abonnement-del-24, <:abonnement:confirmer_supprimer_abonnement:>})]
 *     ]
 *     ```
 *
 * @example
 *     ```
 *     if (autoriser('supprimer', 'abonnement', $id_abonnement)) {
 *          $supprimer_abonnement = charger_fonction('supprimer_abonnement', 'action');
 *          $supprimer_abonnement($id_abonnement);
 *     }
 *     ```
 *
 * @param null|int $arg
 *     Identifiant à supprimer.
 *     En absence de id utilise l'argument de l'action sécurisée.
**/
function action_supprimer_abonnement_dist($arg=null) {
	if (is_null($arg)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}
	$arg = intval($arg);

	// cas suppression
	if ($arg) {
		sql_delete('spip_abonnements',  'id_abonnement=' . sql_quote($arg));
	}
	else {
		spip_log("action_supprimer_abonnement_dist $arg pas compris");
	}
}
