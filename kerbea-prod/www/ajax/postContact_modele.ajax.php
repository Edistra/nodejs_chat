<?php
	error_reporting(0);
	include('../../base_url.php');
	include(BASE_URL.'/conf/conf.php');
	include(BASE_URL.'/conf/connexion.php');
	include(BASE_URL.'/conf/fonctions.php');
	include(BASE_URL.WORK_DIR.'/phpmailer/class.phpmailer.php');
	
	/**
	* variable POST attendue : nom, prenom, telephone, email, agence
	* 100 : absence d'une ou plusieurs variable
	* 200 : offre introuvable
	* 300 : agence introuvable
	*/

	$array_retour = array();
	
	try{
		if(empty($_POST['prenom']) || empty($_POST['nom']) || empty($_POST['telephone']) || empty($_POST['email']) || empty($_POST['modele']) || empty($_POST['agence'])){
            throw new Exception('Il manque des informations, veuillez remplir entièrement le formulaire.',100);
		}
		
		// Selection des informations du modele selectionnée
		$select_modele = $PDO -> prepare('SELECT * FROM modele_maison WHERE id_modele_maison = :id_modele');
		$select_modele -> execute(array('id_modele' => $_POST['modele']));
		$count_modele = $select_modele -> rowCount();

		if(empty($count_modele)){
			throw new Exception('Informations du modèle de maison introuvable. Veuillez réessayer ultérieurement.',200);
		}
		
		$line_modele = $select_modele -> fetch();

        // Selection des informations de l'agence du modele selectionnée
        $select_agence = $PDO -> prepare('SELECT * FROM agence WHERE id_agence = :alias_agence');
        $select_agence -> execute(array('alias_agence' => $_POST['agence']));
        $count_agence = $select_agence -> rowCount();
        if(empty($count_agence)){
            throw new Exception('Informations de l\'agence introuvable. Veuillez réessayer ultérieurement.',300);
        }

        $line_agence = $select_agence -> fetch();
		
		// Enregistrement de la demande de contact dans la bdd
		$insert_demande_contact = $PDO -> prepare('INSERT INTO archive_contact_fiche
		(
			id_agence,
			id_modele_maison,
			prenom_archcontactfiche,
			nom_archcontactfiche,
			telephone_archcontactfiche,
			email_archcontactfiche,
			ip_archcontactfiche
		)
		VALUES (
			:id_agence,
			:id_modele_maison,
			:prenom_archcontactfiche,
			:nom_archcontactfiche,
			:telephone_archcontactfiche,
			:email_archcontactfiche,
			:ip_archcontactfiche
		)');
		
		$insert_demande_contact -> execute(array(
			'id_agence' => $line_agence['id_agence'],
			'id_modele_maison' => $line_modele['id_modele_maison'],
			'prenom_archcontactfiche' => $_POST['prenom'],
			'nom_archcontactfiche' => $_POST['nom'],
			'telephone_archcontactfiche' => $_POST['telephone'],
			'email_archcontactfiche' => $_POST['email'],
			'ip_archcontactfiche' => $_SERVER['REMOTE_ADDR']
		));
		
		$message_mail = '
			<table width="600" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="top">
						<h1 style="font-family:Arial;font-size:16px;color:#333;">[Modele] - Demande de contact sur le site Maisons Kerbea</h1>
						<h2 style="font-family:Arial;font-size:12px;color:#696a6a;">Agence liée : '.$line_agence['nom_agence'].'.</h2>

						<h2 style="font-family:Arial;font-size:12px;color:#696a6a;">Modele sélectionnée</h2>
						<p style="font-family:Arial;font-size:12px;color:#000;"><strong>Id du modele</strong> : '.$_POST['modele'].'</p>
						<p style="font-family:Arial;font-size:12px;color:#000;"><strong>Nom du modele</strong> : '.$line_modele['nom_modele_maison'].'</p>

						<h2 style="font-family:Arial;font-size:12px;color:#696a6a;">Informations de l\'internaute</h2>
						<p style="font-family:Arial;font-size:12px;color:#000;"><strong>Prénom</strong> : '.$_POST['prenom'].'</p>
						<p style="font-family:Arial;font-size:12px;color:#000;"><strong>Nom</strong> : '.$_POST['nom'].'</p>
						<p style="font-family:Arial;font-size:12px;color:#000;"><strong>Email</strong> : '.$_POST['email'].'</p>
						<p style="font-family:Arial;font-size:12px;color:#000;"><strong>Téléphone</strong> : '.$_POST['telephone'].'</p>

						<p>&nbsp;</p>
						<p style="font-family:Arial;font-size:11px;color:#000;">Cet email est envoyé par le site Maisons Kerbea, aucune réponse directe ne sera traitée.Veuillez vous référer à l\'email du contact présent ci-dessus.</p>
					</td>
				</tr>
		</table>';


		$email_dest = EMAIL_ADMIN;
		$mail_obj = new PHPmailer();
		$mail_obj->IsMail();
		$mail_obj->IsHTML(true);
		$mail_obj->FromName = $_POST['prenom'].' '.$_POST['nom'].' - Maisons Kerbea contact';
		$mail_obj->From = 'no-reply@maisons-kerbea.fr';
		$mail_obj->AddAddress($email_dest);
		$mail_obj->Subject='[Modèle de maison] - Maisons Kerbea - contact d\'un internaute.';
		$mail_obj->Body=$message_mail;
		
		if(!$mail_obj->Send()){
			throw new Exception('Le message n\'a pas pu être transmis, merci de réessayer ultérieurement.',300);
		}
		unset($mail_obj);
		
		$array_retour['error'] = 0;
		$array_retour['message'] = 'Votre demande a bien été envoyée.';
		
	}
	catch(Exception $e){
		$array_retour['error'] = $e -> getCode();
		$array_retour['message'] = $e -> getMessage();
		
		if(empty($array_retour['message'])){
			$array_retour['message'] = 'Une erreur est survenue.(#'.$e -> getCode().').Veuillez rééssayer ultérieurement.';
		}
		
		$file = fopen(BASE_URL."/conf/logs/error_email_contact_fiche.txt","a");
		fwrite($file,"[".date("d/m/y G:i:s",time())."] erreur : ".$e -> getMessage()."\r\n");
		fclose($file);
	}
	
	echo json_encode($array_retour);
?>