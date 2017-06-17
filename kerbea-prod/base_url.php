<?php

// L'url racine du site
define('WORK_DIR','/www');
define('NAME_ADMIN_DIR','admker');

define('BASE_URL', dirname(__FILE__));
define('ADMIN_URL',BASE_URL.WORK_DIR.'/'.NAME_ADMIN_DIR);
define('URL_NATIONAL','maisons-kerbea.fr');
//define('URL_NATIONAL','maisons-kerbea.loc');

/* analyse de l'url pour détecter un eventuel ss domaine */

//if($_SERVER['HTTP_HOST'] != URL_NATIONAL && $_SERVER['HTTP_HOST'] != 'www.'.URL_NATIONAL){
//if($_SERVER['HTTP_HOST'] != URL_NATIONAL && $_SERVER['HTTP_HOST'] != 'dev.'.URL_NATIONAL){
//    $temp_alias = $_SERVER['HTTP_HOST'];
//    $temp_ressource_url = 'http://' . $_SERVER['HTTP_HOST'];
//    $temp_ressource_url_admin = 'http://' . $_SERVER['HTTP_HOST'] . '/' . NAME_ADMIN_DIR;
//}
//else{
//    $temp_alias = '';
//    $temp_ressource_url = 'http://www.' . URL_NATIONAL;
//    $temp_ressource_url = 'http://dev.' . URL_NATIONAL;
//    $temp_ressource_url_admin = 'http://www.' . URL_NATIONAL . '/' . NAME_ADMIN_DIR;
//    $temp_ressource_url_admin = 'http://dev.' . URL_NATIONAL . '/' . NAME_ADMIN_DIR;

//}

define('RESSOURCE_URL','https://www.maisons-kerbea.fr');
define('RESSOURCE_ADMIN_URL','https://www.maisons-kerbea.fr' . '/' . NAME_ADMIN_DIR);

//define('ALIAS_URL', $temp_alias);
//define('RESSOURCE_URL', $temp_ressource_url);
//define('RESSOURCE_ADMIN_URL', $temp_ressource_url_admin);