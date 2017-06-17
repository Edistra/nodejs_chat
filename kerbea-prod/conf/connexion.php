<?php
	try{
		$PDO = new pdo('mysql:dbname='.$PARAM_db.';host='.$PARAM_hote , $PARAM_user ,$PARAM_pass, array(
			PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
		);
	}
	catch(PDOException $e){
		echo 'Impossible de se connecter a la base. Erreur #'.$e->getCode();
		exit();
	}

	include(BASE_URL.WORK_DIR.'/includes/Mobile_Detect.php');
	$device_detect = new Mobile_Detect();