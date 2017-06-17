<?php



include('../base_url.php');

include(BASE_URL.'/conf/conf.php');

include(BASE_URL.'/conf/connexion.php');

include(BASE_URL.'/conf/fonctions.php');



include(BASE_URL.WORK_DIR.'/config/langues.cfg.php');

include(BASE_URL.WORK_DIR.'/includes/session.php');

include(BASE_URL.WORK_DIR.'/config/grid.conf.php');



$_SESSION['langue'] = DEFAULT_LANG;

$environnement_agence['menu'] = array('active' => 2);



if($_GET['id'] == ''){

    header('location:'.RESSOURCE_URL.'/offres.php');

    exit;

}



// Tableau de sélection du menu lié en fonction de la langue (il faut indiquer les 2 (menu et page) pour les pages hybrides

$array_menu_sel_langue = array('fr' => '0');

$array_page_sel_langue = array('fr' => '0');



$id_menu_sel = !empty( $array_menu_sel_langue[$_SESSION['langue']] ) ? $array_menu_sel_langue[$_SESSION['langue']] : '';

$id_page_sel = !empty( $array_page_sel_langue[$_SESSION['langue']] ) ? $array_page_sel_langue[$_SESSION['langue']] : '';



if(!empty($id_page_sel)){

    $result_select_menu_sel = $PDO->query("SELECT *

    FROM dyna_menu

    JOIN dyna_page ON dyna_page.id_page = dyna_menu.id_page

    WHERE dyna_page.id_page = ".$PDO->quote($id_page_sel)."

    AND publier_menu = '1'");

    $compt_select_menu_sel = $result_select_menu_sel->rowCount();



    if(!empty($compt_select_menu_sel)){

        $ligne_select_menu_sel = $result_select_menu_sel->fetch();



// On indique le menu selectionné en fonction de la page affichée

        $id_menu_sel = $ligne_select_menu_sel['id_menu'];

    }

}



// Contient la récupération des menus / sous menu / catégorie des commentaires

include(BASE_URL.WORK_DIR.'/includes/sql_commun.php');



// Requete des blocs (pour indiquer les bonnes images etc).

$result_blocs = $PDO->prepare("

    SELECT *

    FROM dyna_page

    LEFT JOIN dyna_bloc ON dyna_page.id_page = dyna_bloc.id_page

    WHERE dyna_page.id_page = :id_page

    AND publier_page = '1'

    ORDER BY dyna_bloc.posy_bloc ASC, dyna_bloc.posx_bloc ASC");

$result_blocs -> execute(array(

    'id_page' => $id_page_sel

));

$nb_blocs = $result_blocs -> rowCount();

$result_blocs = $result_blocs -> fetchAll();



$array_id_bloc_image = array(0);



foreach( $result_blocs as $line_bloc ){

    if( $line_bloc[ 'type_bloc' ] == 'image' || $line_bloc[ 'type_bloc' ] == 'slider' ){

        $array_id_bloc_image[] = $line_bloc[ 'id_bloc' ];

    }

}



$qMarks = str_repeat('?,', count($array_id_bloc_image) - 1) . '?';



/* Selection des image et des datas liées data pour toutes les images de cette page */

$select_image_data = $PDO -> prepare( '

    SELECT image.*,

    image_data.name_imageData, image_data.value_imageData

    FROM image

    LEFT JOIN image_data ON image.id_image = image_data.id_image

    WHERE image.nom_module = "dynapage"

    AND image.id_liaison IN ('.$qMarks.')

    ORDER BY idOrdre_image DESC

');



$select_image_data -> execute( $array_id_bloc_image );

$count_image_data = $select_image_data -> rowCount();

$select_image_data = $select_image_data -> fetchAll();



foreach( $result_blocs as $index_bloc => $line_bloc ){

    if( $line_bloc[ 'type_bloc' ] == 'image' || $line_bloc[ 'type_bloc' ] == 'slider' ){



        $id_image_old = '';

        $index_nb_image_dans_bloc = -1;

        foreach( $select_image_data as $line_image ){



            if( $line_image[ 'id_liaison' ] == $line_bloc[ 'id_bloc' ] ){



                /* Si c'est la première ligne image trouvé pour le bloc en cours */

                if( $id_image_old != $line_image[ 'id_image' ] ){

                    $index_nb_image_dans_bloc ++;



                    /* Création de la partie "image" dans la line_bloc en cours */

                    $result_blocs[ $index_bloc ][ 'images' ][ $index_nb_image_dans_bloc ][ 'nom' ] = $line_image[ 'nom_image' ];

                    $id_image_old = $line_image[ 'id_image' ];

                }

                $result_blocs[ $index_bloc ][ 'images' ][ $index_nb_image_dans_bloc ][ 'datas' ][ $line_image[ 'name_imageData' ] ] = $line_image[ 'value_imageData' ];

            }

        }

    }

}





/* ############################################### Référencement par défaut ############################################### */



$seo['upline'] = $seo['title'] = !empty($select_infos_agence) ? 'Offre | Maisons Kerbea' : 'Offre | Maisons Kerbea'.$select_infos_agence['nom_agence'];

$seo['keywords'] = $seo['description'] = $seo['baseline'] = '';



/* ######################################################################################################################## */



/* Récupération du référencement */

$select_referencement = $PDO -> query('SELECT * FROM seo JOIN dyna_page ON seo.seo_id = dyna_page.seo_id WHERE id_page = '.$PDO -> quote($id_page_sel));

$count_referencement = $select_referencement -> rowCount();



// Si une ligne de référencement existe

if(!empty($count_referencement)){

// On indique qu'on a pas besoin du référencement par défaut

    $referencement_trouve = true;



// remplissage du référencement

    $ligne_referencement = $select_referencement -> fetch();

    $seo['title'] = !empty($ligne_referencement['seo_title']) ? $ligne_referencement['seo_title'] : $seo['title'];

    $seo['description'] = !empty($ligne_referencement['seo_description']) ? $ligne_referencement['seo_description'] : $seo['description'];

    $seo['keywords'] = !empty($ligne_referencement['seo_keywords']) ? $ligne_referencement['seo_keywords'] : $seo['keywords'];

    $seo['upline'] = !empty($ligne_referencement['seo_upline']) ? $ligne_referencement['seo_upline'] : $seo['upline'];

    $seo['baseline'] = !empty($ligne_referencement['seo_baseline']) ? $ligne_referencement['seo_baseline'] : $seo['baseline'];

}



/* ############################################### REQUETES DIVERSES ############################################### */



$where_agence = !empty($_SESSION['agence']['alias']) ? ' AND alias_agence = '.$PDO -> quote($_SESSION['agence']['alias']) : '';

$select_offre = $PDO -> prepare('

SELECT

    offre.*,

    modele_maison.image1_modele_maison,

    modele_maison.image2_modele_maison,

    modele_maison.image3_modele_maison,

    modele_maison.image4_modele_maison,

    modele_maison.image5_modele_maison,

    modele_maison.image6_modele_maison,

    modele_maison.image7_modele_maison,

    modele_maison.image8_modele_maison,

    agence.id_agence, agence.nom_agence, agence.alias_agence, agence.adresse_agence, agence.codePostal_agence, agence.telephone_agence, agence.ville_agence

FROM offre

LEFT JOIN modele_maison ON offre.id_modele_maison = modele_maison.id_modele_maison

LEFT JOIN agence ON offre.id_agence = agence.id_agence

WHERE 1 = 1

'.$where_agence.'

AND offre.id_offre = :id_offre

AND actif_offre = 1

ORDER BY idOrdre_offre DESC');

$select_offre -> execute(array(

    'id_offre' => $_GET['id']

));



if( !$select_offre -> rowCount() ){

    header('location:'.RESSOURCE_URL.'/offres.php');

    exit;

}



$line_offre = $select_offre -> fetch();

$title = 'À proximité '.choosePronom('de', $line_offre['ville_offre']).' '.$line_offre['ville_offre'];

$seo['upline'] = $seo['title'] = $title.' | Offre | Maisons Kerbea';



$image_offre = RESSOURCE_URL.'/images/contenu/kerbea-no-image-fiche-offre.jpg';

if(!empty($line_offre['image_modele_maison']) && !empty($line_offre['id_modele_maison'])){

    $image_offre = RESSOURCE_URL.'/medias/modele_maison/galerie/moyenne-high/'.$line_offre['image'.$line_offre['image_modele_maison'].'_modele_maison'];

}

elseif(!empty($line_offre['image1_offre'])){

    $image_offre = RESSOURCE_URL.'/medias/offre/galerie/moyenne-high/'.$line_offre['image1_offre'];

}



$json_offre = json_encode(array(array(



    'name' => $title,

    'gps_lat' => $line_offre['gpsLat_offre'],

    'gps_lng' => $line_offre['gpsLng_offre'],

    'icon' => array(

        'size' => array(38, 49),

        'url' => RESSOURCE_URL.'/images/pictos/marker-offre.png'

    )

)));

?>



<!DOCTYPE HTML>

<html lang="fr-FR">

	<head>

		<meta charset="UTF-8" />

		<title><?php echo $seo['title']; ?></title>

        <meta name="description" content="<?php echo strip_tags($line_offre['descriptionLongue_offre']); ?>" />

        <meta name="keywords" content="" />



        <meta property="og:title" content=" <?php echo $title; ?>" />

        <meta property="og:description" content="<?php echo strip_tags($line_offre['descriptionLongue_offre']); ?>" />

        <meta property="og:url" content="<?php echo 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];?>" />

        <meta property="og:image" content="<?php echo $image_offre; ?>" />



		<?php include(BASE_URL.WORK_DIR.'/includes/include_css_js_commun.inc.php'); ?>

		<link rel="stylesheet" href="<?php echo RESSOURCE_URL; ?>/css/grid_structure.css.php?id=<?php echo $id_page_sel.'&amp;token='.uniqid(10); ?>" />

        <script>



            $(document).ready(function(){

                var slider_realisation = $('#slider_realisation').carouselYnd({

                    slider_clip : '#slider_realisation_clip',

                    slider_item : '.slider_realisation_item',

                    auto : 0,

                    autoTempo : 7000,

                    callback : function(){

                        var slide_actif = slider_realisation.getSlideActif();

                        $('#container_vignette .vignette.actif').removeClass('actif');

                        $('#container_vignette .vignette:eq(' + slide_actif + ')').addClass('actif');

                    }

                });



                $('#container_vignette .vignette').click(function( event ){

                    event.preventDefault();



                    if(slider_realisation.isAnimationRun() || $(this).hasClass( 'actif' )){

                        return false;

                    }



                    var slide_index = $(this).index('.vignette');



                    $('#container_vignette .vignette.actif').removeClass('actif');

                    $(this).addClass('actif');



                    $('#slider_realisation_clip').fadeOut( 400, function (){

                        $(this).css({display : 'block', visibility : 'hidden'});

                        slider_realisation.slideMan(slide_index ,'right', 0, function(){

                            $('#slider_realisation_clip').css({display : 'none', visibility : 'visible'}).fadeIn( 400 );

                        });

                        if (window.matchMedia("(max-width: 767px)").matches) {

                            $('html, body').animate({scrollTop: $("#slider_realisation").offset().top}, 1000);

                        }

                    });

                });

            });



            $(function() {

                var lastLoadInfobox,

                    id_a,

                    id_o;



                id_a = <?php echo $line_offre['id_agence']; ?>;

                id_o = <?php echo $line_offre['id_offre']; ?>;



                $(document).on('click', '.contact, .contactus', function () {

                    window.scrollTo(0, 0);



                    if (lastLoadInfobox != 'contact') {

                        $('.infobox .infobox_area').removeClass('big').addClass('small');



                        $('.infobox .infobox_title, .infobox .infobox_content').empty();

                        $('.infobox .infobox_title').text('Je souhaite être contacté(e)');

                        $('.infobox .infobox_content').load(RESSOURCE_URL + '/ajax/form/contact_offre.inc.php?id=' + id_a, function () {

                            $('.infobox_content #contact_form #offre').val(id_o);

                        });

                        $('.infobox').show();



                        lastLoadInfobox = 'contact';

                    }

                    else {

                        $('.infobox').show();

                    }

                    return false;

                });



                $(' .infobox .black_mask, .infobox .btn_close').click(function () {

                    $('.infobox').hide();

                });

            });

        </script>

        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.13&libraries=geometry,places&sensor=false"></script>

        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/js-marker-clusterer/1.0.0/markerclusterer_compiled.js"></script>

        <script>

            var spots = <?php echo $json_offre; ?>;

        </script>

        <script src="<?php echo RESSOURCE_URL; ?>/js/map.js"></script>

	</head>

	<body>

        <div class="infobox">

            <div class="black_mask"></div>

            <div class="infobox_area">

                <div class="infobox_title"></div>

                <div class="infobox_content"></div>

                <div class="btn_close"></div>

            </div>

        </div>

		<?php include(BASE_URL.WORK_DIR.'/includes/header.inc.php'); ?>

        <div id="container-content">

            <?php if(!empty($environnement_agence['background_image'])):?>

                <div class="img-back-agence"><img src="<?php echo $environnement_agence['background_image']; ?>" alt=""/></div>

            <?php endif; ?>



            <div class="content-hover">

                <div class="site-width relative">



                    <div class="breadcrumbs"><a href="<?php echo RESSOURCE_URL; if(!empty($_SESSION['agence']['alias'])){ echo '/agence-'.$_SESSION['agence']['alias'];} ?>">Maisons Kerbéa<?php if(!empty($_SESSION['agence']['alias'])){ echo ' '.$_SESSION['agence']['alias'];} ?></a><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-red.png" alt=""/><a href="<?php echo RESSOURCE_URL; if(!empty($_SESSION['agence']['alias'])){ echo '/agence-'.$_SESSION['agence']['alias']; }; ?>/annonces-terrain-maison">Liste des Offres</a><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-red.png" alt=""/><?php echo $title; ?></div>



                    <h1 class="black-title"><?php echo $title; ?></h1>

                    <?php include(BASE_URL.WORK_DIR.'/includes/share.php'); ?>

                    <div class="fiche">

                        <div class="medias">

                            <div class="part-left">

                                <img src="<?php echo $image_offre; ?>" alt=""/>

                            </div>

                            <div class="part-right">

                                <a href="#" class="contact"><span>Je souhaite être contacté(e)</span><img class="mrg_l_s" src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-right.png" alt=""/></a>

                                <div id="gmap"></div>

                            </div>

                            <div class="both"></div>

                        </div>

                        <div class="description">

                            <div class="part-left">

                                <h3 class="type"><?php echo ucfirst(str_replace('+',' + ', $line_offre['type_offre'])); ?></h3>

                                <?php echo (!empty($line_offre['prix_mensuel_offre']) ? '<div class="price">Prix à partir de <strong>'.$line_offre['prix_mensuel_offre'].'&euro; /mois</strong></div>' : '<div class="price">Prix à partir de <strong>'.$line_offre['prix_offre'].'&euro;</strong></div>'); ?>

<!--                                <div class="price">Prix à partir de <strong>--><?php //echo $line_offre['prix_offre']; ?><!-- &euro;</strong></div>-->

                                <div class="text"><?php echo $line_offre['descriptionLongue_offre']; ?></div>

                                <div class="detail-sup">

                                    <strong>Ce prix inclut</strong> : <?php echo ucfirst(str_replace('+',' + ', $line_offre['type_offre'])); ?> | Superficie terrain : <?php echo $line_offre['surfaceTerrain_offre'].'m²';

                                    if(!empty($line_offre['surfaceMaison_offre'])){ echo '/ superficie maison : '.$line_offre['surfaceMaison_offre'].'m²'; } ?>

                                    <br /><br /><strong>Environnement</strong> : <?php echo $line_offre['environnement_offre']; ?>

                                </div>

                                <div class="relative">

                                    <a href="#" class="contact"><span>Je souhaite être contacté(e)</span><img class="mrg_l_s" src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-right.png" alt=""/></a>

                                    <div class="bb"></div>

                                </div>

                                <div class="mrg_t_l gnl_gray align_c"><em>*Sous réserve de disponibilité de la part de nos partenaires fonciers</em></div>

                            </div>

                            <div class="part-right">

                                <?php if(!empty($line_offre['dpe_offre'])): ?>

                                    <div class="align_c mrg_b_l"><img src="<?php echo RESSOURCE_URL; ?>/images/dpe/diagnostic-<?php echo $line_offre['dpe_offre']; ?>.jpg" alt=""/></div>

                                <?php endif; ?>



                                <div class="align_c">

                                    <?php

                                    echo '<strong>MAISONS KERBÉA '.mb_strtoupper($line_offre['nom_agence'],'utf8').'</strong>'.

                                    '<br /><br />'.$line_offre['adresse_agence'].

                                    '<br />'.$line_offre['codePostal_agence'].' '.$line_offre['ville_agence'].

                                    '<br /><br />Tél : '.$line_offre['telephone_agence'];



                                    if(!empty($line_offre['fax_agence'])){

                                        echo '<br />Fax : '.$line_offre['fax_agence'];

                                    }

                                    ?>

                                    <br /><br /><a href="#" class="contactus gnl_red">Contactez-nous</a>



                                    <?php /*if(empty($_SESSION['agence']['alias'])): ?>

                                        <div class="mrg_t_m"><a style="border:1px solid #787878;display: inline-block;padding: 10px;" href="<?php echo RESSOURCE_URL.'/agence-'.$line_offre['alias_agence']; ?>/annonces-terrain-maison"><span style="color:#787878;">Voir toutes les offres de cette agence</span></a></div>

                                    <?php endif;*/ ?>

                                </div>

                            </div>

                            <div class="both"></div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



		<?php include(BASE_URL.WORK_DIR.'/includes/footer.inc.php'); ?>
	</body>

</html>
