<?php
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
		if(empty($_POST['prenom']) || empty($_POST['nom']) || empty($_POST['telephone']) || empty($_POST['email']) || empty($_POST['offre'])){
			throw new Exception('Il manque des informations, veuillez remplir entièrement le formulaire.',100);
		}
		
		// Selection des informations de l'agence selectionnée
		$select_offre = $PDO -> prepare('SELECT * FROM offre WHERE id_offre = :id_offre');
		$select_offre -> execute(array('id_offre' => $_POST['offre']));
		$count_offre = $select_offre -> rowCount();
		
		if(empty($count_offre)){
			throw new Exception('Informations de l\'offre introuvable. Veuillez réessayer ultérieurement.',200);
		}
		
		$line_offre = $select_offre -> fetch();
		
		// Selection des informations du modele selectionnée
		$select_agence = $PDO -> prepare('SELECT * FROM agence WHERE id_agence = :id_agence');
		$select_agence -> execute(array('id_agence' => $line_offre['id_agence']));
		$count_agence = $select_agence -> rowCount();
		
		if(empty($count_agence) || empty($count_offre)){
			throw new Exception('L\'agence sélectionnée est introuvable. Veuillez réessayer ultérieurement.',300);
		}
		
		$line_agence = $select_agence -> fetch();
		
		// Enregistrement de la demande de contact dans la bdd
		$insert_demande_contact = $PDO -> prepare('INSERT INTO archive_contact_offre
		(
			id_agence,
			id_offre,
			prenom_archcontactoffre,
			nom_archcontactoffre,
			telephone_archcontactoffre,
			email_archcontactoffre,
			ip_archcontactoffre
		)
		VALUES (
			:id_agence,
			:id_offre,
			:prenom_archcontactoffre,
			:nom_archcontactoffre,
			:telephone_archcontactoffre,
			:email_archcontactoffre,
			:ip_archcontactoffre
		)');
		
		$insert_demande_contact -> execute(array(
			'id_agence' => $line_agence['id_agence'],
			'id_offre' => $line_offre['id_offre'],
			'prenom_archcontactoffre' => $_POST['prenom'],
			'nom_archcontactoffre' => $_POST['nom'],
			'telephone_archcontactoffre' => $_POST['telephone'],
			'email_archcontactoffre' => $_POST['email'],
			'ip_archcontactoffre' => $_SERVER['REMOTE_ADDR']
		));
		
		$message_mail = '
			<table width="600" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="top">
						<h1 style="font-family:Arial;font-size:16px;color:#333;">[Offre] - Demande de contact sur le site Maisons Kerbea</h1>
						<h2 style="font-family:Arial;font-size:12px;color:#696a6a;">Agence liée à l\'offre : '.$line_agence['nom_agence'].'.</h2>
						
						<h2 style="font-family:Arial;font-size:12px;color:#696a6a;">Offre sélectionnée</h2>
						<p style="font-family:Arial;font-size:12px;color:#000;"><strong>Id de l\'offre</strong> : '.$line_offre['id_offre'].'</p>
						<p style="font-family:Arial;font-size:12px;color:#000;"><strong>Ville</strong> : '.$line_offre['ville_offre'].'</p>
						<p style="font-family:Arial;font-size:12px;color:#000;"><strong>Type</strong> : '.$line_offre['type_offre'].'</p>
						<p style="font-family:Arial;font-size:12px;color:#000;"><strong>Prix</strong> : '.$line_offre['prix_offre'].' €</p>
						<p style="font-family:Arial;font-size:12px;color:#000;"><strong>Mensualité</strong> : '.$line_offre['prix_mensuel_offre'].' €</p>
						<p style="font-family:Arial;font-size:12px;color:#000;"><strong>Surface terrain</strong> : '.$line_offre['surfaceTerrain_offre'].' m²</p>
						
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
		
		$email_dest = !empty($line_agence['email_agence']) ? $line_agence['email_agence'] : EMAIL_ADMIN;
		$mail_obj = new PHPmailer();
		$mail_obj->IsMail();
		$mail_obj->IsHTML(true);
		$mail_obj->FromName = $_POST['prenom'].' '.$_POST['nom'].' - Maisons Kerbea contact';
		$mail_obj->From = 'no-reply@maisons-kerbea.fr';
		$mail_obj->AddAddress($email_dest);
		$mail_obj->Subject='[Offre] - Maisons Kerbea - contact d\'un internaute.';
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