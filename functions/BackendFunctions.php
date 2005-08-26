<?php
/**
 * VCD-db - a web based VCD/DVD Catalog system
 * Copyright (C) 2003-2004 Konni - konni.com
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * @author  H�kon Birgsson <konni@konni.com>
 * @package Functions
 * @version $Id$
 */
?>
<? 

/**
 * @return bool
 * @param $mail_to String
 * @param $subject String
 * @param $body String
 * @desc Send an email, returns true on success and false on failure
 */
function sendMail($mail_to, $subject='', $body='', $use_html=false) {
	
	global $ClassFactory;
	$SETTINGSClass = $ClassFactory->getInstance("vcd_settings");
	
	
	$smtp = $ClassFactory->getInstance("smtp_class");
	
	$content_type = "text/plain; charset=iso-8859-1";

	if ($use_html) {
		$content_type = "text/html";
	} 
	
	
	/*
	 * If you need to use the direct delivery mode and this is running under
	 * Windows or any other platform that does not have enabled the MX
	 * resolution function GetMXRR() , you need to include code that emulates
	 * that function so the class knows which SMTP server it should connect
	 * to deliver the message directly to the recipient SMTP server.
	 */
	if(!function_exists("GetMXRR"))
	{
		/*
		 * If possible specify in this array the address of at least on local
		 * DNS that may be queried from your network.
		 */
		$_NAMESERVERS=array();
		include("classes/external/mail/getmxrr.php");
		
		
	}
	/*
	 * If GetMXRR function is available but it is not functional, to use
	 * the direct delivery mode, you may use a replacement function.
	 */
	/*
	else 
	{
		$_NAMESERVERS=array();
		if(count($_NAMESERVERS)==0)
			Unset($_NAMESERVERS);
		include("rrcompat.php"); 
		$smtp->getmxrr="_getmxrr";
	}
	*/

	$smtp->host_name=getenv("HOSTNAME"); /* relay SMTP server address */
	$smtp->localhost="localhost"; /* this computer address */
	$smtp->host_name = $SETTINGSClass->getSettingsByKey('SMTP_SERVER');
	
	$from = $SETTINGSClass->getSettingsByKey('SMTP_FROM');
	$to = $mail_to;
	
	/* Set to 1 to deliver directly to the recepient SMTP server */
	$smtp->direct_delivery = 0;	 
	/* Set to the number of seconds wait for a successful connection to the SMTP server */
	$smtp->timeout = 10;
	
	$smtp->data_timeout=0;    		/* Set to the number seconds wait for sending or retrieving data from the SMTP server.
	                           	  	Set to 0 to use the same defined in the timeout variable */
	                           	
	/* Set to 1 to output the communication with the SMTP server */
	$smtp->debug = $SETTINGSClass->getSettingsByKey('SMTP_DEBUG');
	 /* Set to 1 to format the debug output as HTML */
	$smtp->html_debug = $SETTINGSClass->getSettingsByKey('SMTP_DEBUG');
	/* Set to the user name if the server requires authetication */  
	$smtp->user = $SETTINGSClass->getSettingsByKey('SMTP_USER');
	 /* Set to the authetication realm, usually the authentication user e-mail domain */
	$smtp->realm = $SETTINGSClass->getSettingsByKey('SMTP_REALM');
	/* Set to the authetication password */ 
	$smtp->password = $SETTINGSClass->getSettingsByKey('SMTP_PASS');
		
	
	if($smtp->SendMessage(
		$from,
		array(
			$to
		),
		array(
			"From: $from",
			"To: $to",
			"Subject: $subject ..",
			"Content-Type: $content_type",
			"Date: ".strftime("%a, %d %b %Y %H:%M:%S %Z")
		),
		"$body.\n"))
		return true;
	else
		return false;

}



function keyED($txt,$encrypt_key)		{
	$encrypt_key = md5($encrypt_key);
	$ctr=0;
	$tmp = "";
	for ($i=0;$i<strlen($txt);$i++){
			if ($ctr==strlen($encrypt_key)) $ctr=0;
			$tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1);
			$ctr++;
	}
	return $tmp;
}

function Encrypt($txt,$key) {
	srand((double)microtime()*1000000);
	$encrypt_key = md5(rand(0,32000));
	$ctr=0;
	$tmp = "";
	for ($i=0;$i<strlen($txt);$i++)
	{
	if ($ctr==strlen($encrypt_key)) $ctr=0;
	$tmp.= substr($encrypt_key,$ctr,1) .
	(substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
	$ctr++;
	}
	return base64_encode(keyED($tmp,$key));
}

function Decrypt($txt,$key)
{
	$txt = keyED(base64_decode($txt),$key);
	$tmp = "";
	for ($i=0;$i<strlen($txt);$i++){
		$md5 = substr($txt,$i,1);
		$i++;
		$tmp.= (substr($txt,$i,1) ^ $md5);
	}
	return $tmp;
}


function createReminderEmailBody($borrower_name, $arrLoanObj) {
	
	global $language;
	$msg = sprintf($language->show('MAIL_RETURNMOVIES1'), $borrower_name);
	foreach ($arrLoanObj as $loanObj) {
		$msg .= $loanObj->getCDTitle() . " - ".$language->show('LOAN_SINCE')." " . date("d/m/Y", $loanObj->getDateOut()) . "\n\n";
	}
	$msg .= sprintf($language->show('MAIL_RETURNMOVIES2'), $_SESSION['user']->getFullname());
	return $msg;
	
}

function createNotifyEmailBody(vcdObj $obj) {
	
	global $ClassFactory;
	global $language;
	$SETTINGSClass = $ClassFactory->getInstance("vcd_settings");
	$home = $SETTINGSClass->getSettingsByKey('SITE_HOME');
	
	$msg = '';
	$msg .= "<html><body>";
	$msg .= sprintf($language->show('MAIL_NOTIFY'), $home, $obj->getID());
	$msg .= "</body></html>";
	return $msg;
}


function generateExcel() {
	global $ClassFactory;
	$user_id = $_SESSION['user']->getUserID();
	$vcd = $ClassFactory->getInstance("vcd_movie");
	$arrMovies = $vcd->getAllVcdByUserId($user_id);
	$SETTINGSClass = $ClassFactory->getInstance('vcd_settings');
	
	//initiate a instance of "excelgen" class
	$excel = new ExcelGen("My_Movies");
	
	//initiate $row,$col variables
	$row=0;
	$col=0;
	
	//write text in cell(0,0)
	$excel->WriteText($row,$col,"My Movie List (".date("d.m.Y").") (Generated by VCD-db)");
	$row++;
	
	// Write the column headers
	$excel->WriteText($row, 0, "Title");
	$excel->WriteText($row, 1, "Genre");
	$excel->WriteText($row, 2, "Media type");
	$excel->WriteText($row, 3, "Year");
	$excel->WriteText($row, 4, "Media index");
	$row++;
	
	for ($row; $row < sizeof($arrMovies) + 2; $row++) {
		$col = 0;
		
		$movie = $arrMovies[$row-2];
		
		// Write The title
		$excel->WriteText($row, $col, $movie->getTitle());
		$col++;
		
		// Write The category
		if (!is_null($movie->getCategory())) {
			$excel->WriteText($row, $col, $movie->getCategory()->getName());
		}
		$col++;
		
		// Write The Media type
		if (!is_null($movie->getMediaType())) {
			$mediaTypeObj = $movie->getMediaType();
			$excel->WriteText($row, $col, $mediaTypeObj[0]->getDetailedName());
		}
		$col++;
				
		// Write The Year
		$excel->WriteNumber($row, $col, $movie->getYear());
		$col++;
		
		// Write The Media index
		$arr = $SETTINGSClass->getMetadata($movie->getID(), $user_id, 'mediaindex');
		if (is_array($arr) && sizeof($arr) == 1) {
			$excel->WriteText($row, $col, $arr[0]->getMetadataValue());
		}
		$col++;
	}
	
	//stream Excel for user to download or show on browser
	$excel->SendFile();
}

/** 
	Process user uploaded Excel file containing movies
*/

function checkExcelImport(&$out_movietitles) {
	global $ClassFactory;
	$upload =& new uploader();
	$SETTINGSClass = $ClassFactory->getInstance("vcd_settings");
	$path = $SETTINGSClass->getSettingsByKey('SITE_ROOT');
	
	if($_FILES){
		foreach($_FILES as $key => $file){
			$upload->set("name",$file["name"]); // Uploaded file name.
			$upload->set("type",$file["type"]); // Uploaded file type.
			$upload->set("tmp_name",$file["tmp_name"]); // Uploaded tmp file name.
			$upload->set("error",$file["error"]); // Uploaded file error.
			$upload->set("size",$file["size"]); // Uploaded file size.
			$upload->set("fld_name",$key); // Uploaded file field name.
			$upload->set("max_file_size",10192000); // Max size allowed for uploaded file in bytes =  ~10 MB.
			$upload->set("supported_extensions",array("xls" => "application/vnd.ms-excel")); // Allowed extensions and types for uploaded file.
			$upload->set("randon_name",true); // Generate a unique name for uploaded file? bool(true/false).
			$upload->set("replace",true); // Replace existent files or not? bool(true/false).
			$upload->set("dst_dir",$_SERVER["DOCUMENT_ROOT"]."".$path."upload/"); // Destination directory for uploaded files.
			$result = $upload->moveFileToDestination(); // $result = bool (true/false). Succeed or not.
		}
	}
	
	if($upload->succeed_files_track) {
		$file_arr = $upload->succeed_files_track; 
		$upfile = $file_arr[0]['destination_directory'].$file_arr[0]['new_file_name'];
			  
		/* 
			Process the Excel file
		*/
		
		if (fs_file_exists($upfile)) {
			require_once('classes/external/excel/reader.php');
			$data = new Spreadsheet_Excel_Reader();
			$data->read($upfile);
	
			// Generate Objects from the Excel file ...
			$imported_movies = array();
			
			if ($data->sheets[0]['numRows'] < 3 || $data->sheets[0]['numCols'] < 5) {
				VCDException::display("No movies found in the Excel file.");
				return false;
			} else if ($data->sheets[0]['cells'][2][1] != 'Title') {
				VCDException::display('Bad format');
				return false;
			} else {
				for ($i = 3; $i <= $data->sheets[0]['numRows']; $i++) {
					array_push($out_movietitles, $data->sheets[0]['cells'][$i][1]);
				}
			}
		} else {
			VCDException::display('Failed to open the uploaded file');
			return false;
		}
	} else {
		VCDException::display('Error uploading file');
		return false;
	}
	
	return $file_arr[0]['new_file_name'];
}

function processExcelMovies($upfile) {
	global $ClassFactory;
	$SETTINGSClass = $ClassFactory->getInstance("vcd_settings");
	$VCDClass = new vcd_movie();

	if (fs_file_exists($upfile)) {
		require_once('classes/external/excel/reader.php');
		$data = new Spreadsheet_Excel_Reader();
		$data->read($upfile);
	} else {
		VCDException::display('Failed to open the uploaded file<break> for the movies');
	}
	
	// GenerateObjects from the Excel file ...
	$imported_movies = array();
   
	// Create the results display array
	$results_array = array();
	
	for ($i = 3; $i <= $data->sheets[0]['numRows']; $i++) {
		// Create the basic CD obj
		$basic = array('',
				(string)$data->sheets[0]['cells'][$i][1],
				$SETTINGSClass->getCategoryIDByName((string)$data->sheets[0]['cells'][$i][2]),
				(string)$data->sheets[0]['cells'][$i][4]);
		$vcd = new vcdObj($basic);
		
		// Add 1 instance
		$mediaTypeObj = $SETTINGSClass->getMediaTypeByName((string)$data->sheets[0]['cells'][$i][3]);
		
		if ($mediaTypeObj) {
			$vcd->addInstance($_SESSION['user'], $mediaTypeObj, 1, time());
				
			try {
				$new_vcdid = $VCDClass->addVcd($vcd);
				if (is_numeric($new_vcdid) && $new_vcdid > 0) {
					$mediaindex = $data->sheets[0]['cells'][$i][5];
					
					if ($mediaindex) {
						$SETTINGSClass->addMetadata(new metadataObj(array('', $new_vcdid, $_SESSION['user']->getUserId(), 'mediaindex', $mediaindex)));
					} else {
						$mediaindex = 0;
					}
					
					$itemresult = array('status' => 1, 'title' => $vcd->getTitle(), 'mediaindex' => $mediaindex);
				} else {
					$itemresult = array('status' => 0, 'title' => $vcd->getTitle(), 'mediaindex' => 0);
				}
			} catch (Exception $e) {
				$itemresult = array('status' => 0, 'title' => $vcd->getTitle(), 'mediaindex' => 0);
			}
			
			array_push($results_array, $itemresult);
		} else {
			array_push($resuls_array, array('status' => 0, 'title' => $vcd->getTitle(), 'mediaindex' => 0));
		}
	}
	
	/*foreach ($imported_movies as $cdobj) {
		try {
			$new_vcdid = $VCDClass->addVcd($cdobj);
			if (is_numeric($new_vcdid) && $new_vcdid > 0) {
				$itemresult = array('status' => 1, 'title' => $cdobj->getTitle(), 'thumb' => $cdobj->getCoverCount());
			} else {
				$itemresult = array('status' => 0, 'title' => $cdobj->getTitle(), 'thumb' => 0);
			}
		} catch (Exception $e) {
			$itemresult = array('status' => 0, 'title' => $cdobj->getTitle(), 'thumb' => 0);
		}
		
		array_push($results_array, $itemresult);
	}*/
	
	fs_unlink($upfile);
	
	$_SESSION['excelresults'] = $results_array;
	redirect('?page=private&o=add&source=excelresults');
}

function getCategoryResults($catArr, $dataArray) {
	$resultArr = $catArr;
	$keys = array_keys($resultArr);
	foreach ($keys as $key) {
		$resultArr[$key] = 0;
	}
	foreach ($dataArray as $inArr) {
		$resultArr[$inArr[0]] = $inArr[1];
	}
	return $resultArr;
}
?>