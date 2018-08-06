<?php

if (!defined("_ECRIRE_INC_VERSION")) {
    return;
}


// Nombre maximum de mails de relances que le cron peut envoyer en une fois.
if (!defined('_ABONNEMENTS_RELANCE_POOL')){
	define('_ABONNEMENTS_RELANCE_POOL', 10);
}

// simulation pour Bank
define('_SIMU_BANK_ALLOWED',true);
