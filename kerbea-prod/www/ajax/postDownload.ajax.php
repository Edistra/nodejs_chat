<?php
	include('../../base_url.php');
	include(BASE_URL.'/conf/conf.php');
	include(BASE_URL.'/conf/connexion.php');
	include(BASE_URL.'/conf/fonctions.php');
	include(BASE_URL.WORK_DIR.'/phpmailer/class.phpmailer.php');
	
	/**
	* variable POST attendue : nom, prenom, telephone, email, id
	* 100 : absence d'une ou plusieurs variable
	* 200 : modèle introuvable
	* 300 : fiche introuvable
	*/
	
	$array_retour = array();
	
	try{
		if(empty($_POST['prenom']) || empty($_POST['nom']) || empty($_POST['telephone']) || empty($_POST['email']) || empty($_POST['agence']) || empty($_POST['id']) ){
			throw new Exception('Il manque des informations, veuillez remplir entièrement le formulaire.',100);
		}
		
		// Selection des informations de l'agence selectionnée
		$select_modele_maison = $PDO -> prepare('SELECT * FROM modele_maison WHERE id_modele_maison = :id_modele_maison');
		$select_modele_maison -> execute(array('id_modele_maison' => $_POST['id']));
		$count_modele_maison = $select_modele_maison -> rowCount();
		
		if(empty($count_modele_maison)){
			throw new Exception('Le modèle maison sélectionné est introuvable. Veuillez réessayer ultérieurement.',200);
		}
		
		$line_modele = $select_modele_maison -> fetch();
		
		if(empty($line_modele['fiche_modele_maison'])){
			throw new Exception('Désolé, la fiche de ce modèle est introuvable. Veuillez réessayer ultérieurement',300);
		}
		
		// Selection des informations du modele selectionnée
		$select_agence = $PDO -> prepare('SELECT * FROM agence WHERE id_agence = :id_agence');
		$select_agence -> execute(array('id_agence' => $_POST['agence']));
		$count_agence = $select_agence -> rowCount();
		
		if(empty($count_agence) || empty($count_modele_maison)){
			throw new Exception('L\'agence sélectionnée est introuvable. Veuillez réessayer ultérieurement.',200);
		}
		
		$line_agence = $select_agence -> fetch();
		
		// Enregistrement de la demande de contact dans la bdd
		$insert_demande_download = $PDO -> prepare('INSERT INTO archive_download
		(
			id_agence,
			id_modele_maison,
			prenom_archdownload,
			nom_archdownload,
			telephone_archdownload,
			email_archdownload,
			ip_archdownload
		)
		VALUES (
			:id_agence,
			:id_modele_maison,
			:prenom_archdownload,
			:nom_archdownload,
			:telephone_archdownload,
			:email_archdownload,
			:ip_archdownload
		)');
		
		$insert_demande_download -> execute(array(
			'id_agence' => $line_agence['id_agence'],
			'id_modele_maison' => $line_modele['id_modele_maison'],
			'prenom_archdownload' => $_POST['prenom'],
			'nom_archdownload' => $_POST['nom'],
			'telephone_archdownload' => $_POST['telephone'],
			'email_archdownload' => $_POST['email'],
			'ip_archdownload' => $_SERVER['REMOTE_ADDR']
		));
		
		$message_mail = '
			<table width="600" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="top">
						<h1 style="font-family:Arial;font-size:16px;color:#333;">Vous avez demandé à récupérer la fiche d\'un modèle maison sur le site Maisons Kerbea</h1>
						<h2 style="font-family:Arial;font-size:12px;color:#696a6a;">Modèle de maison sélectionné : '.$line_modele['nom_modele_maison'].'.</h2>
						<h2 style="font-family:Arial;font-size:12px;color:#696a6a;">Fiche</h2>
						<p style="font-family:Arial;font-size:12px;color:#000;"><a href="'.RESSOURCE_URL.'/medias/modele_maison/files/fiche/'.$line_modele['fiche_modele_maison'].'">Cliquez ici pour accéder à la fiche</a></p>
						
						<p style="font-family:Arial;font-size:10px;color:#000;">Ceci est un email automatique, aucune réponse ne sera traitée.</p>
						<p>&nbsp;</p>
					</td>
				</tr>
		</table>';
		
		$email_dest = $_POST['email'];
		$mail_obj = new PHPmailer();
		$mail_obj->IsMail();
		$mail_obj->IsHTML(true);
		$mail_obj->FromName = 'Maisons Kerbea';
		$mail_obj->From = 'no-reply@maisons-kerbea.fr';
		$mail_obj->AddAddress($email_dest);
		$mail_obj->Subject='Maisons Kerbea - Fiche modèle maisons '.$line_modele['nom_modele_maison'];
		$mail_obj->Body=$message_mail;
		
		if(!$mail_obj->Send()){
			throw new Exception('Le message n\'a pas pu être transmis, merci de réessayer ultérieurement.',300);
		}
		unset($mail_obj);
		
		$array_retour['error'] = 0;
		$array_retour['message'] = 'Votre message a bien été envoyé.';
		
	}
	catch(Exception $e){
		$array_retour['error'] = $e -> getCode();
		$array_retour['message'] = $e -> getMessage();
		
		if(empty($array_retour['message'])){
			$array_retour['message'] = 'Une erreur est survenue.(#'.$e -> getCode().').Veuillez rééssayer ultérieurement.';
		}
		
		$file = fopen(BASE_URL."/conf/logs/error_email_download.txt","a");
		fwrite($file,"[".date("d/m/y G:i:s",time())."] erreur : ".$e -> getMessage()."\r\n");
		fclose($file);
	}
	
	echo json_encode($array_retour);
