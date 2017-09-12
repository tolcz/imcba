<?php 
if ($_SERVER["REQUEST_METHOD"] == "POST") {

	//include config file
	include_once dirname(__FILE__)."/php/sGlobals.php";
	include_once dirname(__FILE__)."/php/fx.gps.php";
	include_once dirname(__FILE__)."/php/tempo.php";

	function test_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}

	// define variables and set to empty values
	$past = $present = $future = 0.0;

	$sql = "INSERT INTO imcba_survey01
	(past1,
	past2,
	past3,
	past4,
	pres1,
	pres2,
	pres3,
	pres4,
	future1,
	future2,
	future3,
	future4,
	rec1,
	rec2,
	rec3,
	rec4,
	age,
	sex,
	email,
	fname,
	mname,
	lname,
	phone,
	php_id) 
	VALUES("
	.$_POST["q3_past_1"].","
	.$_POST["q6_past_2"].","
	.$_POST["q10_past_3"].","
	.$_POST["q52_past_4"].","
	.$_POST["q4_present_1"].","
	.$_POST["q7_present_2"].","
	.$_POST["q11_present_3"].","
	.$_POST["q53_present_4"].","
	.$_POST["q5_future_1"].","
	.$_POST["q8_future_2"].","
	.$_POST["q12_future_3"].","
	.$_POST["q54_future_4"].",'"
	.$_POST["recording1"]."','"
	.$_POST["recording2"]."','"
	.$_POST["recording3"]."','"
	.$_POST["recording4"]."',"
	.$_POST["q44_age"].",'"
	.$_POST["q36_yourSex"]."','"
	.$_POST["q33_yourEmail33"]."','"
	.$_POST["q32_yourName[first]"]."','"
	.$_POST["q32_yourName[middle]"]."','"
	.$_POST["q32_yourName[last]"]."','"
	.$_POST["q34_phoneNumber[area]"].$_POST["q34_phoneNumber[phone]"]."','"
	.session_id()."')";

	if (!mysql_query($sql)) {
		echo "Error: " . mysql_error();
		echo $sql;
	} else {
	
	$past += test_input($_POST["q3_past_1"]);
	$past += test_input($_POST["q6_past_2"]);
	$past += test_input($_POST["q10_past_3"]);
	$past += test_input($_POST["q52_past_4"]);

	$present += test_input($_POST["q4_present_1"]);
	$present += test_input($_POST["q7_present_2"]);
	$present += test_input($_POST["q11_present_3"]);
	$present += test_input($_POST["q53_present_4"]);
	
	$future += test_input($_POST["q5_future_1"]);
	$future += test_input($_POST["q8_future_2"]);
	$future += test_input($_POST["q12_future_3"]);
	$future += test_input($_POST["q54_future_4"]);

	$past    = ($past - 4) * 25;
	$present = ($present - 4) * 25;
	$future  = ($future - 4) * 25;

	$gpsfx = new GPSFX(400);
	$tempo = $gpsfx->calcGPS($future, $past, $present);
	$app = new Tempo($tempo);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="HandheldFriendly" content="true">
    <title>Thank You</title>
    <link href="ThankYou_pliki/css.css" rel="stylesheet" type="text/css">
    <script type="text/javascript" src="ThankYou_pliki/prototype.js"></script>
    <style type="text/css">
        @import url(https://fonts.googleapis.com/css?family=Muli:light,lightitalic,normal,italic,bold,bolditalic);
        * {
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            *behavior: url(js/boxsizing.htc);
        }

        html,
        body {
            width: 100%;
            margin: 0px;
            padding: 0px;
        }

        body {
            background: url('img/thankyou.jpg') rgb(195, 195, 195);
            background-repeat: no-repeat;
            background-attachment: scroll;
            background-position: center top;
            background-size: auto;
            background-attachment: fixed;
            background-size: cover;
            font-family: 'Muli', sans-serif;
            font-size: 16px;
            color: rgb(255, 255, 255);
            text-align: center;
        }

        .form-all {
            background: url('') rgba(0, 0, 0, 0.7);
            background-repeat: no-repeat;
            background-attachment: scroll;
            background-position: center top;
            background-size: auto;

            width: 100%;
            max-width: 550px;
            margin: 36px auto;
            padding: 19px 29px;
            -webkit-box-shadow: 0 4px 4px -1px rgba(0, 0, 0, 0.1);
            box-shadow: 0 4px 4px -1px rgba(0, 0, 0, 0.1);
        }

        #footer {
            text-align: left;
            margin: -35px auto 0;
            font-size: 14px;
            width: 550px;
        }

        #footer>div {
            box-shadow: 0 4px 4px -1px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            padding: 12px 15px;
            overflow: hidden;
        }

        #footer>div>div {
            padding: 10px 0 10px 5px
        }

        .thankYouPage-footerJFLink span {
            display: none
        }

        @media screen and (max-width: 550px),
        screen and (max-device-width: 768px) and (orientation: portrait),
        screen and (max-device-width: 415px) and (orientation: landscape) {
            body {
                background-color: rgba(0, 0, 0, 0.8);
            }
            .form-all {
                margin: 12px 3%;
                border: 0;
                -webkit-box-shadow: none;
                box-shadow: none;
                width: 94%;
                max-width: initial;
            }

            .thankYouPage-footerJFLink img {
                display: none
            }
            .thankYouPage-footerJFLink span {
                display: inline-block
            }

            #footer {
                width: 94%;
                margin-top: 0;
            }

            #footer>div>div {
                padding: 3px 0 0 5px;
                font-size: 12px;
            }

            #footer>div>div span {
                display: block
            }
            #footer>div>div span.footer-dash {
                display: none
            }
        }

        @media print {
            body {
                background: white;
                color: black;
            }

            .form-all {
                margin: 0 auto;
                max-width: 100%;
                box-shadow: none;
                background: white;
                float: none;
                width: 550px;
            }
            img {
                max-width: 100% !important;
                page-break-inside: avoid;
            }
        }

		#header {
		background-color: rgba(0, 0, 0, 0.0);
		color: white;
		position: fixed;
		top: 0;
		left: 0;
		width: 100%;
		height: 30px;
		padding: 0;
		margin: 0;
		z-index: 1000;
		}
		#header #header-content {
			margin: 5px;
		}
		
	  /*PREFERENCES STYLE*//*__INSPECT_SEPERATOR__*/
		/* Injected CSS Code */
		/* unvisited link */
		a:link {
			color: orange;
		}

		/* visited link */
		a:visited {
			color: cyan;
		}

		/* mouse over link */
		a:hover {
			color: magenta;
		}

		/* selected link */
		a:active {
			color: green;
		}
	</style>
</head>

<body class="thankyou">
	<div id="header">
		<div id="header-content">
			<a href="http://www.imcba.com">
				<center><img src="img/IMC-BA_lrg-logo.png" height="20" width="54" /></center>
			</a>
		</div>
	</div>
    <div id="stage" class="form-all">
        <div style="text-align: center;" id="ThankYouFrame">
			<p style="text-align: center;"><img src="img/check-icon.png" alt="" width="128" height="128"></p>
            <h1 style="text-align: center;">Thank You!</h1>
            <p style="text-align: center;">Your submission has been received.</p>
            <p>To see preliminary results <a href="#" onclick="document.getElementById('GPSframe1').style.display = 'block'; document.getElementById('ThankYouFrame').style.display = 'none';">click here</a>.
        </div>
        <div id="GPSframe1" style="text-align: justify; display: none; padding: 0px 10px 0px 10px;">
			 <table style="width:100%">
				 <tr>
					 <th><?php $gpsfx->renderIntensityBars();?></th>
					 <th><?php $gpsfx->renderBadge();?></th>
				 </tr>
			</table> 
			<?php 
				$result = $app->renderTempo('OVERVIEW');
				$result = str_replace("Would you like to know more?", "", $result);
				echo $result;
			?>
			<p>Would you like to <a href="#" onclick="document.getElementById('GPSframe1').style.display = 'none'; document.getElementById('GPSframe2').style.display = 'block';">know more</a>?</p>
	    </div>
        <div id="GPSframe2" style="text-align: justify; display: none; padding: 0px 10px 0px 10px;">
			<?php 
				$result = $app->renderTempo('VIEW OF THE WORLD');
				echo $result;
			?>
			<p>Would you like to <a href="#" onclick="document.getElementById('GPSframe2').style.display = 'none'; document.getElementById('GPSframe3').style.display = 'block';">know more</a>?</p>
	    </div>
        <div id="GPSframe3" style="text-align: justify; display: none; padding: 0px 10px 0px 10px;">
			<?php 
				$result = $app->renderTempo('VALUES');
				echo $result;
			?>
			<p>Would you like to <a href="#" onclick="document.getElementById('GPSframe3').style.display = 'none'; document.getElementById('GPSframe4').style.display = 'block';">know more</a>?</p>
	    </div>
        <div id="GPSframe4" style="text-align: justify; display: none; padding: 0px 10px 0px 10px;">
			<?php 
				$result = $app->renderTempo('DECISION MAKING');
				echo $result;
			?>
			<p>Would you like to <a href="#" onclick="document.getElementById('GPSframe4').style.display = 'none'; document.getElementById('GPSframe5').style.display = 'block';">know more</a>?</p>
	    </div>
        <div id="GPSframe5" style="text-align: justify; display: none; padding: 0px 10px 0px 10px;">
			<?php 
				$result = $app->renderTempo('COMMUNICATION');
				echo $result;
			?>
			<p>Would you like to <a href="#" onclick="document.getElementById('GPSframe5').style.display = 'none'; document.getElementById('GPSframe6').style.display = 'block';">know more</a>?</p>
	    </div>
        <div id="GPSframe6" style="text-align: justify; display: none; padding: 0px 10px 0px 10px;">
			<?php 
				$result = $app->renderTempo('LEADERSHIP');
				echo $result;
			?>
			<p>Would you like to <a href="#" onclick="document.getElementById('GPSframe6').style.display = 'none'; document.getElementById('GPSframe7').style.display = 'block';">know more</a>?</p>
	    </div>
        <div id="GPSframe7" style="text-align: justify; display: none; padding: 0px 10px 0px 10px;">
			<?php 
				$result = $app->renderTempo('RELATIONSHIPS');
				echo $result;
			?>
			<p>Would you like to <a href="#" onclick="document.getElementById('GPSframe7').style.display = 'none'; document.getElementById('GPSframe8').style.display = 'block';">know more</a>?</p>
	    </div>
        <div id="GPSframe8" style="text-align: justify; display: none; padding: 0px 10px 0px 10px;">
			<?php 
				$result = $app->renderTempo('RESISTANCES');
				echo $result;
			?>
			<p>Would you like to <a href="http://www.imcba.com">know more</a>?</p>
	    </div>
    </div>
    <div id="footer" class="form-footer"></div>

    <script type="text/javascript">
        if (window.parent !== window) {
            window.parent.postMessage('setHeight:' + $$('body')[0].getHeight(), '*');
        }
    </script>

</body>

</html>

<?php 
	}
	$_POST = array();
	session_destroy();
}
?>
