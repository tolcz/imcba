<?php
 /* upload script for audio files
  © tolczak@gmail.com 2017
  © imcba.com 2017
 */
	session_start();   

	$target_dir = "uploads/";
	$tmp_file = $_FILES["audioFile"]["tmp_name"];
	$uploadOk = true;
	$imageFileType = pathinfo($_FILES["audioFile"]["name"], PATHINFO_EXTENSION);
	$target_file = $target_dir . session_id() . basename($tmp_file) . ".wav";
	
	try {
		if(isset($_POST["submit"])) {
			// Check mime type
			$mime_type = mime_content_type($tmp_file);
			$check = (substr($mime_type, 0, 5) === "audio");
			if($check !== false) {
				$msg =  "File is " . $mime_type;
				$uploadOk = true;
			} else {
				$msg =  "Only audio files accepted. File type is " . $mime_type;
				$uploadOk = false;
			}
		}
		
		// Check if file already exists
		if (file_exists($target_file)) {
			$msg =  "Sorry, file already exists.";
			$uploadOk = false;
		}
		
		// Check file size
		if ($_FILES["audioFile"]["size"] > 30*1024*1024) {
			$msg =  "Sorry, your file is too large.";
			$uploadOk = false;
		}

		// if everything ok so far, try to upload file
		if ($uploadOk and move_uploaded_file($tmp_file, $target_file)) {
			$msg =  "The file ". basename($_FILES["audioFile"]["name"]). " has been uploaded.";
		} else {
			$msg =  "Upload error";
			$uploadOk = false;
		}
	} catch(Exception $e) {
		$msg = 'Error: ' . $e->getMessage();
		$uploadOk = false;
	}
	
	$response = new StdClass;
	$response->message  = $msg;
	$response->success  = $uploadOk;
	$response->code     = 200;
	//$response->duration = 0.1;
	$response->result   = $uploadOk;
	$response->filesize = $_FILES["audioFile"]["size"];
	if ($uploadOk) {
		$response->url  = "http://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['REQUEST_URI']) . "/" . $target_file;
	}

	$myJSON = json_encode($response);
	header('Content-type: application/json');
	echo $myJSON;
?>
