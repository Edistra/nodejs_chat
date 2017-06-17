<?php
	include('../../base_url.php');
	include(BASE_URL.'/conf/conf.php');
	include(BASE_URL.'/conf/connexion.php');
	include(BASE_URL.'/conf/fonctions.php');
	
	session_start();
	
	$array_retour = array();
	$array_retour['error'] = 0;
	$array_retour['data'] = !empty($_SESSION['front']['offre']['filtre']) ? $_SESSION['front']['offre']['filtre'] : null;
	
	echo json_encode($array_retour);
?>