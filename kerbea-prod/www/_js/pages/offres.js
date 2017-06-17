$(function(){

    var filtre = {
        type : {
            all : true,
            maison : false,
            terrain : false
        },
        variante : {
            all : true,
            pied : false,
            comble : false,
            etage : false,
            garage : false
        },
        search : {
            critere : null,
            value : null,
            surfaceTerrainMin : null,
            surfaceMaisonMin : null,
            prixMax : null,
            mensualiteMax : null
        }
    },
    geocoder,autocomplete,
        nb_elem_in_page = 20;
    
    /**
    * Mise à jour de la liste des offres
    * @return void
    */
    function majList(){
        $('.list-item').show().addClass('accepted');

        $('.list-item').each(function(){
            if(
                // Priorité : recherche par lieu
                (filtre.search.critere !== null && $(this).data(filtre.search.critere) != filtre.search.value)
                // Recherche par critère de bien (surface maison)
                || ( filtre.search.surfaceMaisonMin !== null && $(this).data('surface_m') <= filtre.search.surfaceMaisonMin)
                // Recherche par critère de bien (surface terrain)
                || ( filtre.search.surfaceTerrainMin !== null && $(this).data('surface_t') <= filtre.search.surfaceTerrainMin)
                // Recherche par critère de bien (prix max)
                || typeof $(this).data('prix') !== 'undefined' &&  filtre.search.prixMax !== null && filtre.search.prixMax < $(this).data('prix')
                || typeof $(this).data('mensualite') !== 'undefined' &&  filtre.search.mensualiteMax !== null && filtre.search.mensualiteMax < $(this).data('mensualite')
            ){
                $(this).hide().removeClass('accepted');
                return;
            }

            if(!filtre.type.all && ($(this).data('type') == 'maison' && !filtre.type.maison || $(this).data('type') == 'terrain' && !filtre.type.terrain)){
                $(this).hide().removeClass('accepted');
                return;
            }

            if(!filtre.variante.all){
                // Masqué par défaut
                $(this).hide().removeClass('accepted');

                var checks = {'pied':false,'comble':false,'etage':false,'garage':false};

                checks.pied = ( filtre.variante.pied && $(this).data('pied') != undefined ) ? true : false;
                checks.comble = ( filtre.variante.comble && $(this).data('comble') != undefined ) ? true : false;
                checks.etage = ( filtre.variante.etage && $(this).data('etage') != undefined ) ? true : false;
                checks.garage = ( filtre.variante.garage && $(this).data('garage') != undefined ) ? true : false;

                // Si l'une des conditions ci-dessus est rempli on affiche le bien
                if(checks.pied || checks.comble || checks.etage || checks.garage){
                    $(this).show().addClass('accepted');
                }
            }
        });

        saveFilterSession();
        generatePageNumber();
        clickOnPageNumber(1);
    }
    
    /**
    * Mise à jour des champs de recherche
    */
    function majField(){
        for(var index in filtre.type) {
            //if (filtre.type.hasOwnProperty(index)) {
                if(filtre.type[index]){
                    $('.type .filtre[data-type="' + index + '"]').addClass('active');
                }
                else{
                    $('.type .filtre[data-type="' + index + '"]').removeClass('active');
                }
            //}
        }
        for(var index in filtre.variante) { 
            //if (filtre.variante.hasOwnProperty(index)) {
                if(filtre.variante[index]){
                    $('.variante .filtre[data-variante="' + index + '"]').addClass('active');
                }
                else{
                    $('.variante .filtre[data-variante="' + index + '"]').removeClass('active');
                }
            //}
        }
        
        if(filtre.search.value !== null){ $('#searchPlace').val(filtre.search.value); } else { $('#searchPlace').val(''); }
        if(filtre.search.surfaceTerrainMin !== null){ $('#surface_t').val(filtre.search.surfaceTerrainMin); } else { $('#surface_t').val(''); }
        if(filtre.search.surfaceMaisonMin !== null){ $('#surface_m').val(filtre.search.surfaceMaisonMin); } else { $('#surface_m').val(''); }
        if(filtre.search.prixMax !== null){ $('#prixMax').val(filtre.search.prixMax); } else { $('#prixMax').val(''); }
        if(filtre.search.mensualiteMax !== null){ $('#mensualiteMax').val(filtre.search.mensualiteMax); } else { $('#mensualiteMax').val(''); }
    }
    
    /**
    * Sauve filtre en ligne
    */
    function saveFilterSession(){
        $.post(RESSOURCE_URL + '/ajax/postSaveFilterSession.ajax.php',{filtre:JSON.stringify(filtre)},'JSON');
    }
    
    /**
    * récupère filtre
    */
    function getFilterSession(){
        $.getJSON(RESSOURCE_URL + '/ajax/getFilterSession.ajax.php', function(result){
            if(result.error == 0){
                if(result.data !== null){
                    filtre = JSON.parse(result.data);
                }
                /* force maj on first load to initiate $_SESSION vars and start page counter */
                majField();
                majList();
            }
        });
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

    /**
     * Mise à jours des filtres dans le cas de l'activation du filtre "tous" pour les types
     * @return void
     */
    function selectAllTypeOffre(){
        $('.filtre[data-type="all"]').addClass('active');
        filtre.type.all = true;
        filtre.type.terrain = filtre.type.maison = false;
    }

    function raz(){
        filtre = {
            type : {
                all : true,
                maison : false,
                terrain : false
            },
            variante : {
                all : true,
                pied : false,
                comble : false,
                etage : false,
                garage : false
            },
            search : {
                critere : null,
                value : null,
                surfaceTerrainMin : null,
                surfaceMaisonMin : null,
                prixMax : null,
                mensualiteMax : null
            }
        }
    }


    $(document).on('click','.type .filtre', function(){
        var type_flt = $(this).data('type');

        if(type_flt == 'all'){
            $('.type .filtre').removeClass('active');
            selectAllTypeOffre();
        }
        else{
            if($(this).hasClass('active')){
                $(this).removeClass('active');
                filtre.type[type_flt] = false;

                if(!filtre.type.terrain && !filtre.type.maison){
                    selectAllTypeOffre();
                }
            }
            else{
                $(this).addClass('active');
                filtre.type[type_flt] = true;

                $('.type .filtre[data-type="all"]').removeClass('active');
                filtre.type.all = false;
            }
        }
        majList();
    });


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

    /**
    /**
    * Search geocode
    */
    geocoder = new google.maps.Geocoder();
    var autoCompleteOptions = {
        // bounds: bounds,
        // types: ['(regions)'],
        componentRestrictions: {country: 'fr'}
    },
    inputSearch = document.getElementById('searchPlace');
    autocomplete = new google.maps.places.Autocomplete(inputSearch, autoCompleteOptions);
    
    google.maps.event.addListener(autocomplete, 'place_changed', function () {
        $('#searchPlace_form').submit();
    });
    
    /**
    * Action soumission formulaire
    * @return false / preventDefault
    */

    $('#critereBien_form').placeholder().submit(function(e){
        e.preventDefault();
        var surface_m = parseInt($('#surface_m').val(),10 ),
        surface_t = parseInt($('#surface_t').val(), 10 ),
        prixMax = parseInt($('#prixMax').val(), 10),
        mensualiteMax = parseInt($('#mensualiteMax').val(), 10 );

        if( surface_t != filtre.search.surfaceTerrainMin || surface_m != filtre.search.surfaceMaisonMin || prixMax != filtre.search.prixMax){
            filtre.search.surfaceTerrainMin = surface_t != '' && !isNaN(surface_t) ? surface_t : null;
            filtre.search.surfaceMaisonMin = surface_m != '' && !isNaN(surface_m) ? surface_m : null;
            filtre.search.prixMax = prixMax != '' && !isNaN(prixMax) ? prixMax : null;
            filtre.search.mensualiteMax = mensualiteMax != '' && !isNaN(mensualiteMax) ? mensualiteMax : null;
        }
        
        var adresse = $('#searchPlace').val();

        if( adresse != '' && adresse != filtre.search.value ){
            if(adresse.match(/[1-9]{2}/)){
                filtre.search.critere = 'departement';
                filtre.search.value = adresse;
            }
            else{
                geocoder.geocode( { 'address': adresse, region:'fr'}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        // récupération de la valeur retournée par google
                        filtre.search.value = results[0].address_components[0].short_name.toLowerCase();
                        $('#searchPlace').val(filtre.search.value);

                        // analyse du type du premier résultat
                        switch(results[0].types[0]){
                            // Code postal
                            case 'postal_code' :
                                filtre.search.critere = 'cp';
                                break;
                            // Ville
                            case 'locality' :
                                filtre.search.critere = 'ville';
                                break;
                            // Département
                            case 'administrative_area_level_2' :
                                filtre.search.critere = 'departement_nom';
                                break;
                            default :
                                alert('Veuillez indiquer une ville, un département ou un code postal');
                                filtre.search.critere = null;
                                $('#searchPlace').val('');
                        }
                    }
                    else {
                        return false;
                    }
                    // A laisser car geocode est en ajax
                    majList();
                });
            }
        }
        else if( adresse == '' ){
            filtre.search.critere = filtre.search.value = null;
        }
        majList();
    });

    function generatePageNumber(){
        var total_visible_elem = $('.list-item:visible').length,
            nb_page = Math.ceil(total_visible_elem / nb_elem_in_page),
            html_nb_page = '';
        for(var i = 1; i < nb_page + 1; i++){
            html_nb_page += '<span>' + i + '</span>';
        }
        $('.navigation-page').html(html_nb_page).children(':first').addClass('active');
    }

    function clickOnPageNumber(page){
        $('.list-item.accepted').hide().slice(page * nb_elem_in_page - nb_elem_in_page, page * nb_elem_in_page).show();
    }
    
    $(document).on('click', 'form div.raz', function(){
        raz();
        majField();
        majList();
    });

    $(document).on('click', '.navigation-page span', function(){
        clickOnPageNumber($(this).text());
        $('.navigation-page span.active').removeClass('active');
        $(this).addClass('active');
    });
    getFilterSession();
});