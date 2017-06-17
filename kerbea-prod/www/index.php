<?php
include('../base_url.php');
include(BASE_URL . '/conf/conf.php');
include(BASE_URL . '/conf/connexion.php');
include(BASE_URL . '/conf/fonctions.php');

include(BASE_URL . WORK_DIR . '/config/langues.cfg.php');
include(BASE_URL.WORK_DIR.'/includes/session.php');
include(BASE_URL.WORK_DIR.'/config/grid.conf.php');

$_SESSION['langue'] = DEFAULT_LANG;

//if(ALIAS_URL != ''){
//    header('location:'.RESSOURCE_URL.'/agence.php');
//    exit;
//}

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

$seo['upline'] = $seo['title'] = 'Maisons Kerbea - Constructeur de maisons individuelles';
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
$select_slides = $PDO->prepare('
    SELECT *
    FROM slide
    WHERE actif_slide = 1
    ORDER BY idOrdre_slide DESC');
$select_slides->execute();
$count_select_slides = $select_slides->rowCount();
$select_slides = $select_slides->fetchAll();

$select_agence = $PDO->query('
SELECT nom_agence name, alias_agence alias, adresse_agence adress, codePostal_agence zipcode, telephone_agence tel,ville_agence city, gpsLat_agence gps_lat, gpsLng_agence gps_lng
FROM agence
WHERE actif_agence = 1
ORDER BY idOrdre_agence DESC');
$select_agence = $select_agence->fetchAll();

foreach ($select_agence as $index => $agence) {
    $select_agence[$index]['link'] = RESSOURCE_URL . '/agence-' . $agence['alias'];
    $select_agence[$index]['img'] = '';
}
$agences_json = count($select_agence) ? json_encode($select_agence) : '[]';

$select_actualite = $PDO->query('SELECT * FROM actualite
WHERE publier_actu = 1
AND publierHome_actu = 1
AND id_agence IS NULL
AND (
    (dateDebut_actu<= NOW() AND dateFin_actu > NOW())
    OR (dateDebut_actu = "0000-00-00" AND dateFin_actu = "0000-00-00")
    OR (dateDebut_actu <= NOW() AND dateFin_actu = "0000-00-00")
    OR (dateDebut_actu = "0000-00-00" AND dateFin_actu > NOW())
  )
  ORDER BY idOrdre_actu DESC');

?>

<!DOCTYPE HTML>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8"/>
    <title><?php echo $seo['title']; ?></title>
    <meta name="description" content="<?php echo $seo['description']; ?>"/>
    <meta name="keywords" content="<?php echo $seo['keywords']; ?>"/>
    <meta name="robots" content="index, follow"/>

    <?php include(BASE_URL . WORK_DIR . '/includes/include_css_js_commun.inc.php'); ?>
    <link rel="stylesheet"
          href="<?php echo RESSOURCE_URL; ?>/css/grid_structure.css.php?id=<?php echo $id_page_sel . '&amp;token=' . uniqid(10); ?>"/>

    <script type="text/javascript">
        var spots = <?php echo $agences_json; ?>;
        $(function () {

            function resizeSlider(slider) {
                // update width items (principal. mobile)
                var slider_width = parseInt($(window).width(), 10);

                // width item = width slider
                $('.' + slider + '_item').css('width', slider_width + 'px');

                // update width clip
                var clip_width = parseInt($('#' + slider + '_clip').width(), 10);
                var clip_width_calcul = parseInt($('.' + slider + '_item').width(), 10) * $('.' + slider + '_item').length;

                if (clip_width != clip_width_calcul) {
                    $('#' + slider + '_clip').css('width', clip_width_calcul + 'px');
                }
            }
            $(window).resize(function () {
                resizeSlider('slider_home');
            });

            $(window).resize();

            $(window).load(function () {
                // Slider home
                var slider_home = $('#slider_home').carouselYnd({
                    slider_clip: '#slider_home_clip',
                    slider_item: '.slider_home_item',
                    slider_prev: '.fl-l',
                    slider_next: '.fl-r',
                    auto: 1,
                    autoTempo: 5000,
                    vitesseDefil: 800,
                    autoContainer_stop: null,
                    navigation: 'bullet',
                    init_callback: function () {
                    }
                });
                $(window).resize();
            });
        });
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.18&libraries=geometry,places&sensor=false"></script>
    <script type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/js-marker-clusterer/1.0.0/markerclusterer_compiled.js"></script>
    <script src="<?php echo RESSOURCE_URL; ?>/js/map.js"></script>
</head>

<body class="home">
<?php include(BASE_URL . WORK_DIR . '/includes/header.inc.php'); ?>
<div id="container-content">
    <?php if ($count_select_slides): ?>
        <div id="slider_home">
            <div id="slider_home_clip">
                <?php
                $count_slide = 0;
                foreach ($select_slides as $slide):
                    $count_slide++;
                    ?>
                    <div
                        class="slider_home_item"><?php echo createLink('<img src="' . RESSOURCE_URL . '/medias/slide/galerie/grande/' . $slide['image1_slide'] . '" alt="" />', $slide['link_slide'], '', $slide['linkBlank_slide']); ?>
                        <?php if (!empty($slide['texte_slide'])): ?>
                            <div class="text"><?php echo $slide['texte_slide']; ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="fl-l"></div>
            <div class="fl-r"></div>
        </div>
    <?php endif; ?>
</div>
<!---->
<!--    <div class="site-width">-->
<!--        <div class="col-left-home">-->
<!--            <div id="map-container">-->
<!--                <h2><a href="--><?php //echo RESSOURCE_URL; ?><!--/nos-agences">Trouver <strong>votre agence</strong><br/>Construction-->
<!--                        de maisons individuelles</a></h2>-->
<!--                <div class="separ-dotted"></div>-->
<!--                <div class="show-offer">-->
<!--                    <a href="--><?php //echo RESSOURCE_URL; ?><!--/carte-offres.html">-->
<!--                        <img src="--><?php //echo RESSOURCE_URL; ?><!--/images/pictos/home-white.png" alt=""/><span-->
<!--                            class="and">+</span><img-->
<!--                            src="--><?php //echo RESSOURCE_URL; ?><!--/images/pictos/leaf-white.png" alt="" class="leaf"/><span>Afficher les offres de terrains + maisons</span>-->
<!--                    </a>-->
<!--                </div>-->
<!--                <div id="gmap" class="no-mobile"></div>-->
<!--            </div>-->
<!--        </div>-->
<!--        <div class="col-right-home">-->
<!--            <div id="container-actu">-->
<!--                <h2>Les news Kerbea</h2>-->
<!--                --><?php //if ($select_actualite->rowCount()): ?>
<!--                    <a href="--><?php //echo RESSOURCE_URL; ?><!--/actualites" class="all-news">toutes les news</a>-->
<!--                    <div id="slider_actu">-->
<!--                        <div id="slider_actu_clip">-->
<!--                            --><?php
//                            foreach ($select_actualite as $actualite):
//                                $image_actu = !empty($actualite['image1_actu']) ? RESSOURCE_URL . '/medias/actualite/galerie/moyenne/' . $actualite['image1_actu'] : RESSOURCE_URL . '/images/contenu/no-actualite.png';
//                                $link_actu = RESSOURCE_URL . '/actualites/' . rewrite_nom($actualite['titre_actu'] . '-' . $actualite['id_actu']);
//                                ?>
<!--                                <div class="slider_actu_item">-->
<!---->
<!--                                    <div class="image"><img src="--><?php //echo $image_actu; ?><!--" alt="Image d'actualité"/>-->
<!--                                    </div>-->
<!--                                    <div class="text">--><?php //echo $actualite['descriptionCourte_actu']; ?><!--</div>-->
<!--                                    --><?php //echo createLink('lire la suite', $link_actu, 'link'); ?>
<!--                                </div>-->
<!--                            --><?php //endforeach; ?>
<!--                        </div>-->
<!--                    </div>-->
<!--                --><?php //else : ?>
<!--                    <div>Il n'y a aucune actualité pour le moment.</div>-->
<!--                --><?php //endif; ?>
<!--            </div>-->
<!--            --><?php //if (count($select_agence)): ?>
<!--                <div class="nb-agence"><strong>--><?php //echo count($select_agence) ?><!-- agences Kerbéa</strong> pour réaliser-->
<!--                    votre projet-->
<!--                </div>-->
<!--            --><?php //endif; ?>
<!--        </div>-->
<!--        <div class="both"></div>-->
<!--    </div>-->
<!--    <div class="hr-line-gray"></div>-->
    <div class="site-width">
        <div class="dyna_center">
            <?php include(BASE_URL . WORK_DIR . '/includes/dynapage_system.inc.php'); ?>
        </div>
    </div>
</div>
<?php include(BASE_URL . WORK_DIR . '/includes/footer.inc.php'); ?>
</body>
</html>
