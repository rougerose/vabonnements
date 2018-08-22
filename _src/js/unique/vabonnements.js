// =====================================
// vabonnements
// =====================================


function vabonnements($element) {
	var classeOffres = 'js-fa-offres-'; // classe de chaque groupe d'offre

	var $conteneur = $element,
		$form = $conteneur.find('> form'),
		$inputs = $form.find('input[type="radio"]'),
		$localisation = $inputs.filter('input[name="localisation"]'), // localisation
		$duree = $inputs.filter('input[name="duree"]'), // duree
		$fsOffres = $form.find('.editer_abonnements_offre'), // groupe offres
		$offres = $fsOffres.find('.fa-offres'), // offres par groupe
		$offre = $fsOffres.find('.fa-offre'); // offre individuelle

	// Initialiser au chargement
	// **************************
	var chargement = true;
	
	$form.addClass('js-form');

	$inputs.change(function() {
		toggleInput($(this), $offre, chargement);
	});
	
	var nbre_localisation = getValeur($localisation),
		nbre_duree = getValeur($duree),
		classe_offre = classeOffres + nbre_localisation + '-' + nbre_duree;
	
	$inputs.each(function() {
		if ($(this).prop('checked')) {
			toggleInput($(this), $offre, chargement);
		}
	});
	
	toggleAffichageOffres(classe_offre, $offres, chargement);
	
	
	// Évenements onchange
	// ********************
	
	var chargement = false;
	
	// $localisation et $duree sont les sélecteurs combinés pour afficher
	// un groupe d'offres d'abonnement en particulier.
	// Chaque changement de sélecteur déclenche l'affichage.
	$localisation.change(function() {
		var nbre_localisation = $(this).val(),
			nbre_duree = getValeur($duree);
		
		var classe_offre = classeOffres + nbre_localisation + '-' + nbre_duree;
		
		$fsOffres.trigger('afficherGroupeOffres', classe_offre);
	});

	$duree.change(function() {
		var nbre_duree = $(this).val(),
			nbre_localisation = getValeur($localisation),
			classe_offre = classeOffres + nbre_localisation + '-' + nbre_duree;
		$fsOffres.trigger('afficherGroupeOffres', classe_offre);
	});


	$fsOffres.on('afficherGroupeOffres', function(event, classeOffre) {
		toggleAffichageOffres(classeOffre, $offres, chargement);
	});
};


function toggleInput($input, $offre, chargement) {
	var classeParent = '.fa__choix',
		classeSelected = 'is-selected',
		inputName = 'abonnement_offre';
		
	if (chargement === true) {
		$input.parents(classeParent).toggleClass(classeSelected);
	} else {
		if ($input.attr('name') == inputName) {
			$offre.removeClass(classeSelected);
		}
		$input.parents(classeParent).toggleClass(classeSelected).siblings().removeClass(classeSelected);
	}
}


function toggleAffichageOffres(classeOffre, $offres, chargement) {
	var selectedIndex = null,
		deselectedIndex = null,
		classeAbsolute = 'is-stacked';
	
	$offres.each(function(i) {
		var $el = $(this),
			opacity = $el.css('opacity');
		
		if (chargement === true) {
			if ($el.hasClass(classeOffre)) {
				$el.show().animate({opacity: 1});
			} else {
				$el.hide().addClass(classeAbsolute);
			}
		} else {
			// l'élément sélectionné
			if ($el.hasClass(classeOffre)) {
				selectedIndex = i;
			} else {
				// On enregistre l'élément visible à masquer.
				if ($el.is(":visible")) {
					// $el.animate({opacity: 0}, 100).hide();
					deselectedIndex = i;
				}
			}
		}
		
	});
	
	if (chargement === false) {
		// Modifier la classe position absolue de l'élément à décocher et
		// de l'élément à sélectionner.
		// Faire apparaître l'élément sélectionné.
		$offres.eq(deselectedIndex).animate({opacity: 0}, 100).addClass(classeAbsolute).hide();
		$offres.eq(selectedIndex).show().removeClass(classeAbsolute).animate({opacity: 1}, 500);
	}
}

function getValeur($obj) {
	$val = '';
	$obj.each(function(){
		$el = $(this);
		if ($el.prop('checked')) {
			$val = $el.val();
		}
	});
	return $val;
}
