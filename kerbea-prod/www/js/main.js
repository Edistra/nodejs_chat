$(document).ready(function() {
    var toggleMenu = function () {
        $('.responsive-menu-toggle').toggleClass('open');
        $('.overlay').toggle();
        $('.main-menu').toggle();
    };

    $('.search-show').click(function () {
        $('.main-menu .search-form').toggle();
    });
    $('.responsive-menu-toggle').click(function () {
        toggleMenu();
    });

    $('.main-menu .menu-item-parent').click(function (e) {
        $(this).toggleClass('open');
    });

    $(window).resize(function () {
        //console.log($(window).width());
        if($(window).width() <= 640) {
            if($('.main-menu').is(':visible')) {
                toggleMenu();
                $('.overlay').hide();
            }
        } else {
            if(!$('.main-menu').is(':visible')) {
                toggleMenu();
                $('.overlay').hide();
            }
        }
    });
    // TODO Penser à masquer le champ de recherche si on passe en responsive
});
