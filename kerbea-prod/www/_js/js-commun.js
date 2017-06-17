$(document).ready(function(){
    $( 'body' ).addClass( 'standard' );

    if($(window).width() < 768){
        $(function(){

            /* Gestion des interraction menus */

            $('.container-ssmenu, .menu-home').hide();

            $('.has-ssmenu').each(function(){
                $(this).append('<span class="css_fl_r"></span>');
            });

            $('.actionMenu').click(function(){
                $('.menu-principal #container_main_menu').toggle();
                $(this).toggleClass('actif');
            });

            $(document).on('click','.li_retour',function(){
                $('ul.menu-1').animate({left:0},200,function(){});
                $('.menu-principal #container_main_menu .ss_menu_transit').animate({right:-300},200,function(){
                    $('.menu-principal #container_main_menu .ss_menu_transit:last').remove();
                });

                var taille_ssmenu = $('ul.menu-1').height();
                $('#container_main_menu').height(taille_ssmenu);
            });

            $('.has-ssmenu').click(function(){
                if($(window).width() < 1000){
                    var menuName = $(this).children('a').text();
                    $sousMenu = $(this).children('.container-ssmenu');

                    $('.menu-principal #container_main_menu').append('<div class="no_ecran ss_menu_transit"><ul><li class="li_retour">Retour <span class="css_fl_l"></span></li><li class="li_title">' + menuName + '</li></ul></div>');
                    $ssMenuTransit_div = $('.ss_menu_transit:last');
                    $ssMenuTransit_ul = $('.ss_menu_transit:last>ul');
                    $(this).find('.container-ssmenu > .menu-2 > li').clone().show().appendTo($ssMenuTransit_ul);
                    $ssMenuTransit_div.css('right','-244px');

                    $('ul.menu-1').animate({left:-300},200,function(){});
                    $ssMenuTransit_div.animate({right:0},200,function(){});

                    var taille_ssmenu = $('.ss_menu_transit ul').height();
                    $('.menu-principal #container_main_menu').height(taille_ssmenu);

                    return false;
                }
            });

        });


    $('.toggle').hide();
    $('.btn_mobile').click(function(){

        var type = $(this).data('type');
        var element = $(document).find('.'+type);

        if(element.is(':visible')){
            element.hide();
        }else{
            $('.toggle').not(element).hide();
            element.show();
        }

    });

    }

    $.cookieBar();
});

$(window).scroll( function (){
    var nb_scroll = $(window).scrollTop();


    if( !$( 'body' ).hasClass( 'compressed' ) && nb_scroll > 0 ){
        $( 'body' ).addClass( 'compressed' );
        $( 'body' ).removeClass( 'standard' );
    }
    else if( $( 'body' ).hasClass( 'compressed' ) && nb_scroll == 0 ){
        $( 'body' ).removeClass( 'compressed' );
        $( 'body' ).addClass( 'standard' );
    }
});


(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.3";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));