/*
* Carousel développé par David G.
* Copyright Agence youneed - depuis 2011 à aujourd'hui
* Aucune modification / ajout / suppression / copie partielle ou complète n'est autorisé sans autorisation
* @todo : bloquer le slider en butée lorsque déplacement par touch : un slide a la fois, pas 2 ni plus
*/

(function ($) {
	'use strict';
	$.fn.carouselYnd = function (options) {
		var sliderClip_width = 0,
			animation_running = false,
			slide_actif = 0,
			nb_slides = 0,
			autoInterval = null,
			speedFinalAnimate = 500,
			defauts = {
				'slider' : this,                    // Slider ciblé
				'sliderClearAuto' : this,           // Element qui permet d'arreter l'auto au survol
				'slider_clip' : '#slider_clip',     // Element(s) clip
				'slider_item' : '.slider_item',     // Element(s) item
				'slider_prev' : '#prev',            // Element(s) déclencheurs précédent
				'slider_next' : '#next',            // Element(s) déclencheurs suivant
				'orientation' : 'horizontal',       // Orientation du carousel {horizontal,vertical}
				'navigation' : 'default',           // Type de navigation : default ou bullet
				'bullet_container' : this,          // conteneur des bullet
				'vitesseDefil' : 500,               // Vitesse d'animation
				'auto' : 0,                         // Slider automatique activé ou non
				'autoTempo' : 4000,                 // Durée entre chaque slide automatique
				'autoContainer_stop' : this,        // Container de pause pour l'automatique
				'touch_mobile_only' : 0,            // Les fonctions touch que pour un slider lancé dans une dimension de mobile
				'width_max_mobile' : 800,           // La limite de taille du slider pour etre compris dans un "taille mobile"
				'callback' : function () {},        // Callback exécuté à chaque fin de déplacement
				'init_callback' : null              // Callback exécuté quand le slider a fini son instanciation
			},
			parametres = $.extend(defauts, options);
		parametres.animation_sens = (parametres.orientation === 'vertical') ? 'top' : 'left';

		/**
		 * create initial slider's datas
		 */
		function init() {

			// On récupère la taille total des items les uns a coté des autres
			var slider_width = (parametres.orientation === 'horizontal') ? $(parametres.slider).width() : $(parametres.slider).height();

            $(parametres.slider_item).each(function () {
				var slideIndex = $(this).index();
				$(this).addClass('itemnum_' + slideIndex);
				sliderClip_width += (parseInt($(this).outerWidth(true), 10));
				nb_slides++;
			});

			// On applique la taille calculé au clip qui englobe les items
			$(parametres.slider_clip).css('width', sliderClip_width + 'px');

            if (sliderClip_width > slider_width && nb_slides > 1) {
                // Si la taille du clip est plus grande que la taille du slider on valide le lancement && s'il y a plus d'un slide
                return true;
			}

			return false;
		}

		/**
		* activate / desactivate click event on <a> in the slider
		*/
		function switchEventClickAnchor(action) {
			if (action === 'on') {
				parametres.slider.find('a').unbind('click');
			} else {
				parametres.slider.find('a').bind('click', function () {
					return false;
				});
			}
		}

		/**
		 * si le slide est vers la droite (sens naturel) alors il suffit de compter combien d'élément il y a jusqu'a la cible : on fait une animation de cette distance puis on déplace tous ceux qui sont avant la cible a la fin
		 * si le slide est vers la gauche il faut déplacer les éléments qui se trouve jusqu'a la cible avant l'élément en cours, en faisant attention de bien déplacer le clip de la taille pour rester fixer sur l'actif, on fait une animation jusqu'a left:0
		 * @param  slideTarget	{HTMLElement}	slide a afficher
		 * @param  direction	{string}		left || right
		 * @param  touch		{boolean}		action type touch ou non
		 * @param  callback		{function}		fonction a executé en fin d'action
		 */
		function slideTo(slideTarget, direction, touch, vitesseDefil, callback) {
			callback = typeof callback !== 'function' ? parametres.callback : callback;

			if (vitesseDefil === undefined) {
				vitesseDefil = parametres.vitesseDefil;
			}

			// Il faut que le slide demandé soit différent de celui actuellement montré
			if (slideTarget !== slide_actif && !animation_running) {
				var calculAnimate = 0,
					animate_speed = touch ? speedFinalAnimate : vitesseDefil,
					i;

				animation_running = true;

				// récupération de la position du slide demandé dans le tableau de slide
				if (direction === 'right') {
					for (i = slide_actif; i !== slideTarget; i++) {

						calculAnimate += $(parametres.slider).find('.itemnum_' + i).width();

						// Si le prochain tour de boucle aura atteint le dernier élément dans le tableau, on fait en sorte que le prochain tour reparte à 0
						if (i + 1 === nb_slides) {
							i = -1;
						}
					}

					if (calculAnimate > 0) {
						$(parametres.slider_clip).animate({left : '-' + calculAnimate}, animate_speed, function () {
							i = $(parametres.slider).find('.itemnum_' + slideTarget).index();
							while (i--) {
								$(parametres.slider_item + ':last').after($(parametres.slider_item).eq(0));
							}
							$(parametres.slider_clip).css('left', 0);

							slide_actif = slideTarget;
							animation_running = false;

							switchEventClickAnchor('on');

							if (typeof callback === 'function') {
								callback();
							}
						});
					}
				} else {
					for (i = slide_actif; i !== slideTarget; i--) {
						// On bouge l'élément devant le premier et on déplace le clip de sa longueur pour toujours rester en face de l'actif
						$(parametres.slider_item + ':first').before($(parametres.slider_item + ':last'));
						var clipPos_left = $(parametres.slider_clip).position().left;
						clipPos_left -= $(parametres.slider).find('.itemnum_' + i).width();;
						$(parametres.slider_clip).css('left',clipPos_left + 'px');
						
						// Si le prochain tour de boucle aura dépassé le premier élément dans les négatif, on repart du dernier élément : le prochain tour de boucle commencera donc a nb_slides 
						if(i - 1 == -1){
							i = nb_slides;
						}
					}
					
					$(parametres.slider_clip).animate({left:0}, animate_speed,function(){
						slide_actif = slideTarget;
						animation_running = false;
						
						switchEventClickAnchor('on');
							
						if(typeof callback === 'function'){
							callback();
						}
					});
				}
				
				if(parametres.navigation == "bullet"){
					updateBulletSelected('.item_switch:eq(' + slideTarget + ')');
				}
				
			}
		}
		/**
		* get the previous slide (relative to the active one)
		* @returns {int} slide index
		*/
		function getSlide_prev(){
			return slide_actif - 1 <= -1 ? nb_slides - 1 : slide_actif - 1;
		}
		
		/**
		* get the next slide (relative to the active one)
		* @returns {int} slide index
		*/
		function getSlide_next(){
			return slide_actif + 1 >= nb_slides ? 0 : slide_actif + 1;
		}
		
		/**
		* creation bullet system function
		*/
		function createBullet(){
			// Création des switch
			var div_switch = '<div class="slider_nav"><div class="container_item_switch_left" /><div class="container_item_switch">',
			img_bullet = (parametres.url_bullet != null) ? '<img src="'+parametres.url_bullet+'" alt="" />':'';
			
			for(var i = 0; i < $(parametres.slider_item).length; i++){
				div_switch += '<span class="item_switch">'+img_bullet+'</span>'
			}
			div_switch += '</div><div class="container_item_switch_right" /></div>';
			$(parametres.bullet_container).append(div_switch);
			$(parametres.bullet_container).find('.item_switch:first').addClass('item_switch_select');			// init first switch selected
		}
		
		/**
		* update bullet selected
		* @param {object / jquery selector} bullet object
		*/
		function updateBulletSelected(bullet){
			$(parametres.bullet_container).find('.item_switch_select').removeClass('item_switch_select');
			$(parametres.bullet_container).find(bullet).addClass('item_switch_select');
		}
		/**
		* action click on bullet
		* @param {object / jquery selector} bullet object
		*/
		function clicOnBullet(bullet){
			if(!animation_running){
				
				updateBulletSelected(bullet);
				
				var this_index = $(bullet).index();
				
				if(this_index != slide_actif){
					var slide_direction = this_index > slide_actif ? 'right' : 'left';
					slideTo(this_index, slide_direction);
				}
			}
		}
		/**
		* create instance auto slider
		*/
		function autoLaunch(){
			if(autoInterval != null){
				clearInterval(autoInterval);
			}
			autoInterval = setInterval(function(){
				slideTo(getSlide_next() ,'right')
			},parametres.autoTempo);
		}
		
		
		function clearGhostItem(){
			if(ghostItem_exist){
				$(parametres.slider).find('.ghostItem').remove();
				$(parametres.slider_clip).css({
					left : '+=' + element_width,
					width : '-=' + element_width
				});
				ghostItem_exist = false;
			}
		}
		
		function handleHammer(ev) {
			if(!animation_running){
				ev.preventDefault();
				
				if(ev.type == 'dragleft' || ev.type == 'dragright'){
					ev.gesture.preventDefault();
				}
				
				if(parametres.auto){
					clearInterval(autoInterval);
				}
				switch(ev.type) {
					case 'dragright':
					case 'dragleft':
						switchEventClickAnchor('off');
						
						var drag_offset = ev.gesture.deltaX;
						
						// Cas particulier
						if(ev.type == 'dragright' && $(parametres.slider).find('.ghostItem').length == 0){
							$( parametres.slider_item + ':last' ).clone().addClass( 'ghostItem' ).prependTo( parametres.slider_clip );
							$(parametres.slider_clip).css({
								width : '+=' + element_width
							})
							ghostItem_exist = true;
						}
						if(ghostItem_exist){
							drag_offset -= element_width;
						}
						$(parametres.slider_clip).css({
							left : drag_offset
						})
						speedFinalAnimate = parametres.vitesseDefil;
						break;
						
					case 'swipeleft':
						switchEventClickAnchor('off');
						
						clearGhostItem();
						
						speedFinalAnimate = 100;
						goNext(true);
						ev.gesture.stopDetect();
						
						break;
						
					case 'swiperight':
						switchEventClickAnchor('off');
						
						clearGhostItem();
						
						speedFinalAnimate = 100;
						goPrev(true);
						ev.gesture.stopDetect();
						
						break;
						
					case 'release':
						// more then 50% moved, navigate
						if(Math.abs(ev.gesture.deltaX) > element_width/2) {
							clearGhostItem();
							
							if(ev.gesture.direction == 'right') {
								goPrev(true);
							} else {
								goNext(true);
							}
						}
						else {
							var clip_target_left = 0;
							if(ghostItem_exist){
								clip_target_left -= element_width;
							}
							
							$(parametres.slider_clip).animate({left:clip_target_left,easing:'linear'},speedFinalAnimate, function(){
								if(ghostItem_exist){
									$(parametres.slider).find('.ghostItem').remove();
									$(parametres.slider_clip).css('left','0');
									ghostItem_exist = false;
								}
								
								switchEventClickAnchor('on');
							});
						}
						break;
				}
				
				if(parametres.auto){
					autoLaunch();
				}
			}
			else{
				if(typeof ev.gesture != 'undefined'){
					ev.gesture.stopDetect();
					ev.gesture.preventDefault();
				}
			}
		}
		
		
		// if jquery.hammer.js exists
		if(typeof Hammer === 'function'){

			var element_width = parseInt($(parametres.slider_item).width(), 10),
			ghostItem_exist = false;
			
			
			/* Protection resizing on touch */
			$(window).resize(function(){
				element_width = parseInt($(parametres.slider_item).width(), 10);
			});
		}
		
		function goPrev(touch){
			slideTo(getSlide_prev() ,'left', touch);
		}
		function goNext(touch){
			slideTo(getSlide_next() ,'right', touch);
		}
		
		// public method
		this.slideMan = function(index, direction , speed, callback){
			slideTo(index, direction, false, speed, callback);
		}
		this.isAnimationRun = function(){
			return animation_running;
		}
		this.getSlideActif = function(){
			return slide_actif;
		}
		
		// Main function (for each slider)
		return this.each(function(){
			// If slider initiate
			if(init()){
				// Mobile & tablet only && 
				if( false == parametres.touch_mobile_only || 1 == parametres.touch_mobile_only && parseInt($(window).width(), 10) < parametres.width_max_mobile){
					$(parametres.slider_item).hammer({
						drag_lock_to_axis : true,
						swipe_velocity : 0.5
					}).on("mousedown touch release dragleft dragright swipeleft swiperight dragup dragdown", handleHammer);
				}
				
				// bind action on previous button
				$(parametres.slider_prev).click(function(e){
					e.preventDefault();
					goPrev();
				});
				// bind action on next button
				$(parametres.slider_next).click(function(e){
					e.preventDefault();
					goNext();
				});
				
				// if slider contain bullet system
				if(parametres.navigation == "bullet"){
					createBullet();
					
					// Bind action on bullet click
					$(parametres.bullet_container).find('.item_switch').on('click',function(){
						clicOnBullet(this);
						if(parametres.auto){
							autoLaunch();
						}
						return false;
					});
				}
				
				// If slider auto
				if(parametres.auto){
					autoLaunch();

					if(parametres.autoContainer_stop !== null){
					// bind action on mousenter / leave slider : pause auto
						$(parametres.autoContainer_stop).mouseenter(function(){
							clearInterval(autoInterval);
						})
						.mouseleave(function(){
							autoLaunch();
						});
					}
				}
                
                if(typeof parametres.init_callback === 'function'){
                    parametres.init_callback();
                }
			}
			
		});
	};
})(jQuery);