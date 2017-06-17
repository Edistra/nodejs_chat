<?php
	ini_set('session.use_cookies', '1');
	ini_set('session.use_only_cookies', '1');  // PHP >= 4.3
	ini_set('session.use_trans_sid', '0');
	
	$PARAM_hote="localhost";
	$PARAM_user="root";
	$PARAM_pass="root";
	$PARAM_db="kerbea";
	
	define('NOM_SITE','Maisons Kerbea');
	define('DEFAULT_LANG', "fr");
	define('DEBUG_TRANSLATE', false);
