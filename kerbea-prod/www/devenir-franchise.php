<?php
	include('../base_url.php');
	include(BASE_URL.'/conf/conf.php');
	include(BASE_URL.'/conf/connexion.php');
	include(BASE_URL.'/conf/fonctions.php');
    include(BASE_URL.WORK_DIR.'/phpmailer/class.phpmailer.php');
	
	include(BASE_URL.WORK_DIR.'/config/langues.cfg.php');
	include(BASE_URL.WORK_DIR.'/includes/session.php');
	include(BASE_URL.WORK_DIR.'/config/grid.conf.php');
	
	// tableau de selections des requetes a faire dans sql_commun.php
	$array_requetes = array();
	
	if( empty( $_SESSION[ 'id_section' ] )) $_SESSION[ 'id_section' ] = 1;

    $_SESSION['langue'] = DEFAULT_LANG;
	
	$id_menu_sel = '';
    $id_page_sel = '11';

    
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
	$seo['title'] = 'Devenir franchisé | maisons-kerbea';
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


/* ############################################### Traitement du formulaire d'inscription ############################################### */
/**
nom variable => ([0]=> valeur défaut (si le nom de l'input est écris directement dedans uniquement, sinon laisser vide), [1]=> requis ou non
 */
$array_variable_a_traiter = array(
    'prenom'=>array('',1),
    'nom'=>array('',1),
    'message'=>array('',0),
    'ville'=>array('',0),
    'postal'=>array('',0),
    'email'=>array('',1),
    'telephone'=>array('',0)
);

$msg_error = '';

// Récupération des variables
$form_post_ok = true;
foreach($array_variable_a_traiter as $variable_post => $variable_config){
    if(!empty($_POST[$variable_post]) && is_string($_POST[$variable_post])) $_POST[$variable_post] = trim($_POST[$variable_post]);
    $list[$variable_post] = !empty($_POST[$variable_post]) ? $_POST[$variable_post] : $variable_config[0];
    $list[$variable_post] = !is_array($list[$variable_post]) ? htmlspecialchars(trim($list[$variable_post])) : $list[$variable_post];
}

if(count($_POST)){
    // Vérification du formulaire
    foreach($array_variable_a_traiter as $variable_post => $variable_config){
        if(!empty($variable_config[1]) && ($list[$variable_post] == '' || $list[$variable_post] == $variable_config[0] || (!empty($variable_config[2]) && $list[$variable_post] =! $list[$variable_config[2]]))){
            $form_post_ok = false;
            $list[$variable_post] = $variable_config[0];

            $msg_error .= 'Veuillez remplir tous les champs obligatoires.<br />';
        }
    }

    if(!verif_email($list['email'])){
        $form_post_ok = false;
        $msg_error .= 'L\'email est invalide.<br />';
    }

    if($form_post_ok){
        try{
            
            $message_mail = '
					<table width="600" border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td valign="top">
								<h1 style="font-family:Arial;font-size:16px;color:#333;">[Demande franchisé] depuis le site Maisons Kerbea</h1>
								<h2 style="font-family:Arial;font-size:12px;color:#696a6a;">Identité</h2>
								<p style="font-family:Arial;font-size:12px;color:#000;">'.$list['prenom'].' '.$list['nom'].'</p>
								<h2 style="font-family:Arial;font-size:12px;color:#696a6a;">Coordonnées</h2>
								<p style="font-family:Arial;font-size:12px;color:#000;">Email : '.$list['email'].'</p>';
            $message_mail .= !empty($list['telephone']) ? '<p style="font-family:Arial;font-size:12px;color:#000;">Téléphone : '.$list['telephone'].'</p>' : '';
            $message_mail .= !empty($list['ville']) ? '<p style="font-family:Arial;font-size:12px;color:#000;">Ville : '.$list['ville'].'</p>' : '';
            $message_mail .= !empty($list['postal']) ? '<p style="font-family:Arial;font-size:12px;color:#000;">Code postal : '.$list['postal'].'</p>' : '';
            $message_mail .= '<p style="font-family:Arial;font-size:12px;color:#000;">Message : <br />'.nl2br($list['message']).'</p>
								<p>&nbsp;</p>
								<p style="font-family:Arial;font-size:11px;color:#000;">Cet email est envoyé par le site Maisons Kerbea, aucune réponse directe ne sera traitée.Veuillez vous référer à l\'email du contact présent ci-dessus.</p>
							</td>
						</tr>
				</table>';

                
            $mail_obj = new PHPmailer();
            $mail_obj->IsMail();
            $mail_obj->IsHTML(true);
            $mail_obj->FromName = $list['prenom'].' '.$list['nom'];
            $mail_obj->From = 'no-reply@maisons-kerbea.fr';
            $mail_obj->AddAddress('developpement@kerbea.fr');
            $mail_obj->Subject='[Demande franchisé] Maisons Kerbea';
            $mail_obj->Body=$message_mail;

            if(!$mail_obj->Send()){
                unset($mail_obj);
            }
            
            header('location:'.RESSOURCE_URL.'/devenir-franchise.php?sendok');
            exit;
        }
        catch(Exception $e){
            $msg_error = 'Une erreur est survenue lors de l\'envoi de votre message.<br />Merci de rééssayer ultérieurement.<br />';

            $file = fopen(BASE_URL."/conf/logs/error_email_franchise.txt","a");
            fwrite($file,"[".date("d/m/y G:i:s",time())."] erreur : ".$e -> getMessage()." - URI : ".$_SERVER['REQUEST_URI']."\r\n");
            fclose($file);
        }
    }
}
elseif(isset($_GET['sendok'])){
    $msg_error = 'Nous vous remercions pour l’intérêt que vous portez à Maisons Kerbea. Nos conseillers reviendront vers vous dans les plus brefs délais.<br />';
}


/* ############################################### REQUETES DIVERSES ############################################### */


?>
<!DOCTYPE HTML>
<html lang="fr-FR">
	<head>
		<meta charset="UTF-8" />
		<title><?php echo $seo['title']; ?></title>
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<meta name="robots" content="index, follow" />
		
		<?php include(BASE_URL.WORK_DIR.'/includes/include_css_js_commun.inc.php'); ?>
        <link rel="stylesheet" href="<?php echo RESSOURCE_URL; ?>/css/form.css" type="text/css">
		
		<link rel="stylesheet" href="<?php echo RESSOURCE_URL; ?>/css/grid_structure.css.php?id=<?php echo $id_page_sel.'&amp;token='.uniqid(10); ?>" />
        <script type="text/javascript" src="<?php echo RESSOURCE_URL; ?>/js/jquery.placeholder.js"></script>
        <script type="text/javascript" src="<?php echo RESSOURCE_URL; ?>/js/jquery.validate.min.js"></script>
        <script type="text/javascript" src="<?php echo RESSOURCE_URL; ?>/js/js_validate.js"></script>
	</head>
    <body>
    <?php include(BASE_URL.WORK_DIR.'/includes/header.inc.php'); ?>
    <div id="container-content">

        <div class="img-back-agence no-mobile"><img src="<?php echo $environnement_agence['background_image']; ?>" alt=""/></div>
        
        <div class="page-contact content-hover">
            <div class="site-width">
            
                <div class="breadcrumbs"><a href="<?php echo RESSOURCE_URL; ?>">Maisons Kerbéa</a><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-red.png" alt=""/>Contact</div>

                <h1 class="black-title">Devenir franchisé</h1>
                <div class="both"></div>
                
                <?php if(!empty($msg_error)) echo '<div style="text-align:center; font-weight: bold; padding:20px; color:#1A817B;" class="msg_error">'.$msg_error.'</div>';?>
                <?php include(BASE_URL.WORK_DIR.'/includes/dynapage_system.inc.php'); ?>
                
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="page_contact" id="contact_form" style="text-align:center; padding: 30px;">
                    <div class="form_item demi"><input type="text" name="prenom" placeholder="Prénom *" title="Prénom"/></div>
                    <div class="form_item demi last"><input type="text" name="nom" placeholder="Nom *" title="Nom"/></div>
                    <div class="form_item demi"><input type="text" name="ville" placeholder="Ville" title="Ville"/></div>
                    <div class="form_item demi last"><input type="text" name="postal" placeholder="Code Postal" title="Code Postal"/></div>
                    <div class="form_item demi"><input type="text" name="telephone" placeholder="Téléphone *" title="Téléphone"/></div>
                    <div class="form_item demi last"><input type="text" name="email" placeholder="E-mail *" title="E-mail"/></div>
                    <div class="form_item">
                        <textarea name="message" id="" cols="30" rows="10" placeholder="Message *" ><?php echo $list['message']; ?></textarea>
                    </div>
                    <div class="align_r"><em>* champs obligatoires</em></div>
                    <div class="form_item submit"><input type="submit" value="Envoyer" /></div>
                    <div class="both"></div>
                </form>
            </div>
        </div>
    </div>
    <?php include(BASE_URL.WORK_DIR.'/includes/footer.inc.php'); ?>
    </body>
</html>