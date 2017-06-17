$(function(){

	var filtre = {
		variante : {
			all : true,
			pied : false,
			comble : false,
			etage : false,
            garage : false
		}
	};
	
	/**
	* Mise à jour de la liste des offres
	* @return void
	*/
	function majList(){
		$('.list-item').show();
		$('.list-item').each(function(){

            if(!filtre.variante.all){
                // Masqué par défaut
                $(this).hide();

                var checks = {'pied':false, 'comble':false, 'etage':false, 'garage':false};

                checks.pied = ( filtre.variante.pied && $(this).data('pied') != undefined ) ? true : false;
                checks.comble = ( filtre.variante.comble && $(this).data('comble') != undefined ) ? true : false;
                checks.etage = ( filtre.variante.etage && $(this).data('etage') != undefined ) ? true : false;
                checks.garage = ( filtre.variante.garage && $(this).data('garage') != undefined ) ? true : false;

                // Si l'une des conditions ci-dessus est rempli on affiche le bien
                if(checks.pied || checks.comble || checks.etage || checks.garage){
                    $(this).show();
                }
            }
		});
	}
	
	/**
	* Mise à jour des champs de recherche
	*/
	function majField(){
		for(var index in filtre.variante) { 
			if (filtre.variante.hasOwnProperty(index)) {
				if(filtre.variante[index]){
					$('.variante .filtre[data-variante="' + index + '"]').addClass('active');
				}
				else{
					$('.variante .filtre[data-variante="' + index + '"]').removeClass('active');
				}
			}
		}
	}

	/**
	* Mise à jours des filtres dans le cas de l'activation du filtre "tous" pour les variantes
	* @return void
	*/
	function selectAllVarianteOffre(){
		$('.filtre[data-variante="all"]').addClass('active');
		filtre.variante.all = true;
		filtre.variante.pied = filtre.variante.comble = filtre.variante.etage = filtre.variante.garage = false;
	}

	function raz(){
		filtre = {
			variante : {
				all : true,
				pied : false,
				comble : false,
				etage : false,
				garage : false
			}
		}
	}
    
	/**
	* Action click sur filtre variante
	*/
	$(document).on('click','.variante .filtre', function(){
		var type_flt = $(this).data('variante');
		
		if(type_flt == 'all'){
			$('.variante .filtre').removeClass('active');
			selectAllVarianteOffre();
		}
		else{
			if($(this).hasClass('active')){
				$(this).removeClass('active');
				filtre.variante[type_flt] = false;

				if(!filtre.variante.etage && !filtre.variante.comble && !filtre.variante.pied && !filtre.variante.garage){
					selectAllVarianteOffre();
				}
			}
			else{
				$(this).addClass('active');
				filtre.variante[type_flt] = true;

				$('.variante .filtre[data-variante="all"]').removeClass('active');
				filtre.variante.all = false;
			}
		}

		majList();
	});

	$(document).on('click', 'form div.raz', function(){
		raz();
		majField();
		majList();
	});

});