<?php
	include('../base_url.php');
	include(BASE_URL.'/conf/conf.php');
	include(BASE_URL.'/conf/connexion.php');
	include(BASE_URL.'/conf/fonctions.php');
	
	include(BASE_URL.WORK_DIR.'/config/langues.cfg.php');
//	include(BASE_URL.WORK_DIR.'/includes/session.php');
	include(BASE_URL.WORK_DIR.'/config/grid.conf.php');

    $scanDirDyna = array_diff(scandir(BASE_URL.WORK_DIR.'/images/back_dyna/'),array('.','..'));
    $randomBack = array_rand($scanDirDyna);
    $imgBackDyna = $scanDirDyna[$randomBack];

    $environnement_agence['background_image'] = RESSOURCE_URL.'/images/back_dyna/'.$imgBackDyna;

	// tableau de selections des requetes a faire dans sql_commun.php
	$array_requetes = array();
	
	if( empty( $_SESSION[ 'id_section' ] )) $_SESSION[ 'id_section' ] = 1;
	
	if(isset($_GET['l']) && array_key_exists($_GET['l'],$array_langues)){
		$_SESSION['langue'] = array_key_exists($_GET['l'], $array_langues) ? $_GET['l'] : DEFAULT_LANG;
	}
	else{
		$_SESSION['langue'] = DEFAULT_LANG;
	}
	
	$id_menu_sel = $id_page_sel = '';
	
	if(isset($_GET['alias'])){
		// Récupération dans la base de l'id page en fonction de l'alias
		$select_id_page = $PDO -> prepare('SELECT id_page FROM dyna_page WHERE alias_page = :alias_page');
		$select_id_page -> execute(array('alias_page' => $_GET['alias']));
		$count_id_page = $select_id_page -> rowCount();
		$select_id_page = $select_id_page -> fetch();
		
		if(empty($count_id_page)){
			header('location:'.RESSOURCE_URL.'/introuvable.php');
			exit();
		}
		
		$id_page_sel = $select_id_page['id_page'];
		
		/* Cas spécifique au changement de menu eyestore / eyewear */
		if( $_GET[ 'alias' ] == 'home-store' || $_GET[ 'alias' ] == 'accueil-store'){
			$_SESSION[ 'id_section' ] = 2;
		}
	}
	else{
		header('location:'.RESSOURCE_URL.'/introuvable.php');
		exit;
	}
	
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
    $result_blocs = $PDO->prepare("SELECT *
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
    $select_image_data = $PDO -> prepare( 'SELECT image.*,
        image_data.name_imageData, image_data.value_imageData
        FROM image
        LEFT JOIN image_data ON image.id_image = image_data.id_image
        WHERE image.nom_module = "dynapage"
        AND image.id_liaison IN ('.$qMarks.')
        ORDER BY idOrdre_image DESC' );

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

	$seo['upline'] = !empty($result_blocs[0]['nom_page']) ? $result_blocs[0]['nom_page'] : '';
	$seo['title'] = $seo['upline'].' | maisons-kerbea.';
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
		<meta name="description" content="<?php echo $seo['description']; ?>" />
		<meta name="keywords" content="<?php echo $seo['keywords']; ?>" />
		<meta name="robots" content="index, follow" />
		
		<?php include(BASE_URL.WORK_DIR.'/includes/include_css_js_commun.inc.php'); ?>
		
		<link rel="stylesheet" href="<?php echo RESSOURCE_URL; ?>/css/grid_structure.css.php?id=<?php echo $id_page_sel.'&amp;token='.uniqid(10); ?>" />
	</head>
    <body>
    <?php include(BASE_URL.WORK_DIR.'/includes/header.inc.php'); ?>
    <div id="container-content">

        <?php if(!empty($environnement_agence['background_image'])):?>
            <div class="img-back-agence"><img src="<?php echo $environnement_agence['background_image']; ?>" alt=""/></div>
        <?php endif; ?>

        <div class="content-hover">
            <div class="site-width">
                <div class="breadcrumbs"><a href="<?php echo RESSOURCE_URL; ?>">Maisons Kerbéa</a><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-red.png" alt=""/><?php echo $seo['upline']; ?></div>
                <h1 class="black-title"><?php echo $seo['upline'];  ?></h1>
                <div class="both"></div>
                <?php include(BASE_URL.WORK_DIR.'/includes/dynapage_system.inc.php'); ?>
                <?php /*<div class="col-right"><?php include(BASE_URL.WORK_DIR.'/includes/dynapage_include_system.inc.php'); ?></div> */?>
            </div>
        </div>
    </div>
    <?php include(BASE_URL.WORK_DIR.'/includes/footer.inc.php'); ?>
    </body>
</html>