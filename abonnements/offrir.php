<?php

if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}

/**
 * Offrir un abonnement
 *
 * Il s'agit de la première étape qui suit le paiement : l'abonnement est 
 * attribué à la personne qui paie. Un numéro de coupon cadeau est attribué.
 * L'activation de l'abonnement par le bénéficiaire sera la deuxième étape.
 * 
 * @param  int $id_abonnements_offre
 * @param  array  $options
 * 		int 	id_auteur
 * 		string 	statut
 * 		int 	id_commande
 * 		string 	prix_ht_initial
 * 		string 	date
 * 		string 	date_debut
 * 		string 	date_fin
 * 		string 	numero_debut
 * 		string 	numero_fin
 * 		string 	mode_paiement
 * 		string	duree
 * 		string	log
 * @return int|bool  id_abonnement ou false
 */
function abonnements_offrir_dist($id_abonnements_offre, $options = array()) {
	include_spip('base/abstract_sql');
	
	$id_abonnement = 0;
	
	if ($row = sql_fetsel('*', 'spip_abonnements_offres', 'id_abonnements_offre='.$id_abonnements_offre)) {
		
		$defaut = array(
			'id_auteur' => 0,
			'statut' => 'prepa',
			'id_commande' => 0,
			'prix_ht_initial' => null,
			'taxe' => $row['taxe'],
			'date' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
			'date_debut' => '',
			'date_fin' => '',
			'numero_debut' => '',
			'numero_fin' => '',
			'duree' => '',
			'mode_paiement' => '',
			'log' => '',
			'coupon' => ''
		);
		
		$options = array_merge($defaut, $options);
		
		
		// 
		// Pas d'auteur ?
		// 
		if (!$options['id_auteur']) {
			spip_log("Impossible de créer l'abonnement en base : aucun auteur " . var_export($options, true), "vabonnements" . _LOG_ERREUR);
			return false;
		}
		
		
		// 
		// C'est un cadeau, l'abonnement sera activé directement par le 
		// bénéficiaire. Son statut est payé pour le moment.
		// 
		$statut = 'paye';
		
		$prix_ht_initial = $options['prix_ht_initial'];
		
		if (is_null($prix_ht_initial)) {
			$prix_ht_initial = $row['prix_ht'];
		}
		
		
		// 
		// Abonnement offert, les données relatives au numéro de début, de fin,
		// ainsi que la date de fin d'abonnement restent vides.
		// Elles seront modifiées lors de l'activation du bénéficiaire. 
		// 
		// La seule information que l'on garde est la date d'envoi du message 
		// souhaitée par le payeur. Elle sera utilisée à cet effet dans un 
		// premier temps, puis modifiée selon ce que souhaite effectivement
		// le bénéficiaire. 
		// 
		$numero_debut = '';
		$numero_fin = '';
		$date_debut = '';
		$date_fin = '';
		
		$duree = $row['duree'];
		
		// 
		// Identifier le bénéficiaire qui est enregistré dans le descriptif
		// de la commande d'abonnement
		// 
		$descriptif = sql_getfetsel(
			'descriptif',
			'spip_commandes_details',
			'id_commandes_detail=' . $options['id_commandes_detail']
		);
		// 'offert@id_auteur'
		$beneficiaire = substr(strstr($descriptif, '@'), 1);
		$id_auteur = intval($beneficiaire);
		
		
		// 
		// La date d'envoi du message -- et la date éventuelle de son abonnement --
		// sont notée dans le champ PGP
		// 
		if ($date_message = sql_getfetsel('pgp', 'spip_auteurs', 'id_auteur=' . $id_auteur)) {
			$date_message = unserialize($date_message);
			
			$date_envoi = date('Y-m-d H-i-s', $date_message['abonnement_offert_date']);
			
			// effacer la date du champ PGP
			sql_updateq('spip_auteurs', array('pgp' => ''), 'id_auteur=' . $id_auteur);
		}
		
		
		// 
		// Cacul du numéro du code cadeau qui sera envoyé au bénéficiaire
		// afin qu'il active son abonnement.
		// 
		include_spip('inc/vabonnements_code');
		
		$date_commande = sql_getfetsel('date', 'spip_commandes', 'id_commande=' . $options['id_commande']);
		$code_action = _ACTION_OFFRIR_ABONNEMENT;
		$code_cadeau = vabonnements_creer_code($id_auteur, $date_commande, $code_action);
		
		
		// 
		// Log
		// 
		include_spip('inc/vabonnements');
		
		$titre_offre = supprimer_numero($row['titre']);
		$duree_en_clair = filtre_duree_en_clair($duree);
		$prix_en_clair = prix_formater($prix_ht_initial + ($prix_ht_initial * $options['taxe']));
		$id_commande = $options['id_commande'];
		$commande_en_clair = "Commande n°$id_commande : ";
		$date_envoi_en_clair = affdate($date_envoi);
		$id_payeur = $options['id_auteur'];
		$nom_payeur = prenom_nom(generer_info_entite($id_payeur, 'auteur', 'nom'));
		$message_payeur = sql_countsel(
			'spip_messages',
			'id_auteur=' . sql_quote($id_payeur) . ' AND destinataires LIKE ' . sql_quote("%$id_auteur%") . ' AND type=' . sql_quote('kdo') . ' AND statut=' . sql_quote('prepa')
		);
		$message_perso_payeur = ($message_payeur) ? "Oui." : "Non.";
		
		// Exemple -> Commande n°XX : paiement de l'abonnement X ans, offre tarif... (prix) par Nom prénom (auteur n°) et destiné à Nom Prénom (auteur n°). Code cadeau envoyé au bénéficiaire : . Message personnalité du payeur ? Oui|Non.
		$log_abos = $commande_en_clair."paiement de l'abonnement $duree_en_clair, offre $titre_offre (prix $prix_en_clair), ";
		$log_abos .= "par $nom_payeur (auteur n°" . $options['id_auteur'] . "). ";
		$log_abos .= "Code d'activation destiné au bénéficiaire $code_cadeau. ";
		$log_abos .= "Le message annonçant le cadeau sera envoyé le $date_envoi_en_clair. ";
		$log_abos .=  "Message personnalisé par le payeur ? $message_perso_payeur";
		$log = vabonnements_log($log_abos);
		
		
		// 
		// Créer l'abonnement
		// 
		$ins = array(
			'id_abonnements_offre' => $id_abonnements_offre,
			'id_auteur' => $id_auteur,
			'id_commande' => $options['id_commande'],
			'date' => $options['date'],
			'date_debut' => $date_debut,
			'date_fin' => $date_fin,
			'numero_debut' => $numero_debut,
			'numero_fin' => $numero_fin,
			'mode_paiement' => $options['mode_paiement'],
			'prix_echeance' => $prix_ht_initial,
			'duree_echeance' => $duree,
			'statut' => $statut,
			'log' => $log,
			'coupon' => $code_cadeau
		);
		
		$id_abonnement = sql_insertq('spip_abonnements', $ins);
		
		if (!$id_abonnement){
			spip_log("Impossible de creer l'abonnement en base " . var_export($ins, true), "vabonnements" . _LOG_ERREUR);
			return false;
		}
		
		if ($statut == 'prop') {
			// TODO: notifier le payeur 
			// 
			//$notifications = charger_fonction("notifications", "inc");
			//$notifications('activerabonnement', $id_abonnement, array('statut' => $statut, 'statut_ancien' => 'prepa'));
		}
	}
	
	return $id_abonnement;
}
