<?php

/*
 * This array contains data about the current agence (if agence selected)
 */

session_start();

if(!empty($_GET['alias'])){
    $select_infos_agence = $PDO -> prepare('
      SELECT id_agence, nom_agence, adresse_agence, codePostal_agence, telephone_agence, ville_agence, description_agence, gpsLat_agence, gpsLng_agence,facebook_agence, analytics_agence
      FROM agence
      WHERE alias_agence = :alias
    ');
    $select_infos_agence -> execute(array('alias' => $_GET['alias']));

    if( !$select_infos_agence -> rowCount()){
        header('location:'.RESSOURCE_URL);
        exit;
    }
    $select_infos_agence = $select_infos_agence -> fetch();
    $_SESSION['agence'] = array();
    $_SESSION['agence']['id'] = $select_infos_agence['id_agence'];
    $_SESSION['agence']['ga'] = $select_infos_agence['analytics_agence'];
    $_SESSION['agence']['alias'] = $_GET['alias'];
    $_SESSION['agence']['session'] = true;
}else{
    $_SESSION['agence']['alias'] = '';
    $_SESSION['agence']['session'] = false;
}

$scanDirDyna = array_diff(scandir(BASE_URL.WORK_DIR.'/images/back_dyna/'),array('.','..'));
$randomBack = array_rand($scanDirDyna);
$imgBackDyna = $scanDirDyna[$randomBack];

$environnement_agence['background_image'] = RESSOURCE_URL.'/images/back_dyna/'.$imgBackDyna;