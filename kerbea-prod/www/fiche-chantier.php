<?php
include('../base_url.php');
include(BASE_URL.'/conf/conf.php');
include(BASE_URL.'/conf/connexion.php');
include(BASE_URL.'/conf/fonctions.php');

include(BASE_URL.WORK_DIR.'/config/langues.cfg.php');
include(BASE_URL.WORK_DIR.'/includes/session.php');
include(BASE_URL.WORK_DIR.'/config/grid.conf.php');

$_SESSION['langue'] = DEFAULT_LANG;
$environnement_agence['menu'] = array('active' => 4);

if(empty($_GET['id'])){
    header('location:'.RESSOURCE_URL);
    exit;
}

$id_realisation = $_GET['id'];
$select_realisation = $PDO -> prepare('SELECT * FROM realisation WHERE id_realisation = :id_realisation AND actif_realisation = 1');
$select_realisation -> execute(array('id_realisation' => $_GET['id']));

if($select_realisation -> rowCount() == 0){
    $link_to_realisation = !empty($_SESSION['agence']['alias']) ? RESSOURCE_URL.'/agence-'.$_SESSION['agence']['alias'].'/realisations' : RESSOURCE_URL.'/realisations';
    header('location:'.$link_to_realisation);
    exit;
}

$line_realisation = $select_realisation -> fetch();

// Tableau de sélection du menu lié en fonction de la langue (il faut indiquer les 2 (menu et page) pour les pages hybrides
$array_menu_sel_langue = array('fr' => 5);$array_page_sel_langue = array('fr' => 0);

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

$seo['upline'] = $seo['title'] = 'Chantier '.$line_realisation['nom_realisation'].' | Maisons Kerbea';
if('' != ALIAS_URL) $seo['upline'] = $seo['title'] .= ' '.$select_infos_agence['nom_agence'];
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


?>

<!DOCTYPE HTML>
<html lang="fr-FR">
	<head>
		<meta charset="UTF-8" />
		<title><?php echo $seo['title']; ?></title>
		
		<?php include(BASE_URL.WORK_DIR.'/includes/include_css_js_commun.inc.php'); ?>
		<link rel="stylesheet" href="<?php echo RESSOURCE_URL; ?>/css/grid_structure.css.php?id=<?php echo $id_page_sel.'&amp;token='.uniqid(10); ?>" />
        <link rel="stylesheet" href="<?php echo RESSOURCE_URL; ?>/css/form.css"/>

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

                /* Actions au démarrage */
                $('#container_vignette .vignette:first').addClass( 'actif' );
                
            });
        </script>

	</head>
	<body>
       
		<?php include(BASE_URL.WORK_DIR.'/includes/header.inc.php'); ?>
        <div id="container-content">
            <?php if(!empty($environnement_agence['background_image'])):?>
                <div class="img-back-agence"><img src="<?php echo $environnement_agence['background_image']; ?>" alt=""/></div>
            <?php endif; ?>

            <div class="content-hover">
                <div class="site-width relative">
                    <div class="breadcrumbs"><a href="<?php echo RESSOURCE_URL; if(!empty($_SESSION['agence']['alias'])){ echo '/agence-'.$_SESSION['agence']['alias'];} ?>">Maisons Kerbéa<?php if(!empty($_SESSION['agence']['alias'])){ echo ' '.$_SESSION['agence']['alias'];} ?></a><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-red.png" alt=""/><a href="<?php echo RESSOURCE_URL; if(!empty($_SESSION['agence']['alias'])){ echo '/agence-'.$_SESSION['agence']['alias'];} ?>/chantier">Chantier</a><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-red.png" alt=""/><?php echo $line_realisation['nom_realisation']; ?></div>

                    <h1 class="black-title"><?php echo $line_realisation['nom_realisation']; ?></h1>
                    <?php include(BASE_URL.WORK_DIR.'/includes/share.php'); ?>
                    <div class="fiche">
                        <div class="medias">
                            <div class="part-left">
                                <div id="slider_realisation">
                                    <div id="slider_realisation_clip">
                                        <?php
                                        for($i = 1; $i <= 8; $i++){
                                            if(!empty($line_realisation['image'.$i.'_realisation']))
                                                echo '<div class="slider_realisation_item">
                                                <a class="" data-lightbox="Images chantier" href="'.RESSOURCE_URL.'/medias/realisation/galerie/grande/'.$line_realisation['image'.$i.'_realisation'].'">
                                                    <img src="'.RESSOURCE_URL.'/medias/realisation/galerie/moyenne-high/'.$line_realisation['image'.$i.'_realisation'].'" alt=""/>
                                                </a>
                                            </div>';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="part-right">
                                <div id="container_vignette" class="no-mobile">
                                    <?php
                                    for($i = 1; $i <= 8; $i++){
                                        if(!empty($line_realisation['image'.$i.'_realisation']))
                                            echo '<div class="vignette galerie"><img src="'.RESSOURCE_URL.'/medias/realisation/galerie/vignette/'.$line_realisation['image'.$i.'_realisation'].'" alt=""/></div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="both"></div>
                        </div>
                        <div class="description">
                            <div class="part-left">
                                <div class="text"><?php echo $line_realisation['description_realisation']; ?></div>
                                <div class="relative no-mobile">
                                    <div class="bb"></div>
                                </div>
                            </div>
                            <div class="part-right">

                                <?php if(!empty($select_infos_agence)): ?>
                                    <div class="align_c">
                                        <?php
                                        echo '<strong>MAISONS KERBÉA '.mb_strtoupper($select_infos_agence['nom_agence'],'utf8').'</strong>'.
                                            '<br /><br />'.$select_infos_agence['adresse_agence'].
                                            '<br />'.$select_infos_agence['codePostal_agence'].' '.$select_infos_agence['ville_agence'].
                                            '<br /><br />Tél : '.$select_infos_agence['telephone_agence'];

                                        if(!empty($line_offre['fax_agence'])){
                                            echo '<br />Fax : '.$select_infos_agence['fax_agence'];
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
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