<?php
include('../base_url.php');
include(BASE_URL.'/conf/conf.php');
include(BASE_URL.'/conf/connexion.php');
include(BASE_URL.'/conf/fonctions.php');

include(BASE_URL.WORK_DIR.'/config/langues.cfg.php');
include(BASE_URL.WORK_DIR.'/includes/session.php');
include(BASE_URL.WORK_DIR.'/config/grid.conf.php');

$_SESSION['langue'] = DEFAULT_LANG;
$environnement_agence['menu'] = array('active' => 1);

$id_gamme = intval($_GET['id']);
$select_info_gamme = $PDO -> prepare('SELECT * FROM gamme WHERE id_gamme = :id_gamme');
$select_info_gamme -> execute(array('id_gamme' => $id_gamme));

if(!$select_info_gamme -> rowCount()){
    header('location:'.RESSOURCE_URL);
    exit;
}

$line_info_gamme = $select_info_gamme -> fetch();

// Tableau de sélection du menu lié en fonction de la langue (il faut indiquer les 2 (menu et page) pour les pages hybrides
$array_menu_sel_langue = array('fr' => 5);$array_page_sel_langue = array('fr' => 13);

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

$seo['upline'] = $seo['title'] = 'Nos modèles de maison | Gamme '.strtoupper($line_info_gamme['nom_gamme']).' | Maisons Kerbea';
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


// Si connecté en agence
if($_SESSION['agence']['session']){
    $select_modeles = $PDO -> prepare('
    SELECT *
    FROM modele_maison
    JOIN gamme ON gamme.id_gamme = modele_maison.id_gamme
    JOIN modele_par_agence ON modele_par_agence.id_modele_maison = modele_maison.id_modele_maison
    JOIN agence ON agence.id_agence = modele_par_agence.id_agence
    WHERE publier_modele_maison = 1
    AND agence.id_agence = :id_agence
    AND gamme.id_gamme = :id_gamme
    ORDER BY idOrdre_gamme DESC, idOrdre_modele_maison DESC');
    $select_modeles -> execute(array('id_gamme' => $id_gamme, 'id_agence' => $select_infos_agence['id_agence']));
}else{
    $select_modeles = $PDO -> prepare('
    SELECT *
    FROM modele_maison
    JOIN gamme ON gamme.id_gamme = modele_maison.id_gamme
    WHERE publier_modele_maison = 1
    AND gamme.id_gamme = :id_gamme
    ORDER BY idOrdre_gamme DESC, idOrdre_modele_maison DESC');
    $select_modeles -> execute(array('id_gamme' => $id_gamme));
}
$select_modeles = $select_modeles -> fetchAll();

$select_variante_modele = $PDO -> prepare('
  SELECT variante_modele.id_modele_maison,
    variante_modele.nom_variante_modele,
    variante_modele.id_variante_modele,
    variante_modele.specificites_variante_modele
  FROM variante_modele
  JOIN modele_maison ON variante_modele.id_modele_maison = modele_maison.id_modele_maison
  JOIN gamme ON modele_maison.id_gamme = gamme.id_gamme
  WHERE publier_modele_maison = 1
  AND gamme.id_gamme = :id_gamme');
$select_variante_modele -> execute(array('id_gamme' => $id_gamme));

$count_variante_modele = $select_variante_modele -> rowCount();
$select_variante_modele = $select_variante_modele -> fetchAll();


// Breadcrumbs
$breadcrumbs = $_SESSION['agence']['session'] ? '<a href="'.RESSOURCE_URL.'/agence-'.$_SESSION['agence']['alias'].'">Maisons Kerbéa '.$_SESSION['agence']['alias'].'</a><img src="'.RESSOURCE_URL.'/images/pictos/fl-r-red.png" alt=""/>Modèles de maisons - Gamme '.strtoupper($line_info_gamme['nom_gamme']) : '<a href="'.RESSOURCE_URL.'">Maisons Kerbéa</a><img src="'.RESSOURCE_URL.'/images/pictos/fl-r-red.png" alt=""/>Modèles de maisons - Gamme '.strtoupper($line_info_gamme['nom_gamme']);

$ogimg = $select_modeles[0];


// Si agence, selection des premieres images correspondante au style voulue pour chaque modele de maison
if(!empty($_SESSION['agence']['alias'])){
    $select_image_style_selected = $PDO -> prepare('
      SELECT style_par_modele_maison.id_modele_maison, image_modele_maison
      FROM modele_par_agence
      JOIN style_par_modele_maison ON modele_par_agence.id_stylemodmai = style_par_modele_maison.id_stylemodmai
      WHERE id_agence = :id_agence
      ORDER BY style_par_modele_maison.id_modele_maison ASC, image_modele_maison ASC
    ');

    $select_image_style_selected -> execute(array('id_agence' => $_SESSION['agence']['id']));
    $select_image_style_selected = $select_image_style_selected -> fetchAll();
}
?>

<!DOCTYPE HTML>
<html lang="fr-FR">
	<head>
		<meta charset="UTF-8" />
		<title><?php echo $seo['title']; ?></title>
        <meta name="description" content="<?php echo $seo['description']; ?>" />
        <meta name="keywords" content="<?php echo $seo['keywords']; ?>" />

        <meta property="og:title" content="Maisons individuelles <?php echo $line_info_gamme['nom_gamme']; ?>" />
        <meta property="og:description" content="Maisons individuelles <?php echo $line_info_gamme['nom_gamme']; ?>" />
        <meta property="og:url" content="<?php echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];?>" />
        <meta property="og:image" content="<?php echo RESSOURCE_URL.'/medias/modele_maison/galerie/grande/'.$ogimg['image1_modele_maison']; ?>" />
		<?php include(BASE_URL.WORK_DIR.'/includes/include_css_js_commun.inc.php'); ?>
		<link rel="stylesheet" href="<?php echo RESSOURCE_URL; ?>/css/grid_structure.css.php?id=<?php echo $id_page_sel.'&amp;token='.uniqid(10); ?>" />

        <script type="text/javascript" src="<?php echo RESSOURCE_URL; ?>/js/jquery.placeholder.js"></script>
        <script type="text/javascript" src="<?php echo RESSOURCE_URL; ?>/js/pages/modele_maison.js"></script>
        <script src="https://apis.google.com/js/platform.js" async defer>{lang: 'fr'}
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
                    <div class="breadcrumbs"><?php echo $breadcrumbs; ?></div>

                    <h1 class="black-title">Gamme <?php echo $line_info_gamme['nom_gamme']; ?></h1>
                    <div class="site-width no-height">
                        <div class="dyna_center">
                            <?php include(BASE_URL.WORK_DIR.'/includes/dynapage_system.inc.php'); ?>
                        </div>
                    </div>
                    <div class="list two">
                        <?php include(BASE_URL.WORK_DIR.'/includes/share.php'); ?>

                        <div class="container-filtre bdb">
                            <div class="variante">
                                <div class="title">Filtrer :</div>
                                <div class="filtre active" data-variante="all"><span class="picto"></span>Tous les modèles</div>
                                <div class="filtre" data-variante="pied"><span class="picto"></span>Plain pied</div>
                                <div class="filtre" data-variante="comble"><span class="picto"></span>Combles aménagés</div>
                                <div class="filtre" data-variante="etage"><span class="picto"></span>Étage</div>
                                <div class="filtre" data-variante="garage"><span class="picto"></span>Garage intégré</div>
                                <div class="both"></div>
                            </div>
                        </div>
                        <div class="items">

                        <?php if(count($select_modeles)): ?>
                            <!--
                            <?php
                            $i = 1;

                            foreach( $select_modeles as $modele_maison):
                                $image = RESSOURCE_URL;
                                $image_num_found = false;
                                if(isset($select_image_style_selected) && count($select_image_style_selected)){
                                    foreach($select_image_style_selected as $image_selected){
                                        if($modele_maison['id_modele_maison'] == $image_selected['id_modele_maison'] && !empty($modele_maison['image'.$image_selected['image_modele_maison'].'_modele_maison'])){
                                            $image .= '/medias/modele_maison/galerie/moyenne/'.$modele_maison['image'.$image_selected['image_modele_maison'].'_modele_maison'];
                                            $image_num_found = true;
                                            break;
                                        }
                                    }
                                }
                                if(!$image_num_found){
                                    $image = !empty($modele_maison['image1_modele_maison']) ? '/medias/modele_maison/galerie/moyenne/'.$modele_maison['image1_modele_maison'] : './images/contenu/kerbea-no-image-modele.jpg';
                                }
                                $link = $_SESSION['agence']['session'] ?
                                    RESSOURCE_URL.'/agence-'.$_GET['alias'].'/maisons-individuelles/'.strtolower(rewrite_nom($modele_maison['alias_modele_maison'])) :
                                    RESSOURCE_URL.'/maisons-individuelles/'.strtolower(rewrite_nom($modele_maison['alias_modele_maison']));

                                $html_generate_data = '';

                                $flt_etage = $flt_combleA = $flt_plainPied = $flt_garage = false;

                                if(!empty($count_variante_modele)){
                                    foreach($select_variante_modele as $variante){
                                        if($variante['id_modele_maison'] != $modele_maison['id_modele_maison'] || empty($variante['specificites_variante_modele'])){
                                            continue;
                                        }
                                        $array_spec = explode(',',$variante['specificites_variante_modele']);
                                        if(in_array('étage', $array_spec)){ $flt_etage = true; }
                                        if(in_array('combles aménagés', $array_spec)){ $flt_combleA = true; }
                                        if(in_array('plain pied', $array_spec)){ $flt_plainPied = true; }
                                        if(in_array('garage intégré', $array_spec)){ $flt_garage = true; }

                                        if($flt_etage && $flt_combleA && $flt_plainPied && $flt_garage){
                                            break;
                                        }
                                    }
                                }

                                $html_generate_data .= $flt_etage ? ' data-etage="1"' : '';
                                $html_generate_data .= $flt_combleA ? ' data-comble="1"' : '';
                                $html_generate_data .= $flt_plainPied ? ' data-pied="1"' : '';
                                $html_generate_data .= $flt_garage ? ' data-garage="1"' : '';

                                $type_item = $i == 3  || $i == 4 ? ' alternate' : '';
                                ?>

                                --><div class="list-item<?php echo $type_item; ?>"<?php echo $html_generate_data; ?>>
                                    <div class="image">
                                        <?php echo createLink('<span class="bg-black modele"></span>', $link); ?>
                                        <?php echo createLink('<img src="'.$image.'" alt=""/>', $link); ?>
                                    </div>

                                    <div class="content">
                                        <div class="title"><?php echo $modele_maison['nom_modele_maison']; ?></div>
                                        <?php echo createLink('+', $link,'link'); ?>
                                    </div>
                                </div><!--
                            <?php
                            $i = $i + 1 == 5 ? 1 : $i + 1;
                            endforeach; ?>
                        -->
                        <?php else: ?>
                            Aucune offre n'est disponible pour le moment.
                        <?php endif; ?>
                    </div>
                    </div>
                </div>
            </div>
        </div>

		<?php include(BASE_URL.WORK_DIR.'/includes/footer.inc.php'); ?>
	</body>
</html>