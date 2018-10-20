/*(function($) {
	var pluginName = "vabonnements",
		defaults = {
			classe: "js-fsa-offres-"
		};

	// The actual plugin constructor
	function Plugin ( element, options ) {
		this.element = element;
		this.settings = $.extend( {}, defaults, options );
		this._defaults = defaults;
		this._name = pluginName;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend( Plugin.prototype, {
		init: function() {
			var $el = $(this.element),
				self = this;

			var $form = $el.find('> form'),
				$inputs = $form.find('input[type="radio"]'),
				$localisation = $inputs.filter('input[name="region"]'), // localisation
				$duree = $inputs.filter('input[name="duree"]'), // duree
				$fsOffres = $form.find('.fieldset_offres'), // fieldset offres
				$offres = $fsOffres.find('.fsa-offres'), // offres par groupe
				$offre = $fsOffres.find('.fsa-offre'); // offre individuelle

			var chargement = true;
			
			var nbre_localisation = self.getValeur($localisation),
				nbre_duree = self.getValeur($duree),
				classe_offre = self.settings.classe + nbre_localisation + '-' + nbre_duree;
			
			self.toggleAffichageOffres($offres, classe_offre, chargement);
			
			$inputs.each(function() {
				$el = $(this);
				if ($el.prop('checked')) {
					self.toggleInput($el, $offre, chargement);
				}
			});
			
			var chargement = false;
			
			$inputs.change(function() {
				self.toggleInput($(this), chargement);
			});
			
			$localisation.change(function() {
				var nbre_localisation = $(this).val(),
					nbre_duree = self.getValeur($duree);
				
				var classe_offre = self.settings.classe + nbre_localisation + '-' + nbre_duree;
				
				$fsOffres.trigger('afficherGroupeOffres', classe_offre);
			});

			$duree.change(function() {
				var nbre_duree = $(this).val(),
					nbre_localisation = self.getValeur($localisation),
					classe_offre = self.settings.classe + nbre_localisation + '-' + nbre_duree;
				$fsOffres.trigger('afficherGroupeOffres', classe_offre);
			});

			$fsOffres.on('afficherGroupeOffres', function(event, classeOffre) {
				self.toggleAffichageOffres($offres, classeOffre, chargement);
			});
		},
		
		toggleAffichageOffres: function($collection, classeOffre, chargement) {
			var selectedIndex = null,
				deselectedIndex = null,
				absolute = "is-stacked";
			
			$collection.each(function(i) {
				var $el = $(this),
					opacity = $el.css('opacity');
			
				if (chargement === true) {
					if ($el.hasClass(classeOffre)) {
						$el.animate({opacity: 1});
					} else {
						$el.addClass(absolute);
					}
				} else {
					// l'élément sélectionné
					if ($el.hasClass(classeOffre)) {
						selectedIndex = i;
					} else {
						// On enregistre l'élément visible à masquer.
						if (opacity > 0) {
							$el.animate({opacity: 0}, 100);
							deselectedIndex = i;
						}
					}
				}
			});
			
			if (chargement === false) {
				// Modifier la classe position absolue de l'élément à décocher et
				// de l'élément à sélectionner.
				// Faire apparaître l'élément sélectionné.
				$collection.eq(deselectedIndex).addClass(absolute);
				$collection.eq(selectedIndex).removeClass(absolute).animate({opacity: 1}, 500);
			}
		},
		
		toggleInput: function($obj, $obj_parent_collection, chargement) {
			if (chargement === true) {
				$obj.parents('.fsa__choix').toggleClass('is-selected');
			} else {
				if ($obj.attr('name') == 'abonnements_offre[]') {
					$obj_parent_collection.removeClass('is-selected');
				}
				$obj.parents('.fsa__choix').toggleClass('is-selected').siblings().removeClass('is-selected');
			}
		},
		
		getValeur: function($obj) {
			$val = '';
			$obj.each(function(){
				$el = $(this);
				if ($el.prop('checked')) {
					$val = $el.val();
				}
			});
			return $val;
		}
	} );

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[ pluginName ] = function( options ) {
		return this.each( function() {
			if ( !$.data( this, "plugin_" + pluginName ) ) {
				$.data( this, "plugin_" +
					pluginName, new Plugin( this, options ) );
			}
		} );
	};
})(jQuery);
*/
