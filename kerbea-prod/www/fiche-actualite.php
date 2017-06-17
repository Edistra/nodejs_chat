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

if(empty($_GET['id'])){
    header('location:'.RESSOURCE_URL);
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

$seo['upline'] = $seo['title'] = 'Fiche actualité | maisons-kerbea';
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

$select_actualite = $PDO -> query('SELECT *
FROM actualite
WHERE publier_actu = 1
AND id_actu = "'.$_GET['id'].'"
AND (
    (dateDebut_actu<= NOW() AND dateFin_actu > NOW())
    OR (dateDebut_actu = "0000-00-00" AND dateFin_actu = "0000-00-00")
    OR (dateDebut_actu <= NOW() AND dateFin_actu = "0000-00-00")
    OR (dateDebut_actu = "0000-00-00" AND dateFin_actu > NOW())
)
ORDER BY idOrdre_actu DESC;');
$actualite = $select_actualite -> fetch();
?>

<!DOCTYPE HTML>
<html lang="fr-FR">
<head>
    <meta charset="UTF-8" />
    <title><?php echo $seo['title']; ?></title>

    <?php include(BASE_URL.WORK_DIR.'/includes/include_css_js_commun.inc.php'); ?>
    <link rel="stylesheet" href="<?php echo RESSOURCE_URL; ?>/css/grid_structure.css.php?id=<?php echo $id_page_sel.'&amp;token='.uniqid(10); ?>" />

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
        <div class="img-back-agence no-mobile"><img src="<?php echo $environnement_agence['background_image']; ?>" alt=""/></div>
    <?php endif; ?>

    <div class="content-hover">
        <div class="site-width">
            <div class="breadcrumbs"><a href="<?php echo RESSOURCE_URL;if(!empty($_SESSION['agence']['alias'])){ echo '/agence-'.$_SESSION['agence']['alias'];} ?>">Maisons Kerbéa<?php if(!empty($_SESSION['agence']['alias'])){ echo ' '.$_SESSION['agence']['alias'];} ?></a><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-red.png" alt=""/><a href="<?php echo RESSOURCE_URL;if(!empty($_SESSION['agence']['alias'])){ echo '/agence-'.$_SESSION['agence']['alias'];} ?>/actualites">Listes des actualités</a><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-red.png" alt=""/><?php echo $actualite['titre_actu']; ?></div>
            <h1 class="black-title"><?php echo $actualite['titre_actu']; ?></h1>
            <div class="fiche">
                <a class="btn_retour no-mobile no-tablette" href="<?php echo RESSOURCE_URL;if(!empty($_SESSION['agence']['alias'])){ echo '/agence-'.$_SESSION['agence']['alias'];} ?>/actualites">Liste des actualités</a>
                <div id="container-actu" class="list-actu">
                    <?php $image_actu = !empty($actualite['image1_actu']) ? RESSOURCE_URL.'/medias/actualite/galerie/moyenne/'.$actualite['image1_actu'] : RESSOURCE_URL.'/images/contenu/no-actualite.png'; ?>
                    <div class="actu">
                        <div class="image">
                            <?php if(!empty($actualite['image1_actu'])): ?>
                            <a data-lightbox="<?php echo $actualite['image1_actu'] ;?>" href="<?php echo RESSOURCE_URL.'/medias/actualite/galerie/grande/'.$actualite['image1_actu']; ?>">
                                <img src="<?php echo $image_actu; ?>" alt="Image d'actualité"/>
                            </a>
                            <?php else: ?>
                                <img src="<?php echo $image_actu; ?>" alt="Image d'actualité"/>
                            <?php endif; ?>
                        </div>
                        <div class="text"><?php echo $actualite['descriptionLongue_actu']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include(BASE_URL.WORK_DIR.'/includes/footer.inc.php'); ?>
</body>
</html>