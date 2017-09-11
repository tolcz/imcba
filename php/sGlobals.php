<?php
	session_start();
	//header('P3P:CP="CAO PSA IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
	header('P3P:CP="CAO PSA OUR"');
	
	
	//db connection stuff
	$connect1 = "localhost";
	$connect2 = "mt";
	$connect3 = "mt01";
	$db_name = "mindtime";
	
	$db = mysql_connect($connect1,$connect2,$connect3);
	mysql_select_db($db_name,$db);
	
	//constants
	define('CUR_URL', 'http://localhost/mtapp/');
	define('APP_URL', 'http://localhost/mtapp/');

	define('SHOW_ADDS', false);
	define('APP_ID','183468368331191');
	define('APP_SECRET','1d73409ffa6bb02228c81ec3e8b2b536');
	define('PREFIX_LENGTH',1);
	

?>
