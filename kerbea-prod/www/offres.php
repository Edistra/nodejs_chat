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


// Tableau de sélection du menu lié en fonction de la langue (il faut indiquer les 2 (menu et page) pour les pages hybrides
$array_menu_sel_langue = array('fr' => 51);
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

$seo['upline'] = $seo['title'] = 'Nos offres | Maisons Kerbea '.(!empty($select_infos_agence['nom_agence']) ? $select_infos_agence['nom_agence'] : '');
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
$limit = '';

$select_offres = $PDO -> prepare('
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
    agence.alias_agence,
    agence.nom_agence,
    departement.departement_nom
FROM offre
LEFT JOIN modele_maison ON offre.id_modele_maison = modele_maison.id_modele_maison
LEFT JOIN agence ON offre.id_agence = agence.id_agence
LEFT JOIN departement ON LEFT(offre.codePostal_offre,2) = departement.departement_code
WHERE 1 = 1
'.$where_agence.'
AND actif_offre = 1
ORDER BY idOrdre_offre DESC ');
$select_offres -> execute();
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

        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.13&libraries=geometry,places&sensor=false"></script>
        <script type="text/javascript" src="<?php echo RESSOURCE_URL; ?>/js/jquery.placeholder.js"></script>
        <script type="text/javascript" src="<?php echo RESSOURCE_URL; ?>/js/pages/offres.js"></script>
	</head>
	<body>
		<?php include(BASE_URL.WORK_DIR.'/includes/header.inc.php'); ?>
        <div id="container-content">
            <?php if(!empty($environnement_agence['background_image'])):?>
                <div class="img-back-agence"><img src="<?php echo $environnement_agence['background_image']; ?>" alt=""/></div>
            <?php endif; ?>

            <div class="content-hover">
                <div class="site-width relative">
                
                    <div class="breadcrumbs"><a href="<?php echo RESSOURCE_URL; if(!empty($_SESSION['agence']['alias'])){ echo '/agence-'.$_SESSION['agence']['alias'];} ?>">Maisons Kerbéa<?php if(!empty($_SESSION['agence']['alias'])){ echo ' '.$_SESSION['agence']['alias'];} ?></a><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-red.png" alt=""/>Liste des Offres</div>

                    <h1 class="black-title">Nos offres de maisons</h1>
                    <div class="list four">
                        <?php include(BASE_URL.WORK_DIR.'/includes/share.php'); ?>
                        <div class="align_r"><a class="see-other-mode" href="<?php echo RESSOURCE_URL; if(!empty($_SESSION['agence']['alias'])){ echo '/agence-'.$_SESSION['agence']['alias']; }?>/carte-offres.html">Voir les offres sur la carte <img src="<?php echo RESSOURCE_URL; ?>/images/pictos/map-small-black.png" alt=""/></a></div>

                        <div class="container-filtre">
                            <div class="type mrg_b_s">
                                <div class="title">Type :</div>
                                <div class="filtre active" data-type="all"><span class="picto"></span>Tous types</div>
                                <div class="filtre" data-type="maison"><span class="picto"></span>Terrain+maison</div>
                                <div class="filtre" data-type="terrain"><span class="picto"></span>Terrain seul</div>
                                <div class="both"></div>
                            </div>
                            <div class="variante mrg_b_s">
                                <div class="title">Variantes :</div>
                                <div class="filtre active" data-variante="all"><span class="picto"></span>Toutes les offres</div>
                                <div class="filtre" data-variante="pied"><span class="picto"></span>Plain pied</div>
                                <div class="filtre" data-variante="comble"><span class="picto"></span>Combles aménagés</div>
                                <div class="filtre" data-variante="etage"><span class="picto"></span>Étage</div>
                                <div class="filtre" data-variante="garage"><span class="picto"></span>Garage intégré</div>
                                <div class="both"></div>
                            </div>
                            
                            <div>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="critereBien_form">

                                    <div class="input"><input type="text" name="searchPlace" id="searchPlace" value="" placeholder="Entrez un lieu"/></div>
                                    <div class="input"><input type="text" name="prixMax" id="prixMax" value="" placeholder="budget maxi"/> €</div>
                                    <div class="input"><input type="text" name="mensualiteMax" id="mensualiteMax" value="" placeholder="mensualité maxi"/> €</div>
                                    <div class="input"><input type="text" name="surface_m" id="surface_m" value="" placeholder="surface terrain mini"/> <span class="lowercase">m²</span></div>
                                    <div class="input" id="input_surface_t"><input type="text" name="surface_t" id="surface_t" value="" placeholder="surface maison mini"/> <span class="lowercase">m²</span></div>

                                    <input type="submit" value="valider"/>
                                    <div class="raz">supprimer les critères</div>
                                    <div class="both"></div>
                                </form>
                            </div>
                        </div>
                        <div class="navigation-page"></div>
                        <div class="items">
                        <?php if($select_offres -> rowCount()): $select_offres = $select_offres -> fetchAll(); ?>
                            <!--
                            <?php //$temp_test = 10; while($temp_test --): ?>
                            <?php foreach( $select_offres as $offre):

                                if(!empty($offre['image_modele_maison']) && !empty($offre['id_modele_maison'])){
                                    $image_offre = RESSOURCE_URL.'/medias/modele_maison/galerie/moyenne-offre/'.$offre['image'.$offre['image_modele_maison'].'_modele_maison'];
                                }
                                elseif(!empty($offre['image1_offre'])){
                                    $image_offre = RESSOURCE_URL.'/medias/offre/galerie/moyenne/'.$offre['image1_offre'];
                                }
                                else{
                                    $image_offre = RESSOURCE_URL.'/images/contenu/kerbea-no-image-offre.jpg';
                                }

//                                $url_offre = !empty($_SESSION['agence']['alias']) ? RESSOURCE_URL.'/agence-'.$_SESSION['agence']['alias'] : RESSOURCE_URL;
                                $url_offre = RESSOURCE_URL.'/agence-'.$offre['alias_agence'];
                                $url_offre .= '/annonces-terrain-maison/'.rewrite_nom($offre['ville_offre']).'-'.$offre['id_offre'];
//                                $url_offre = '#';

                                $data_offre = array();
                                $html_generate_data = '';

                                $data_offre['cp'] = $offre['codePostal_offre'];
                                $data_offre['ville'] = mb_strtolower($offre['ville_offre'], 'UTF-8');
                                $data_offre['departement'] = substr($offre['codePostal_offre'],0,2);
                                $data_offre['surface_t'] = $offre['surfaceTerrain_offre'];
                                $data_offre['surface_m'] = $offre['surfaceMaison_offre'];
                                $data_offre['type'] = $offre['type_offre'] == 'terrain+maison' ? 'maison' : 'terrain';
                                $data_offre['prix'] = $offre['prix_offre'];
                                $data_offre['mensualite'] = $offre['prix_mensuel_offre'];
                                $data_offre['departement_nom'] = mb_strtolower($offre['departement_nom'], 'UTF-8');

                                foreach($data_offre as $index => $value){
                                    if(in_array($index, array('prix', 'mensualite')) && $value == 0){
                                        continue(1);
                                    }
                                    $html_generate_data .= ' data-'.$index.'="'.$value.'"';
                                }

                                $specificites_array = explode(',',$offre['specificites_offre']);
                                $html_generate_data .= in_array('étage', $specificites_array) ? ' data-etage="1"' : '';
                                $html_generate_data .= in_array('combles aménagés', $specificites_array) ? ' data-comble="1"' : '';
                                $html_generate_data .= in_array('plain pied', $specificites_array) ? ' data-pied="1"' : '';
                                $html_generate_data .= in_array('garage intégré', $specificites_array) ? ' data-garage="1"' : '';
                                $html_generate_data .= ' data-type="'.$data_offre['type'].'"';
                                ?>
                                --><div class="list-item"<?php echo $html_generate_data; ?>>
                                    <div class="image">
                                        <?php echo createLink('<span class="bg-black offre"></span>', $url_offre); ?>
                                        <?php echo createLink('<img src="'.$image_offre.'" alt="Offre agence '.$offre['nom_agence'].', '.$offre['ville_offre'].'"/>', $url_offre); ?>
                                        <div class="type"><?php echo ucfirst(str_replace('+',' + ', $offre['type_offre'])); ?></div>
                                    </div>
                                    <div class="content">
                                        <div class="title"><?php echo $offre['ville_offre']; ?></div>
                                        <div class="text"><?php echo $offre['descriptionCourte_offre']; ?></div>
                                        <?php echo (!empty($offre['prix_mensuel_offre']) ? '<div class="price">à partir de <strong>'.number_format($offre['prix_mensuel_offre'],0,'',' ').'&euro; /mois</strong></div>' : '<div class="price">à partir de <strong>'.number_format($offre['prix_offre'],0,'',' ').'&euro;</strong></div>'); ?>
<!--                                        <div class="price">Prix à partir de --><?php //echo number_format($offre['prix_offre'],0,'',' '); ?><!-- &euro;</div>-->
                                        <?php echo createLink('+', $url_offre,'link'); ?>
                                    </div>
                                </div><!--
                            <?php endforeach; ?>
                        <?php //endwhile; ?>
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