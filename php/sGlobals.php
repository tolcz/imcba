<?php
	session_start();
	//header('P3P:CP="CAO PSA IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');
	header('P3P:CP="CAO PSA OUR"');
	
	
	//db connection stuff
	$connect1 = "localhost";
	$connect2 = "username";
	$connect3 = "password";
	$db_name =  "database";
	
	$db = mysql_connect($connect1,$connect2,$connect3);
	mysql_select_db($db_name,$db);
	
?>
