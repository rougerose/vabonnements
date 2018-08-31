<?php

if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}


// 
// Nombre maximum de mails de relances que le cron peut envoyer en une fois.
// 
define('_ABONNEMENTS_RELANCE_POOL', 10);

// 
// Formulaires et nospam
// 
$GLOBALS['formulaires_no_spam'][] = 'offrir_abonnement';

// 
// simulation pour Bank
// 
define('_SIMU_BANK_ALLOWED',true);
