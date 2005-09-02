<?php
/*
 * VCD-db - a web based VCD/DVD Catalog system
 * Copyright (C) 2003-2004 Konni - konni.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 */
 // $Id:
?>
<? 
include_once("classes/includes.php");
require_once('classes/fetch/fetch_dvdempire.php');
include_once('classes/external/Image_Toolbox.class.php');

if (!VCDUtils::isLoggedIn()) {
	die("Unauthorized Access");
}


if (isset($_GET['action']) && sizeof($_POST) > 0) {
	$form = $_GET['action'];
} else {
	die("I only accept post!");
}

$reload_and_close = true;


$SETTINGSClass = $ClassFactory->getInstance("vcd_settings");
$VCDClass = $ClassFactory->getInstance("vcd_movie");

switch ($form) {

	case 'borrower':
		$obj = new borrowerObj(array("",$_SESSION['user']->getUserID(),$_POST['borrower_name'], $_POST['borrower_email']));
		$SETTINGSClass->addBorrower($obj);
		VCDUtils::setMessage("(Added ".$_POST['borrower_name']." to your list)");
		break;

	case 'edit_borrower':
		$borrowerObj = $SETTINGSClass->getBorrowerByID($_POST['borrower_id']);
		$borrowerObj->setEmail($_POST['borrower_email']);
		$SETTINGSClass->updateBorrower($borrowerObj);
		VCDUtils::setMessage("(".$borrowerObj->getName()." has been updated)");
		redirect('?page=private&o=settings');
		break;
		
		
	case 'loan':
		$arrMovies = split("#",$_POST['id_list']);
		$borrower_id = $_POST['borrowers'];
		$SETTINGSClass->loanCDs($borrower_id, $arrMovies);
		VCDUtils::setMessage("(Movies successfully loaned)");
		header("Location: ".$_SERVER['HTTP_REFERER'].""); 
		break;
		
	
	case 'addfeed':	
		
		$feeds = $_POST['feeds'];
		foreach ($feeds as $feed) {
			$currFeed = explode("|",$feed);
			$SETTINGSClass->addRssfeed($_SESSION['user']->getUserID(), $currFeed[0], $currFeed[1]);
		}
		
		break;
	
	case 'addcomment':
		
	
		if (isset($_POST['comment']) && isset($_POST['vcd_id'])) {
			$is_private = 0;
			if (isset($_POST['private'])) {
				$is_private = 1;				
			}
			$commObj = new commentObj(array('', $_POST['vcd_id'], $_SESSION['user']->getUserID(), '', $_POST['comment'], $is_private));
			
			if (strlen($_POST['comment']) > 0) {
				$SETTINGSClass->addComment($commObj);
			}
			
		}
				
		redirect("?page=cd&vcd_id=".$_POST['vcd_id']."#comments");
		
		break;
		
	case 'addlisted':
		$arrMovies = split("#",$_POST['id_list']);
		if (is_array($arrMovies) && sizeof($arrMovies) > 0) {
			// Push the ID list to session
			$_SESSION['listed'] = $arrMovies;
			redirect('.?page=private&o=add&source=listed');
		} else {
			redirect();
		}
					
		break;
		
	
	/* Update media player settings */
	case 'player':
		$obj = new metadataObj(array('',0,$_SESSION['user']->getUserID(), 'player', $_POST['player']));
		$SETTINGSClass->addMetadata($obj);	
		$obj = new metadataObj(array('',0,$_SESSION['user']->getUserID(), 'playerpath', $_POST['params']));
		$SETTINGSClass->addMetadata($obj);	
		redirect('pages/player.php');
		break;
		
		
	case 'listedconfirm':
		
		$VCDClass = new vcd_movie();
		
		if (isset($_POST['disccount'])) {
			$j = $_POST['disccount'];
			for ($i=0; $i < $j; $i++) {
				
				$key = "item_".$i;
				$cds = "cds_".$i;
				$valkey = $_POST[$key];
				$valcds = $_POST[$cds];
				$arr = split("\\|",$valkey);
				$VCDClass->addVcdToUser($_SESSION['user']->getUserID(), $arr[0], $arr[1], $valcds);
			}
		}
		redirect();
		break;
	
	case 'edit_frontpage':
		
		if (isset($_POST['stats']) && strcmp($_POST['stats'], "yes") == 0) {
			// User wants to see statistics
			$frontstatsObj = new metadataObj(array('',0, $_SESSION['user']->getUserID(), 'frontstats', 1));
		} else {
			$frontstatsObj = new metadataObj(array('',0, $_SESSION['user']->getUserID(), 'frontstats', 0));
		}
		
		if (isset($_POST['sidebar']) && strcmp($_POST['sidebar'], "yes") == 0) {
			// User wants to see sidebar
			$frontbarObj = new metadataObj(array('',0, $_SESSION['user']->getUserID(), 'frontbar', 1));
		} else {
			$frontbarObj = new metadataObj(array('',0, $_SESSION['user']->getUserID(), 'frontbar', 0));
		}
	
		if (isset($_POST['id_list']) && strlen($_POST['id_list']) > 1) {
			$frontRssObj = new metadataObj(array('',0, $_SESSION['user']->getUserID(), 'frontrss', $_POST['id_list']));
		} else {
			$frontRssObj = new metadataObj(array('',0, $_SESSION['user']->getUserID(), 'frontrss', $_POST['id_list']));
		}
		
		$SETTINGSClass->addMetadata(array($frontbarObj, $frontRssObj, $frontstatsObj));
		
		
		redirect('?page=private&o=settings');
		
		break;
		
		
	case 'update_ignorelist':
		if (isset($_POST['id_list'])) { 
			// Save the ignore list to database
			$obj = new metadataObj(array('',0, $_SESSION['user']->getUserID(), 'ignorelist', $_POST['id_list']));
			$SETTINGSClass->addMetadata($obj);
		}
	
		redirect('?page=private&o=settings');
		break;
		
	case 'addfromxml':
		// call to XMLFunctions
		$movie_titles = array();
		$file_name = checkMovieImport($movie_titles);
		
		if ($file_name) {
			$_SESSION['xmldata'] = $movie_titles;
			$_SESSION['xmlfilename'] = $file_name;
			redirect('?page=private&o=add&source=xml');
		} else {
			$reload_and_close = false;
		}
		
		break;
		
	/* Actually insert the XML data */
	case 'xmlconfirm':
		$filename = "";
		if (isset($_POST['filename'])) {
			$filename = $_POST['filename'];
			$use_covers = false;
						
			$fullpath = TEMP_FOLDER.$filename;
			if (!fs_is_dir($fullpath) && fs_file_exists($fullpath)) {

				if (isset($_POST['xmlthumbs'])) {
					$use_covers = true;
				}
				
				// Process the file	
				processXMLMovies($fullpath, $use_covers);
			}
		}
		break;
	
	case 'addfromexcel':
		$movie_titles = array();
		$file_name = checkExcelImport($movie_titles);
		
		if ($file_name) {
			$_SESSION['exceldata'] = $movie_titles;
			$_SESSION['excelfilename'] = $file_name;
			redirect('?page=private&o=add&source=excel');
		} else {
			redirect('?page=private&o=new');
		}
		break;
		
	/* Actually insert the Excel data */
	case 'excelconfirm':
		$filename = "";
		if (isset($_POST['filename'])) {
			$filename = $_POST['filename'];
						
			$fullpath = TEMP_FOLDER.$filename;
			if (!fs_is_dir($fullpath) && fs_file_exists($fullpath)) {
				// Process the file	
				processExcelMovies($fullpath);
			}
		}
		break;
	
	case 'add_manually':
		// Create the basic CD obj
		$basic = array("", $_POST['title'], $_POST['category'], $_POST['year']);
		$vcd = new vcdObj($basic);
		// Add 1 instance
		$vcd->addInstance($_SESSION['user'], $SETTINGSClass->getMediaTypeByID($_POST['mediatype']), $_POST['cds'], mktime());
		// if file was uploaded .. lets process it ..
		
		$upload =& new uploader();
		$path = $SETTINGSClass->getSettingsByKey('SITE_ROOT');
		
						
		if($_FILES){
	  
		  foreach($_FILES as $key => $file){
	
		    $upload->set("name",$file["name"]); // Uploaded file name.
		    $upload->set("type",$file["type"]); // Uploaded file type.
		   	$upload->set("tmp_name",$file["tmp_name"]); // Uploaded tmp file name.
		    $upload->set("error",$file["error"]); // Uploaded file error.
		    $upload->set("size",$file["size"]); // Uploaded file size.
		    $upload->set("fld_name",$key); // Uploaded file field name.
			$upload->set("max_file_size",209600); // Max size allowed for uploaded file in bytes =  ~200 KB.
			$upload->set("file_perm", 0777);
		    $upload->set("supported_extensions",array("jpg" => "image/pjpeg", "jpg" => "image/jpeg" ,"gif" => "image/gif")); // Allowed extensions and types for uploaded file.
		    $upload->set("randon_name",true); // Generate a unique name for uploaded file? bool(true/false).
			$upload->set("replace",false); // Replace existent files or not? bool(true/false).
			$upload->set("dst_dir",$_SERVER["DOCUMENT_ROOT"]."".$path."upload/"); // Destination directory for uploaded files.
			$result = $upload->moveFileToDestination(); // $result = bool (true/false). Succeed or not.
			
		  }
		  
		  
		  if($upload->succeed_files_track){
	       	   $file_arr = $upload->succeed_files_track; 
	      	   $upfile = $file_arr[0]['destination_directory'].$file_arr[0]['new_file_name'];
	      	   $f_name = $file_arr[0]['new_file_name'];
	      	   
	      	   // Resize the thumbnail
	      	   // Release the file hook
	      	   	unset($upload);
	      	   		
      	   		$im = new Image_Toolbox(TEMP_FOLDER.$f_name);
				$im->newOutputSize(0,140);
				$im->save(TEMP_FOLDER.$f_name, 'jpg');
				unset($im);
      	   		//fs_unlink(TEMP_FOLDER.$f_name);
				
			  	
			  	$cover = new cdcoverObj();
			
				// Get a Thumbnail CoverTypeObj
				$COVERClass = $ClassFactory->getInstance("vcd_cdcover");
				$coverTypeObj = $COVERClass->getCoverTypeByName("thumbnail");
				$cover->setCoverTypeID($coverTypeObj->getCoverTypeID());	
				$cover->setCoverTypeName("thumbnail");
				$cover->setFilename($f_name);
				$vcd->addCovers(array($cover));
      	   		
		  		 
			} else {
				if ($upload->fail_files_track[0]['error_type'] != 6) {
		  		 	VCDException::display($upload->fail_files_track[0]['msg'],true);
			  	}
			}
		}

		
		// Forward the movie to the Business layer
		$new_id = $VCDClass->addVcd($vcd);
		
		
		// Insert the user comments if any ..
		if (isset($_POST['comment']) && (strlen($_POST['comment']) > 1)) {
			$is_private = 0;
			if (isset($_POST['private'])) {
				$is_private = 1;				
			}
			
			$commObj = new commentObj(array('', $new_id, $_SESSION['user']->getUserID(), '', $_POST['comment'], $is_private));
			$SETTINGSClass->addComment($commObj);
		}
				
		if (is_numeric($new_id) && $new_id != -1) {
			redirect("?page=cd&vcd_id=".$new_id."");
		}
		
		break;
		
		
		
	/* Add a movie to the database from the IMDB form */
	case 'addfromimdb':
		
		// Create the basic CD obj
		$basic = array("", $_POST['title'], $_POST['category'], $_POST['year']);
		$vcd = new vcdObj($basic);
		
		// Add 1 instance
		$vcd->addInstance($_SESSION['user'], $SETTINGSClass->getMediaTypeByID($_POST['mediatype']), $_POST['cds'], mktime());
		
		// Create the IMDB obj
		$obj = new imdbObj();
		$obj->setIMDB($_POST['imdb']);
		$obj->setTitle($_POST['imdbtitle']);
		$obj->setAltTitle($_POST['alttitle']);
		$obj->setYear($_POST['year']);
		$obj->setImage($_POST['image']);
		$obj->setDirector($_POST['director']);
		$obj->setGenre($_POST['categories']);
		$obj->setRating($_POST['rating']);
		$obj->setCast($_POST['cast']);
		$obj->setPlot($_POST['plot']);
		$obj->setRuntime($_POST['Runtime']);
		$obj->setCountry($_POST['Country']);
		
		// Add the imdbObj to the VCD
		$vcd->setIMDB($obj);
		
		
		// Add the thumbnail as a cover if any was found on IMDB
		if (isset($_POST['image']) && strcmp($_POST['image'], "") != 0) {
			$cover = new cdcoverObj();
			
			// Get a Thumbnail CoverTypeObj
			$COVERClass = $ClassFactory->getInstance("vcd_cdcover");
			$coverTypeObj = $COVERClass->getCoverTypeByName("thumbnail");
			$cover->setCoverTypeID($coverTypeObj->getCoverTypeID());	
			$cover->setCoverTypeName("thumbnail");
			
			
			$cover->setFilename($_POST['image']);
			$vcd->addCovers(array($cover));
		}
		
		
		// Set the source site
		$sourceSiteObj = $SETTINGSClass->getSourceSiteByAlias('imdb');
		if ($sourceSiteObj instanceof sourceSiteObj ) {
			$vcd->setSourceSite($sourceSiteObj->getsiteID(), $_POST['imdb']);
		}
				
		// Forward the movie to the Business layer
		$new_id = $VCDClass->addVcd($vcd);
		
		
		// Insert the user comments if any ..
		if (isset($_POST['comment']) && (strlen($_POST['comment']) > 1)) {
			$is_private = 0;
			if (isset($_POST['private'])) {
				$is_private = 1;				
			}
			
			$commObj = new commentObj(array('', $new_id, $_SESSION['user']->getUserID(), '', $_POST['comment'], $is_private));
			$SETTINGSClass->addComment($commObj);
		}
		
				
		if (is_numeric($new_id) && $new_id != -1) {
			redirect("?page=cd&vcd_id=".$new_id."");
		}
		
		break;
		
		
		/* Add movie from the DVDEmpire fetcher */
		case 'addfromempire':
			// Create the basic CD obj
			$basic = array("", $_POST['title'], $_POST['category'], $_POST['year']);
			$vcd = new vcdObj($basic);
			
			// Add 1 instance
			$vcd->addInstance($_SESSION['user'], $SETTINGSClass->getMediaTypeByID($_POST['mediatype']), $_POST['cds'], mktime());
			
			// Set the categoryObj
			$vcd->setMovieCategory($SETTINGSClass->getMovieCategoryByID($_POST['category']));
			
			
			// Add the thumbnail as a cover if any was found on IMDB
			if (isset($_POST['thumbnail'])) {
				$cover = new cdcoverObj();
				
				// Get a Thumbnail CoverTypeObj
				$COVERClass = $ClassFactory->getInstance("vcd_cdcover");
				$coverTypeObj = $COVERClass->getCoverTypeByName("thumbnail");
				$cover->setCoverTypeID($coverTypeObj->getCoverTypeID());	
				$cover->setCoverTypeName("thumbnail");
				
				
				$cover->setFilename($_POST['thumbnail']);
				$vcd->addCovers(array($cover));
			}
		
		
			// Set the source site
			$sourceSiteObj = $SETTINGSClass->getSourceSiteByAlias('DVDempire');
			if ($sourceSiteObj instanceof sourceSiteObj ) {
				$vcd->setSourceSite($sourceSiteObj->getsiteID(), $_POST['id']);
			}
			
			// Set the adult studio if any
			if (isset($_POST['studio']) && is_numeric($_POST['studio'])) {
				$vcd->setStudioID($_POST['studio']);
			}
			
			// Associate the existing pornstars to the CD
			$PORNClass = new vcd_pornstar();
			
			
			// Set the adult categories
			if (isset($_POST['id_list'])) {
	     		$adult_categories = split('#',$_POST['id_list']);

	     		if (sizeof($adult_categories) > 0) {
					foreach ($adult_categories as $adult_catid) {
						$catObj = $PORNClass->getSubCategoryByID($adult_catid);
						if ($catObj instanceof porncategoryObj ) {
							$vcd->addAdultCategory($catObj);
						}
						
					}
				}
	     	}
			
			
			
			
			
			
			
			if (isset($_POST['pornstars'])) {
				$pornstars = array_unique($_POST['pornstars']);
				foreach ($pornstars as $pornstar_id) {
					$vcd->addPornstars($PORNClass->getPornstarByID($pornstar_id));
				}
			}
			
			
						
			// and the new ones after we create them
			if (isset($_POST['pornstars_new'])) {
				$pornstars_new = array_unique($_POST['pornstars_new']);
				foreach ($pornstars_new as $new_names) {
					$vcd->addPornstars($PORNClass->addPornstar(new pornstarObj(array("",$new_names, "","",""))));
				}
			}
			
			
			// Check what images to fetch
			$screenFiles = array();
			if (isset($_POST['imagefetch'])) {
				$imagefetchArr = $_POST['imagefetch'];
				
				foreach ($imagefetchArr as $image_type) {
					if (strcmp($image_type, "screenshots") == 0) {
						
						if (isset($_POST['screenshotcount'])) {
							$screencount = $_POST['screenshotcount'];
							$empire = new fetch_dvdempire();
							$empire->setID($vcd->getExternalID());
							$empire->setScreenShotsCount($screencount);
							$screenFiles = $empire->getImagePath('screenshots');
						
						}
						
						
					} else {
					
						// Fetch the image from DVDEmpire
						$empire = new fetch_dvdempire();
						$empire->setID($vcd->getExternalID());
						$path = $empire->getImagePath($image_type);
						$image_name = VCDUtils::grabImage($path);
						
						$cover = new cdcoverObj();
						$COVERClass = $ClassFactory->getInstance("vcd_cdcover");
						$coverTypeObj = $COVERClass->getCoverTypeByName($image_type);
						$cover->setCoverTypeID($coverTypeObj->getCoverTypeID());	
						$cover->setCoverTypeName($image_type);
						
						$cover->setFilename($image_name);
						
						$vcd->addCovers(array($cover));
						
					}
				}
				
			}
			
			
			
			// Forward the movie to the Business layer
			$new_id = $VCDClass->addVcd($vcd);
			
			// Was I supposed to grab some screenshots ?
			if (sizeof($screenFiles) > 0) {
				
				// Does the destination folder exist?
				if (!fs_is_dir(ALBUMS.$new_id)) {
					if (fs_mkdir(ALBUMS.$new_id, 0755)) {
						
						foreach ($screenFiles as $screenshotImage) {
							VCDUtils::grabImage($screenshotImage, false, ALBUMS.$new_id."/");
						}
						
						// Mark thumbnails to movie in DB
						$VCDClass->markVcdWithScreenshots($new_id);
						
					} else {
						VCDException::display("Could not create directory ".ALBUMS.$new_id."<break>Check permissions");
					}
				}
				
				
			}
			
			// Insert the user comments if any ..
			if (isset($_POST['comment']) && (strlen($_POST['comment']) > 1)) {
				$is_private = 0;
				if (isset($_POST['private'])) {
					$is_private = 1;				
				}
				
				$commObj = new commentObj(array('', $new_id, $_SESSION['user']->getUserID(), '', $_POST['comment'], $is_private));
				$SETTINGSClass->addComment($commObj);
			}

			
			
			if (is_numeric($new_id) && $new_id != -1) {
				redirect("?page=cd&vcd_id=".$new_id."");
			}
			
		
		break;
		
		case 'updatepornstar':
		
			$pornstar_id = $_POST['star_id'];
			$pornstar_name = $_POST['name'];
			$pornstar_bio = $_POST['bio'];
			$pornstar_url = $_POST['www'];
			
		
			$PORNClass = $ClassFactory->getInstance("vcd_pornstar");
			$pornstar = $PORNClass->getPornstarByID($pornstar_id);
			$pornstar->setName($pornstar_name);
			$pornstar->setHomePage($pornstar_url);
			$pornstar->setBiography($pornstar_bio);
					
			
			// if file was uploaded .. lets process it ..
		
				$upload =& new uploader();
				$path = $SETTINGSClass->getSettingsByKey('SITE_ROOT');
				
								
				if($_FILES){
			  
				  foreach($_FILES as $key => $file){
	  	
				    $upload->set("name",$file["name"]); // Uploaded file name.
				    $upload->set("type",$file["type"]); // Uploaded file type.
				   	$upload->set("tmp_name",$file["tmp_name"]); // Uploaded tmp file name.
				    $upload->set("error",$file["error"]); // Uploaded file error.
				    $upload->set("size",$file["size"]); // Uploaded file size.
				    $upload->set("fld_name",$key); // Uploaded file field name.
				    $upload->set("file_perm", 0777);
					$upload->set("max_file_size",109600); // Max size allowed for uploaded file in bytes =  ~100 KB.
				    $upload->set("supported_extensions",array("jpg" => "image/pjpeg" , "jpg" => "image/jpeg")); // Allowed extensions and types for uploaded file.
				    $upload->set("randon_name",false); // Generate a unique name for uploaded file? bool(true/false).
					$upload->set("replace",false); // Replace existent files or not? bool(true/false).
					$upload->set("dst_dir",$_SERVER["DOCUMENT_ROOT"]."".$path."upload/"); // Destination directory for uploaded files.
					$result = $upload->moveFileToDestination(); // $result = bool (true/false). Succeed or not.
					
				  }
				  
				  
				  if($upload->succeed_files_track){
			       	   $file_arr = $upload->succeed_files_track; 
			      	   $upfile = $file_arr[0]['destination_directory'].$file_arr[0]['new_file_name'];
			      	   $f_name = $upload->succeed_files_track[0]['file_name'];
			      	   
			      	   // Check if image should be resized
			      	   if (isset($_POST['resize']) && $_POST['resize']) {
		      	   		
			      	   		// Release the file hook
			      	   		unset($upload);
			      	   		
			      	   		$im = new Image_Toolbox(TEMP_FOLDER.$f_name);
							$im->newOutputSize(0,200);
							$im->save(PORNSTARIMAGE_PATH.$f_name, 'jpg');
							unset($im);
			      	   		fs_unlink(TEMP_FOLDER.$f_name);
							
					  	} else {
					  		fs_rename(TEMP_FOLDER.$f_name, PORNSTARIMAGE_PATH.$f_name);
					  	}
					  	
					  	
					  	$pornstar->setImageName($f_name);
				  		 
					} else {
						if ($upload->fail_files_track[0]['error_type'] != 6) {
				  		 	VCDException::display($upload->fail_files_track[0]['msg'],true);
					  	}
					}
				}
			      	   
			$PORNClass->updatePornstar($pornstar);
					
			if (isset($_POST['update'])) {
				redirect("pages/pmanager.php?pornstar_id=".$pornstar_id.""); /* Redirect back to form */ 
			}
			
		
			break;
	
		
	case 'updatemovie':
	
		 $errors = false;
	
		 // Basic data
		 $cd_id = $_POST['cd_id'];
    	 $title = $_POST['title'];
	     $category = $_POST['category'];
	     $year = $_POST['year'];
	     if (isset($_POST['imdb'])) {
	     	$imdb = $_POST['imdb'];
	     }
	     
	     
	     
	     
	     // Fetch the current data from DB
	     $VCDClass = $ClassFactory->getInstance('vcd_movie');
	     $vcd = $VCDClass->getVcdByID($cd_id);
	     
	     
	     
	     $vcd->setYear($year);
	     $vcd->setTitle($title);
	     
	     
	     $movieCategoryObj = $SETTINGSClass->getMovieCategoryByID($category);
	     if ($movieCategoryObj instanceof movieCategoryObj ) {
	     	$vcd->setMovieCategory($movieCategoryObj);
	     }
	     
	     // External ID already exists
	     if ($vcd->getSourceSiteID() == $SETTINGSClass->getSourceSiteByAlias('imdb')->getsiteID()) {
	     	$vcd->setSourceSite($vcd->getSourceSiteID(), $imdb);
	     } 
	     
	     
	     
	     // is this by any means blue movie ?
	     if ($category == $SETTINGSClass->getCategoryIDByName('adult')) {
	     	// Blue movie data
	     	if (isset($_POST['id_list'])) {
	     		$subCatArr = split('#',$_POST['id_list']);
	     		$PORNClass = $ClassFactory->getInstance('vcd_pornstar');
	     		foreach ($subCatArr as $adult_catid) {
	     			$adultCatObj = $PORNClass->getSubCategoryByID($adult_catid);
	     			if ($adultCatObj instanceof porncategoryObj ) {
	     				$vcd->addAdultCategory($adultCatObj);
	     			}
	     		}
	     	}
	     	
	     	
	     	if (isset($_POST['studio']) && is_numeric($_POST['studio']))  {
	     		$vcd->setStudioID($_POST['studio']);
	     	}
	     	
	     		     	
	     
	     } else {
	     	// Imdb data
	     	 $imdb_title = $_POST['imdbtitle'];
		     $imdb_alttitle =  $_POST['imdbalttitle'];
		     $imdb_grade = $_POST['imdbgrade'];
		     $imdb_runtime = $_POST['imdbruntime'];
		     $imdb_director =  $_POST['imdbdirector'];
		     $imdb_countries = $_POST['imdbcountries'];
		     $imdb_categories = $_POST['imdbcategories'];
		     $imdb_plot = $_POST['plot'];
		     $imdb_actors =  $_POST['actors'];
		     
	     	// Get current data
	     	 $imdbObj = $vcd->getIMDB();
	     	 if (!$imdbObj instanceof imdbObj ) {
	     	 	$imdbObj = new imdbObj();	
	     	 	$imdbObj->setIMDB($imdb);
	     	 }
     	 	 $imdbObj->setTitle($imdb_title);
     	 	 $imdbObj->setAltTitle($imdb_alttitle);
     	 	 $imdbObj->setRating($imdb_grade);
     	 	 $imdbObj->setDirector($imdb_director);
      	 	 $imdbObj->setCountry($imdb_countries);
     	 	 $imdbObj->setGenre($imdb_categories);
     	 	 $imdbObj->setPlot($imdb_plot);
     	 	 $imdbObj->setCast($imdb_actors);
     	 	 $imdbObj->setRuntime($imdb_runtime);
	     	 
     	 	 if (strcmp($imdbObj->getIMDB(), "" != 0)) {
     	 	 	$vcd->setIMDB($imdbObj);
     	 	 }
     	 	 
	     }
	     
	     // Check if user has updated his cd item
	     $arrCopies = $vcd->getInstancesByUserID($_SESSION['user']->getUserID());
	     if (sizeof($arrCopies) > 0) {
			$arrMediaTypes = $arrCopies['mediaTypes'];
			$arrNumcds = $arrCopies['discs'];
			// Loop through the instances and compare 
			for ($i = 0; $i < sizeof($arrMediaTypes); $i++) {
				$postedMediaType = $_POST["userMediaType_".$i];
				$media_id = $arrMediaTypes[$i]->getmediaTypeID();
				$postedCDCount   = $_POST["usernumcds_".$i];
				if ($media_id == $postedMediaType && $arrNumcds[$i] == $postedCDCount) {} 
					else {
					// Either media type or numCD's have been updated .. update entry to DB
					$VCDClass->updateVcdInstance($cd_id, $postedMediaType, $media_id, $postedCDCount, $arrNumcds[$i]);
				}
			}
		 }
	     
	     
	     
	    // Update metadata
	    if (isset($_POST['custom_index']) && strlen($_POST['custom_index']) > 0) {
	    	// add or update ?
	    	$metaArr = $SETTINGSClass->getMetadata($vcd->getID(), $_SESSION['user']->getUserID(), 'mediaindex');
	    	if (sizeof($metaArr) == 1) {
	    		$obj = $metaArr[0];
	    		$obj->setMetadataValue($_POST['custom_index']);
	    		$SETTINGSClass->updateMetadata($obj);
	    	} else {
	    		$obj = new metadataObj(array('',$cd_id, $_SESSION['user']->getUserID(), 'mediaindex', $_POST['custom_index']));
	    		$SETTINGSClass->addMetadata($obj);
	    	}
	    }
	     
	    if (isset($_POST['filepath']) && strlen($_POST['filepath']) > 0) {
	    	$obj = new metadataObj(array('',$cd_id, $_SESSION['user']->getUserID(), 'filelocation', $_POST['filepath']));
	    	$SETTINGSClass->addMetadata($obj);
	    }
	     
	    
	    /*
	    	Process uploaded images
	    */
	    $upload = new uploader();
	    $path = $SETTINGSClass->getSettingsByKey('SITE_ROOT');

		if($_FILES){
		  foreach($_FILES as $key => $file){
		   $upload->set("name",$file["name"]); // Uploaded file name.
		    $upload->set("type",$file["type"]); // Uploaded file type.
		   	$upload->set("tmp_name",$file["tmp_name"]); // Uploaded tmp file name.
		    $upload->set("error",$file["error"]); // Uploaded file error.
		    $upload->set("size",$file["size"]); // Uploaded file size.
		    $upload->set("fld_name",$key); // Uploaded file field name.
			$upload->set("max_file_size",509600); // Max size allowed for uploaded file in bytes =  ~500 KB.
			$upload->set("file_perm", 0777);
		    $upload->set("supported_extensions",array("jpg" => "image/pjpeg", "jpg" => "image/jpeg")); // Allowed extensions and types for uploaded file.
		    $upload->set("randon_name",false); // Generate a unique name for uploaded file? bool(true/false).
			$upload->set("replace",true); // Replace existent files or not? bool(true/false).
			$upload->set("dst_dir",$_SERVER["DOCUMENT_ROOT"]."".$path."upload/"); // Destination directory for uploaded files.
			$result = $upload->moveFileToDestination(); // $result = bool (true/false). Succeed or not.
		  } 
		}
		
		if($upload->succeed_files_track){
		      $file_arr = $upload->succeed_files_track; 
		      		      
		      
		      // Check which covertypes were uploaded and update them
		      $COVERClass = $ClassFactory->getInstance('vcd_cdcover');
		      foreach ($upload->succeed_files_track as $cfile) {
		      		$cover_typeid = $cfile['field_name'];
		      		$coverType = $COVERClass->getCoverTypeById($cover_typeid);
		      		
		      		$imginfo = array('', $cd_id, $cfile['new_file_name'], $cfile['file_size'], 
		      							 $_SESSION['user']->getUserID(), date(time()), $cover_typeid,
		      							 $coverType->getCoverTypeName(), '');
		      		$cdcover = new cdcoverObj($imginfo);
		      		$vcd->addCovers(array($cdcover));
		      }
		 } 
		 
		if ($upload->fail_files_track) {
			foreach ($upload->fail_files_track as $msg) {
				if ($msg['error_type'] != 6) {
					VCDException::display("Error with file ".$msg['file_name']."<break>". $msg['msg']);
					$errors = true;
				}
			}
		}

		unset($upload); 
		
		$VCDClass->updateVcd($vcd);
	    
	    
	    if (isset($_POST['update'])) {
	    	if ($errors) {
	    		print "<script>alert('Errors occurred');history.back(-1)</script>";
	    	} else {
	    		redirect("pages/manager.php?cd_id=".$cd_id."&do=reload"); /* Redirect back to form */ 
	    	}
	    } 
	    	
    	
			
		break;
			
			
			
			
			
	default:
		die("Unspecified form handler!");
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head><title>VCD Gallery</title>
	<link rel="stylesheet" type="text/css" href="<?=STYLE?>style.css"/>
	<script src="includes/js/main.js" type="text/javascript"></script>
</head>
<body <?if ($reload_and_close) { reloadandclose(); } ?>>



</body>
</html>