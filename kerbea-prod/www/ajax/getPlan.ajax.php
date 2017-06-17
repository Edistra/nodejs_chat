<?php
	include('../../base_url.php');
	include(BASE_URL.'/conf/conf.php');
	include(BASE_URL.'/conf/connexion.php');
	include(BASE_URL.'/conf/fonctions.php');
	include(BASE_URL.WORK_DIR.'/phpmailer/class.phpmailer.php');
	
	/**
	* variable POST attendue : nom, prenom, telephone, email, agence
	* 100 : absence d'une ou plusieurs variable
	*/
	
	$array_retour = array();
	
	try{
		if(empty($_GET['variante'])){
			throw new Exception('Le plan demandé est introuvable',100);
		}
		
		// Selection des informations du plan / variante
		$select_variante = $PDO -> prepare('SELECT * FROM variante_modele WHERE id_variante_modele = :id_variante');
		$select_variante -> execute(array('id_variante' => $_GET['variante']));
		$count_variante = $select_variante -> rowCount();
		
		if(empty($count_variante)){
			throw new Exception('Le plan demandé est introuvable',200);
		}
		
		$array_retour['variante'] = $select_variante -> fetch();
		
		$array_retour['error'] = 0;
		$array_retour['message'] = '';
		
	}
	catch(Exception $e){
		$array_retour['error'] = $e -> getCode();
		$array_retour['message'] = $e -> getMessage();
		$array_retour['variante'] = '';
		
		if(empty($array_retour['message'])){
			$array_retour['message'] = 'Une erreur est survenue.(#'.$e -> getCode().').Veuillez rééssayer ultérieurement.';
		}
	}
	
	echo json_encode($array_retour);
?>