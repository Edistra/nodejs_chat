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



	$id_menu_sel = $id_page_sel = '13';





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

	$seo['title'] = 'Contact | maisons-kerbea';

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

    'agence'=>array('',1),

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



            if(!empty($_GET['alias'])){

                $list['agence'] = $_SESSION['agence']['id'];

            }



            if($list['agence'] == 'siege'){

                $email_dest = 'contact@kerbea.fr';

            }

            else{

                $select_agence_select = $PDO -> prepare('SELECT id_agence, email_agence, nom_agence FROM agence where id_agence = :id_agence');

                $select_agence_select -> execute(array(

                    'id_agence' => $list['agence']

                ));

                $count_agence = $select_agence_select -> rowCount();



                if(empty($count_agence)){

                    throw new Exception('L\'agence sélectionnée est invalide.');

                }



                $line_agence = $select_agence_select -> fetch();

                $email_dest = !empty($line_agence['email_agence']) ? $line_agence['email_agence'] : EMAIL_ADMIN;

            }



            $message_mail = '

					<table width="600" border="0" cellpadding="0" cellspacing="0">

						<tr>

							<td valign="top">

								<h1 style="font-family:Arial;font-size:16px;color:#333;">Contact depuis le site Maisons Kerbea</h1>

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

            $mail_obj->AddAddress($email_dest);

            $mail_obj->Subject='Contact depuis Maisons Kerbea';

            $mail_obj->Body=$message_mail;



            if(!$mail_obj->Send()){

                unset($mail_obj);

            }



            // archivage de la demande

            $insert_demande_contact = $PDO -> prepare('INSERT INTO archive_contact_std

				(

					id_agence,

					prenom_archcontactstd,

					nom_archcontactstd,

					telephone_archcontactstd,

					ville_archcontactstd,

					postal_archcontactstd,

					email_archcontactstd,

					message_archcontactstd,

					ip_archcontactstd

				)

				VALUES (

					:id_agence,

					:prenom_archcontactstd,

					:nom_archcontactstd,

					:telephone_archcontactstd,

					:ville_archcontactstd,

					:postal_archcontactstd,

					:email_archcontactstd,

					:message_archcontactstd,

					:ip_archcontactstd

				)');



            $insert_demande_contact -> execute(array(

                'id_agence' => $list['agence'],

                'prenom_archcontactstd' => $list['prenom'],

                'nom_archcontactstd' => $list['nom'],

                'telephone_archcontactstd' => $list['telephone'],

                'ville_archcontactstd' => $list['ville'],

                'postal_archcontactstd' => $list['postal'],

                'email_archcontactstd' => $list['email'],

                'message_archcontactstd' => $list['message'],

                'ip_archcontactstd' => $_SERVER['REMOTE_ADDR']

            ));



            header('location:'.RESSOURCE_URL.'/contact.php?sendok');

            exit;

        }

        catch(Exception $e){

            $msg_error = 'Une erreur est survenue lors de l\'envoi de votre message.<br />Merci de rééssayer ultérieurement.<br />';



            $file = fopen(BASE_URL."/conf/logs/error_email_contact.txt","a");

            fwrite($file,"[".date("d/m/y G:i:s",time())."] erreur : ".$e -> getMessage()." - URI : ".$_SERVER['REQUEST_URI']."\r\n");

            fclose($file);

        }

    }

}

elseif(isset($_GET['sendok'])){

    $msg_error = 'Nous vous remercions pour l’intérêt que vous portez à Maisons Kerbea. Nos conseillers reviendront vers vous dans les plus brefs délais.<br />';

}





/* ############################################### REQUETES DIVERSES ############################################### */



$select_all_agences = $PDO -> query('SELECT * FROM agence WHERE actif_agence = 1 ORDER BY nom_agence ASC');

$count_all_agence = $select_all_agences -> rowCount();

$select_all_agences = $select_all_agences -> fetchAll();



if(!empty($_SESSION['agence']['alias'])){





$json_offre = json_encode(array(array(

    'name' => 'Maison Kerbea '.$_SESSION['agence']['alias'],

    'gps_lat' => $select_infos_agence['gpsLat_agence'],

    'gps_lng' => $select_infos_agence['gpsLng_agence'],

    'icon' => array(

        'size' => array(38, 49),

        'url' => RESSOURCE_URL.'/images/pictos/marker-kerbea.png'

    )

)));

}



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



        <?php if(!empty($_SESSION['agence']['alias'])): ?>

        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.13&libraries=geometry,places&sensor=false"></script>

        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/js-marker-clusterer/1.0.0/markerclusterer_compiled.js"></script>



        <script>

            var spots = <?php echo $json_offre; ?>;

        </script>

        <script src="<?php echo RESSOURCE_URL; ?>/js/map.js"></script>

        <?php endif; ?>

	</head>

    <body>

    <?php include(BASE_URL.WORK_DIR.'/includes/header.inc.php'); ?>

    <div id="container-content">



        <?php if(!empty($environnement_agence['background_image'])):?>

            <div class="img-back-agence no-mobile"><img src="<?php echo $environnement_agence['background_image']; ?>" alt=""/></div>

        <?php endif; ?>



        <div class="page-contact content-hover">

            <div class="site-width">



                <div class="breadcrumbs"><a href="<?php echo RESSOURCE_URL; if(!empty($_SESSION['agence']['alias'])){ echo '/agence-'.$_GET['alias'];} ?>">Maisons Kerbéa<?php if(!empty($_SESSION['agence']['alias'])){ echo ' '.$_SESSION['agence']['alias'];} ?></a><img src="<?php echo RESSOURCE_URL; ?>/images/pictos/fl-r-red.png" alt=""/>Contact</div>



                <h1 class="black-title">Contact</h1>

                <div class="both"></div>



                <?php if(!empty($_SESSION['agence']['alias'])): ?>

                    <div class="container-info-agence">

                        <div class="info-agence align_c">

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



                        <div class="info-agence"><div id="gmap" style="width:100%;height:200px;"></div></div>

                        <div class="both"></div>

                    </div>



                <?php endif; ?>



                <?php if(!empty($msg_error)) echo '<div style="text-align:center; font-weight: bold; padding:20px; color:#1A817B;" class="msg_error">'.$msg_error.'</div>';?>

<!--                <div class="left">--><?php //include(BASE_URL.WORK_DIR.'/includes/dynapage_system.inc.php'); ?><!--</div>-->

<!--                <div class="col-right">--><?php //include(BASE_URL.WORK_DIR.'/includes/dynapage_include_system.inc.php'); ?><!--</div>-->

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" class="page_contact" id="contact_form" style="text-align:center; padding: 30px;">

                    <div class="form_item demi"><input type="text" name="prenom" placeholder="Prénom *" title="Prénom"/></div>

                    <div class="form_item demi last"><input type="text" name="nom" placeholder="Nom *" title="Nom"/></div>

                    <div class="form_item demi"><input type="text" name="ville" placeholder="Ville" title="Ville"/></div>

                    <div class="form_item demi last"><input type="text" name="postal" placeholder="Code Postal" title="Code Postal"/></div>

                    <div class="form_item demi"><input type="text" name="telephone" placeholder="Téléphone *" title="Téléphone"/></div>

                    <div class="form_item demi last"><input type="text" name="email" placeholder="E-mail *" title="E-mail"/></div>

                    <div class="both"></div>

                    <?php

                    if(!isset($_GET['alias'])):

                    ?>

                        <div class="form_item" id="select">



                            <select name="agence" id="agence">

                                <option value="">Choisir une agence / contact *</option>

                                <?php

                                foreach($select_all_agences as $line_agence){

                                    echo '<option value="'.$line_agence['id_agence'].'">'.$line_agence['nom_agence'].'</option>';

                                }

                                ?>

                                <option value="siege">SIÈGE</option>

                            </select>

                        </div>

                    <?php

                    else :

                        echo '<input type="hidden" name="agence" value="'.$_SESSION['agence']['id'].'" />';

                    endif;

                    ?>

                    <div class="form_item">

                        <textarea name="message" id="" cols="30" rows="10" placeholder="Message *" ><?php echo $list['message']; ?></textarea>

                    </div>

                    <div class="align_r"><em>* champs obligatoires</em></div>

                    <div class="form_item submit"><input type="submit" value="Envoyer" /></div>
                
                    <div class="both"></div>
                   
                </form>

                <div class="align_l">
                        <em>Les informations recueillies sur ce formulaire sont enregistrées dans un fichier informatisé par KERBEA FRANCE pour la gestion de notre clientèle.<br />
                        Elles sont conservées pendant 1 an et sont destinées Service marketing, communication et développement de Kerbéa France.<br />
                        Conformément à la <a href="https://www.cnil.fr/fr/loi-78-17-du-6-janvier-1978-modifiee" target="_blank">loi « informatique et libertés »</a>, vous pouvez exercer votre droit d'accès aux données vous concernant et les faire rectifier en contactant : Service marketing Kerbéa France: contact@kerbea.fr
                        </em>
                </div>
            </div>

        </div>

    </div>

    <?php include(BASE_URL.WORK_DIR.'/includes/footer.inc.php'); ?>

    </body>

</html>
