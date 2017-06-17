<?php

include('../base_url.php');

include(BASE_URL.'/conf/conf.php');

include(BASE_URL.'/conf/connexion.php');

include(BASE_URL.'/conf/fonctions.php');



include(BASE_URL.WORK_DIR.'/config/langues.cfg.php');

include(BASE_URL.WORK_DIR.'/includes/session.php');

include(BASE_URL.WORK_DIR.'/config/grid.conf.php');



$_SESSION['langue'] = DEFAULT_LANG;

$environnement_agence['menu'] = array('active' => 0);



if($_GET['alias'] == ''){

    header('location:'.RESSOURCE_URL);

    exit;

}



$select_agence = $PDO -> prepare('

SELECT *

FROM agence

WHERE actif_agence = 1

AND alias_agence = :alias_agence

ORDER BY idOrdre_agence DESC');

$select_agence -> execute(array('alias_agence' => $_GET['alias']));



if( !$select_agence -> rowCount() ){

    header('location:'.RESSOURCE_URL.'/introuvable.php');

    exit;

}



// Tableau de sélection du menu lié en fonction de la langue (il faut indiquer les 2 (menu et page) pour les pages hybrides

$array_menu_sel_langue = array('fr' => 1);$array_page_sel_langue = array('fr' => 1);



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



$seo['upline'] = $seo['title'] = 'Agence '.$select_infos_agence['nom_agence'].' | maisons-kerbea';

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



//Récupération du slider

$select_slides = $PDO -> prepare( '

    SELECT *

    FROM slide

    JOIN slide_par_agence ON slide.id_slide = slide_par_agence.id_slide

    WHERE slide_par_agence.actif_slide = 1

    AND slide_par_agence.id_agence = :id_agence

    ORDER BY idOrdre_slide DESC' );

$select_slides -> execute(array('id_agence' => $select_infos_agence['id_agence']));

$count_select_slides = $select_slides -> rowCount();

$select_slides = $select_slides -> fetchAll();



$select_actualite = $PDO -> query('SELECT * FROM actualite

WHERE publier_actu = 1

AND publierHome_actu = 1

AND id_agence = "'.$select_infos_agence['id_agence'].'"

AND (

    (dateDebut_actu<= NOW() AND dateFin_actu > NOW())

    OR (dateDebut_actu = "0000-00-00" AND dateFin_actu = "0000-00-00")

    OR (dateDebut_actu <= NOW() AND dateFin_actu = "0000-00-00")

    OR (dateDebut_actu = "0000-00-00" AND dateFin_actu > NOW())

  )

ORDER BY idOrdre_actu DESC');



$select_offres = $PDO -> prepare('

SELECT offre.id_offre, offre.adresse_offre adress, offre.gpsLat_offre gps_lat, gpsLng_offre gps_lng, ville_offre name, prix_offre, prix_mensuel_offre, offre.surfaceMaison_offre, offre.image1_offre,

    modele_maison.image1_modele_maison,

    modele_maison.image2_modele_maison,

    modele_maison.image3_modele_maison,

    modele_maison.image4_modele_maison,

    modele_maison.image5_modele_maison,

    modele_maison.image6_modele_maison,

    modele_maison.image7_modele_maison,

    modele_maison.image8_modele_maison

FROM offre

LEFT JOIN modele_maison ON offre.id_modele_maison = modele_maison.id_modele_maison

LEFT JOIN agence ON offre.id_agence = agence.id_agence

WHERE agence.alias_agence= :alias_agence

AND actif_offre = 1

AND offre.gpsLat_offre <> ""

AND offre.gpsLng_offre <> ""

ORDER BY idOrdre_offre DESC');

$select_offres -> execute(array('alias_agence' => $_GET['alias']));

$select_offres = $select_offres -> fetchAll();



foreach( $select_offres as $index => $offre ){

    $select_offres[$index]['img'] = $select_offres[$index]['image1_offre'];

    for($i = 1; $i < 9; $i ++){

        if(!empty($select_offres[$index]['image'.$i.'_modele_maison'])){

            $select_offres[$index]['img'] = RESSOURCE_URL.'/medias/modele_maison/galerie/moyenne-offre/'.$select_offres[$index]['image'.$i.'_modele_maison'];

            break;

        }

    }

    $select_offres[$index]['name'] = 'À proximité '.choosePronom('de', $select_offres[$index]['name']).' '.$select_offres[$index]['name'];

    $select_offres[$index]['link'] = RESSOURCE_URL.'/agence-'.$_GET['alias'].'/annonces-terrain-maison/'.rewrite_nom($offre['name']).'-'.$offre['id_offre'];

    $select_offres[$index]['tel'] = (!empty($offre['prix_mensuel_offre']) ? $offre['prix_mensuel_offre'].' €/mois ' : $offre['prix_offre'].' €' ) ;

    $select_offres[$index]['zipcode'] = $offre['surfaceMaison_offre'].'m²' ;

    $select_offres[$index]['icon'] = array(

        'size' => array(38, 49),

        'url' => RESSOURCE_URL.'/images/pictos/marker-offre.png'

    );

}

$offres_json = count($select_offres) ? json_encode($select_offres) : '[]';



// Selection de la première image de réalisation trouvée

$select_first_img_real = $PDO -> prepare('

  SELECT image1_realisation

  FROM realisation

  WHERE id_agence = :id_agence

  AND image1_realisation <> ""

  AND actif_realisation = 1

  AND type_realisation = "realisation"

');

$select_first_img_real -> execute(array('id_agence' => $_SESSION['agence']['id']));

?>



<!DOCTYPE HTML>

<html lang="fr-FR">

	<head>

		<meta charset="UTF-8" />

		<title><?php echo $seo['title']; ?></title>

        <meta name="description" content="<?php echo $seo['description']; ?>" />

        <meta name="keywords" content="<?php echo $seo['keywords']; ?>" />

        <meta name="robots" content="index, follow" />

		<?php include(BASE_URL.WORK_DIR.'/includes/include_css_js_commun.inc.php'); ?>

		<link rel="stylesheet" href="<?php echo RESSOURCE_URL; ?>/css/grid_structure.css.php?id=<?php echo $id_page_sel.'&amp;token='.uniqid(10); ?>" />



        <script type="text/javascript">

            var spots = <?php echo $offres_json; ?>;

            $(function(){



                function resizeSlider(slider){

                    // update width items (principal. mobile)

                    var slider_width = parseInt($(window).width(),10);



                    // width item = width slider

                    $('.' + slider + '_item').css('width',slider_width + 'px');



                    // update width clip

                    var clip_width = parseInt($('#' + slider + '_clip').width(),10);

                    var clip_width_calcul = parseInt($('.' + slider + '_item').width(),10) * $('.' + slider + '_item').length;



                    if(clip_width != clip_width_calcul){

                        $('#' + slider + '_clip').css('width',clip_width_calcul+'px');

                    }

                }

                $(window).resize(function(){

                    resizeSlider('slider_home');

                });



                $(window).resize();



                $( window ).load( function (){

                    // Slider home

                    var slider_home = $('#slider_home').carouselYnd({

                        slider_clip : '#slider_home_clip',

                        slider_item : '.slider_home_item',

                        slider_prev : '.fl-l',

                        slider_next : '.fl-r',

                        auto : 1,

                        autoTempo : 5000,

                        vitesseDefil : 800,

                        autoContainer_stop : null,

                        navigation : 'bullet',

                        init_callback : function (){}

                    });

                    $(window).resize();

                });

            });

        </script>

        <script src="https://maps.googleapis.com/maps/api/js?v=3.18&libraries=geometry,places&sensor=false"></script>

        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/js-marker-clusterer/1.0.0/markerclusterer_compiled.js"></script>

        <script src="<?php echo RESSOURCE_URL; ?>/js/map.js"></script>

	</head>

	<body class="home">

		<?php include(BASE_URL.WORK_DIR.'/includes/header.inc.php'); ?>

        <div id="container-content">

            <?php if( $count_select_slides ): ?>

                <div id="slider_home">

                    <div id="slider_home_clip">

                    <?php

                    $count_slide = 0;

                    foreach( $select_slides as $slide ):

                        $count_slide ++;

                    ?>

                        <div class="slider_home_item"><?php echo createLink( '<img src="'.RESSOURCE_URL.'/medias/slide/galerie/grande/'.$slide[ 'image1_slide' ].'" alt="" />', $slide[ 'link_slide' ], '', $slide[ 'linkBlank_slide' ] ); ?>

                            <?php if(!empty($slide['texte_slide'])): ?><div class="text"><?php echo $slide['texte_slide']; ?></div><?php endif; ?>

                        </div>

                    <?php endforeach; ?>

                    </div>

                    <?php if(count($select_slides) > 1): ?>

                        <div class="fl-l"></div>

                        <div class="fl-r"></div>

                    <?php endif; ?>

                </div>

            <?php endif; ?>

            <div class="site-width">

                <div class="col-left-home">

                    <div id="map-container">

                        <h2>Trouver <strong>votre terrain</strong><br />Construction de maisons individuelles</h2>

                        <div id="gmap"></div>

                    </div>

                </div>

                <div class="col-right-home agence">

                    <div id="contact-agence">

                        <h2><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/pen-white.png" alt=""/><span>Nous écrire</span></h2>

                        <div class="text">Nous répondons à toutes vos questions</div>

                        <?php //$link_contact est défini dans header.inc.php ?>

                        <a href="<?php echo RESSOURCE_URL.$link_contact; ?>" class="link">cliquez ici</a>

                    </div>



                    <?php

                        if($select_first_img_real -> rowCount()):

                        $image_realisation = $select_first_img_real -> fetch()['image1_realisation'];

                    ?>

                        <div id="container-realisation-link">

                            <a href="<?php echo RESSOURCE_URL.'/agence-'.$_SESSION['agence']['alias'].'/realisations'; ?>">

                                <img src="<?php echo RESSOURCE_URL; ?>/medias/realisation/galerie/grande/<?php echo $image_realisation; ?>" alt="Réalisation de maisons Kerbéa" style="width:100%;"/>

                                <span><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/image-double.png" alt=""/> Réalisations</span>

                            </a>

                        </div>

                    <?php endif; ?>

                    <?php if(!empty($select_infos_agence['facebook_agence'])): ?>

                        <div class="facebook_link">

                            <a href="<?php echo $select_infos_agence['facebook_agence']; ?>" target="_blank">

                                <img style="width: 32px;" src="<?php echo RESSOURCE_URL.'/images/pictos/fb-black.png'?>" alt="">

                                <p>Retrouvez-nous sur Facebook</p>

                            </a>

                        </div>

                    <?php endif; ?>

                </div>

                <div class="both"></div>

            </div>



            <div class="line-hr"></div>



            <div class="site-width">

                <div id="description_agence" class="col-left-home">

                    <div class="zone_dyna"><?php echo $select_infos_agence['description_agence']; ?></div>

                </div>

                <div class="col-right-home agence">

                    <div id="container-actu">

                        <h2>Les news Kerbea</h2>

                        <?php if($select_actualite -> rowCount()): ?>

                            <a href="<?php echo RESSOURCE_URL; ?>/agence-<?php echo $_SESSION['agence']['alias']; ?>/actualites" class="all-news">toutes les news</a>

                            <div id="slider_actu">

                                <div id="slider_actu_clip">

                                    <?php

                                    foreach( $select_actualite as $actualite):

                                        $image_actu = !empty($actualite['image1_actu']) ? RESSOURCE_URL.'/medias/actualite/galerie/moyenne/'.$actualite['image1_actu'] : RESSOURCE_URL.'/images/contenu/no-actualite.png';

                                        $link_actu = RESSOURCE_URL.'/agence-'.$_SESSION['agence']['alias'].'/actualites/'.rewrite_nom($actualite['titre_actu'].'-'.$actualite['id_actu']);

                                        ?>

                                        <div class="slider_actu_item">

                                            <div class="image"><img src="<?php echo $image_actu; ?>" alt="Image d'actualité"/></div>

                                            <div class="text"><?php echo $actualite['descriptionCourte_actu']; ?></div>

                                            <?php echo createLink('lire la suite',$link_actu,'link'); ?>

                                        </div>

                                    <?php endforeach; ?>

                                </div>

                            </div>

                        <?php else : ?>

                            <div>Il n'y a aucune actualité pour le moment.</div>

                        <?php endif; ?>

                    </div>

                </div>

                <div class="both"></div>

            </div>

        </div>



		<?php include(BASE_URL.WORK_DIR.'/includes/footer.inc.php'); ?>
	</body>

</html>
