<?php
	include('../../base_url.php');
	include(BASE_URL.'/conf/conf.php');
	include(BASE_URL.'/conf/connexion.php');
	include(BASE_URL.'/conf/fonctions.php');
	include(BASE_URL.WORK_DIR.'/phpmailer/class.phpmailer.php');
	
	session_start();
	
	/**
	* variable POST attendue : nom, prenom, telephone, email, id
	* 100 : absence de variable $_POST
	*/
	
	$array_retour = array();
	
	try{
		if(empty($_POST['filtre'])){
			throw new Exception('Les données envoyées sont erronées.',100);
		}
		
		$_SESSION['front']['offre']['filtre'] = $_POST['filtre'];
		
		$array_retour['error'] = 0;
		$array_retour['message'] = 'Filtres enregistrés.';
	}
	catch(Exception $e){
		$array_retour['error'] = $e -> getCode();
		$array_retour['message'] = $e -> getMessage();
		
		if(empty($array_retour['message'])){
			$array_retour['message'] = 'Une erreur est survenue.(#'.$e -> getCode().').Veuillez rééssayer ultérieurement.';
		}
		
		$file = fopen(BASE_URL."/conf/logs/error_save_session.txt","a");
		fwrite($file,"[".date("d/m/y G:i:s",time())."] erreur : ".$e -> getMessage()."\r\n");
		fclose($file);
	}
	
	echo json_encode($array_retour);