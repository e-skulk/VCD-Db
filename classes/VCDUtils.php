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
 * @package Core
 * @version $Id$
 */
?>
<?PHP


class VCDUtils {

	/**
	* @return Array
	* @param $location String
	* @param $fileregex String
	* @desc Search folder for files with certain extensions defined in the $fileregex parameter.
 */
	static function findfile($location='',$fileregex='') {
   		if (!$location or !is_dir($location) or !$fileregex) {
       		return false;
   		}
 
		$matchedfiles = array();
	 
	   	$all = opendir($location);
	   	while ($file = readdir($all)) {
	       	if (is_dir($location.'/'.$file) and $file <> ".." and $file <> ".") {
	         	$subdir_matches = findfile($location.'/'.$file,$fileregex);
	         	$matchedfiles = array_merge($matchedfiles,$subdir_matches);
	         	unset($file);
	       	}
	       	elseif (!is_dir($location.'/'.$file)) {
	         	if (preg_match($fileregex,$file)) {
	             	array_push($matchedfiles,$location.'/'.$file);
	         	}
		       }
	   		}	
	   	   closedir($all);
		   unset($all);
	       return $matchedfiles;
 	}
 	
 	
 	
 	
 	
 	
 	/**
	* @return Boolean
	* @param $recipent Mixed
	* @param $subject String
	* @param $body String
	* @param $use_html Boolean
	* @desc Send email, $recipent can either be an email address or an array of email addresses.
 */
	static function sendMail($recipent, $subject, $body, $use_html=false) {
		if (is_array($recipent)) {
			
			$b_ok = true;
			foreach ($recipent as $email) {
				$b_ok = sendMail($email, $subject, $body, $use_html);
				if (!$b_ok)	{
					break;
					return false;
				}
			}
			
			return true;
			
		} else {
			return sendMail($recipent, $subject, $body, $use_html);
		}
	}
 	
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown $float
	 * @return unknown
	 */
	static function getmicrotime($float = false) { 
		   list($usec, $sec) = explode(" ", microtime()); 
		   if ($float)
		   		return ((float)$usec + (float)$sec); 
		   else 
		   		return ((int)(float)$usec + (float)$sec); 
		   
	} 
 	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	static function getPageLoadTime() {
		global $start_time;
		$end = VCDUtils::getmicrotime(true);
		$run = $end - $start_time; 
		return substr($run, 0, 5);
	}
 	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	static function getOS() {
		return PHP_OS;
	}
 	
	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	static function isLoggedIn() {
		if (isset($_SESSION['user']) && $_SESSION['user'] instanceof userObj) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Get the current users ID, if user is not logged in null is returned.
	 *
	 * @return int
	 */
	static function getUserID() {
		if (VCDUtils::isLoggedIn()) {
			return $_SESSION['user']->getUserID();
		} else {
			return null;
		}
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown $user_id
	 * @return unknown
	 */
	static function isUsingFilter($user_id) {
		
		$SETTINGSClass = VCDClassFactory::getInstance('vcd_settings');
		$metaArr = $SETTINGSClass->getMetadata(0, $user_id, 'ignorelist');
		if (is_array($metaArr) && sizeof($metaArr) > 0) {
			
			if ($metaArr[0] instanceof metadataObj && strcmp(trim($metaArr[0]->getMetaDataValue()), "") != 0) {
				return true;
			}
			
			return false;
			
		} else {
			return false;
		}
	}
	
	/**
	 * Enter description here...
	 *
	 * @param unknown $text
	 * @param unknown $length
	 * @return unknown
	 */
	static function shortenText($text, $length) {
		if (strlen($text) > $length) {
				$text = substr($text, 0, $length);
				$text = $text."..."; 
			}
		return $text;
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown $date1
	 * @param unknown $date2
	 * @return unknown
	 */
	static function getDaydiff($date1, $date2 = null) {
		if (is_null($date2)) {
			$date2 = mktime();
			$datediff = $date2 - $date1;
			
		} else {
			$datediff = $date2 - $date1;
		}
		
			
		global $language;
		
			
		if (floor($datediff/60/60/24) > 0) {
			if (floor($datediff/60/60/24) == 1) {
				return floor($datediff/60/60/24) . " ". $language->show('LOAN_DAY'); 
			} else {
				return floor($datediff/60/60/24) . " ". $language->show('LOAN_DAYS'); 
			}
			
		} elseif (floor($datediff/60/60) > 0) {
			return $language->show('LOAN_TODAY'); 
			
		} else {
			//return floor($datediff/60) . " m�n.";
			return "1 " . $language->show('LOAN_DAY');
			
		}
		
				
	}
	
 	
	static public function getCharSet() {
		global $language;
		return $language->show('LANG_CHARSET');
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown $strMessage
	 */
	static function setMessage($strMessage) {
		$_SESSION['message'] = $strMessage;
	}
	
	/**
	 * Enter description here...
	 *
	 * @return unknown
	 */
	static function getMessage() {
		$message = "";
		if (isset($_SESSION['message'])) {
			$message = $_SESSION['message'];
			unset($_SESSION['message']);
		}
		return $message;
	}
	
	
	/**
	 * Enter description here...
	 *
	 * @param unknown $image_url
	 * @param unknown $uniqueID
	 * @param unknown $destination
	 * @return unknown
	 */
	static function grabImage($image_url, $uniqueID = true, $destination = TEMP_FOLDER) {
     
      $source = urldecode($image_url);
	  
      if (defined('USE_PROXY') && USE_PROXY == 1) {
	
      	$contents = VCDUtils::proxy_url($source);     	
      	
      } else {
      	
      	$fd = fopen($source, "rb");
      	if (!$fd )	{
      		VCDException::display("Cant open file at: ".$image_url."");
      		return false;
	  	}
        $contents = '';
	  	while (!feof($fd)) {
		  	$contents .= fgets ($fd, 1024);
	  	}
		fclose($fd);
      	
      	
      }
      
      

      // get the extension of this image
      ereg( ".*\.(.*)$", $source, $regs );
      $ext = $regs[1];

      if ($uniqueID) {
      	$filename = VCDUtils::generateUniqueId() . "." .$ext;
      } else {
      	ereg( ".*\/(.*)$", $source, $regs );
      	$filename = $regs[1];
      }
      
      $dFolder = $destination;
      
      $dest = $destination . $filename;
      $fd = fopen($dest, "wb");
      
      if ( !$fd ) {
	  	VCDException::display("Cant write file, check permissions for folder " . $destination);
	  	return false;
	  }
      
	  fwrite($fd, $contents);
      fclose($fd);
	  return $filename;
	}
	
	
 /**
  * Enter description here...
  *
  * @return unknown
  */
 static function generateUniqueId(){
    return md5(uniqid(mt_rand(),TRUE));
  }

	
  /**
   * Enter description here...
   *
   * @param unknown $arrItems
   * @param unknown $sepator
   * @return unknown
   */
  static function split($arrItems, $sepator) {
  	if (is_array($arrItems)) {
  		$string = implode($sepator, $arrItems);
  		
  		return $string;
  		
  	} else {
  		return "";
  	}
  }
  
  /*
		If title contains The at the end .. move it forward.
		Very annoying notation.
  */
   /**
    * Enter description here...
    *
    * @param unknown $strTitle
    * @return unknown
    */
   static function titleFormat($strTitle) {
  		$strTitle = trim($strTitle);
  		
  		$rest = substr($strTitle, -5);
  		if (!$rest) {
  			return $strTitle;
  		}
  		
  		// Title ends with ', The'
  		if (strcmp($rest, ", The") == 0) {
  			$strTitle = "The " . substr($strTitle, 0 , (strlen($strTitle)-5));
  			return $strTitle;
  		} else {
  			return $strTitle;
  		}
  }
  

  
	 /**
	  * Enter description here...
	  *
	  * @param unknown $strFilename
	  * @return unknown
	  */
	 static function getFileExtension($strFilename) {
	  	  ereg( ".*\.(.*)$", $strFilename, $regs );
	      return $regs[1];
	  }
  
  
  
	/**
	 * Enter description here...
	 *
	 * @param unknown $filename
	 * @param unknown $content
	 * @return unknown
	 */
	static function write($filename, $content){
			if(!empty($filename) && !empty($content)){
				$fp = fopen($filename,"w");
				$b = fwrite($fp,$content);
				fclose($fp);
				if($b != -1){
					return TRUE;
				} else {
					VCDException::display("Can't write File [no fwrite]");
					return FALSE;
				}
			} else {
				VCDException::display("Cant write File [no filename | no content]");
				return FALSE;
			}
		}

	/**
	 * Enter description here...
	 *
	 * @param vcdObj $obj
	 * @return unknown
	 */
	static function isOwner(vcdObj $obj) {
		if (isset($_SESSION['user']) && $_SESSION['user'] instanceof userObj ) {
			$user = $_SESSION['user'];
			if ($obj->getInstancesByUserID($user->getUserID()) != null && 
				is_array($obj->getInstancesByUserID($user->getUserID()))) {
				return true;
			}
			return false;
			
		} else {
			return false;
		}
	}
		
	// Check if user has access to the "change movie" console
	/**
	 * Enter description here...
	 *
	 * @param vcdObj $obj
	 * @return unknown
	 */
	static function hasPermissionToChange(vcdObj $obj) {
		if (isset($_SESSION['user']) && $_SESSION['user'] instanceof userObj ) {
			$user = $_SESSION['user'];
			if ($user->isAdmin()) {
				return true;
			}
			
			if ($obj->getInstancesByUserID($user->getUserID()) != null && 
				is_array($obj->getInstancesByUserID($user->getUserID()))) {
				return true;
			}
			
			return false;
			
		} else {
			return false;
		}
	}
  
	
	/**
	 * Enter description here...
	 *
	 * @param unknown $proxy_url
	 * @return unknown
	 */
	static function proxy_url($proxy_url) {
	   if (!defined('PROXY_URL') || !defined('PROXY_PORT') || PROXY_URL == '' || PROXY_PORT == '' ) {
	   		VCDException::display('You must define Proxy server and port in VCDConstants.php', true);
	   		return false;
	   }
				
	   $proxy_name = PROXY_URL;
	   $proxy_port = PROXY_PORT;
	   $proxy_cont = '';
		
	   $proxy_fp = fsockopen($proxy_name, $proxy_port);
	   if (!$proxy_fp)  {
	   		VCDException::display('No response from proxy server', true);
	   		return false;
	   }
	   
	   $urlArr = parse_url($proxy_url);
	   $domain = $urlArr['host'];
   
	   //fputs($proxy_fp, "GET $proxy_url HTTP/1.0\r\nHost: $proxy_name\r\n\r\n");
	   fputs($proxy_fp, "GET $proxy_url HTTP/1.0\r\nHost: $domain\r\n\r\n");
	   
	   while(!feof($proxy_fp)) {$proxy_cont .= fread($proxy_fp,4096);}
	   fclose($proxy_fp);
	   $proxy_cont = substr($proxy_cont, strpos($proxy_cont,"\r\n\r\n")+4);
	   return $proxy_cont;
	} 
	
	
	/**
	 * Get list of available CSS templates for VCD-db.
	 * Returns array of strings, containing the unique template names.
	 *
	 * @return array
	 */
	static function getStyleTemplates() {
		
		$templateDirectory = 'includes/templates';
		$it = new DirectoryIterator( $templateDirectory );
		
		$styles = array();
		
		while($it->valid()) {
			$directory = $it->current();
			if ($directory->isDir() && !$directory->isDot()) {
				array_push($styles, $directory->getFilename()); 
			}
			$it->next();
		}
		
		return $styles;
	}
	
  
}


?>