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
 * @package Vcd
 * @version $Id$
 */
 
?>
<? 
	class vcdObj extends cdObj implements XMLable {
	
		/* local variables */
		private $category_id;
		private $source_id;	  // for example IMDB
		private $external_id; // for example IMDB ID
		
		
		private $arrDisc_count = array();
		private $arrDate_added = array();
		
		/* local objects or array of objects */
		private $moviecategoryobj;
		private $coversObjArr = array();
		private $ownersObjArr = array();
		private $mediaTypeObjArr = array();
		
		// IMDB Obj
		private $imdbObj;
		
		// Adult related
		private $arrPornstars = array();
		private $arrPorncategories = array();
		private $studio_id;
		private $screenshots = false;
		
		
		/**
		 * Object constructor
		 *
		 * @param array $dataArr
		 */
		public function __construct($dataArr) {
			$this->id 			 = $dataArr[0];
			$this->title 		 = trim($dataArr[1]);
			$this->category_id   = $dataArr[2];
			$this->year			 = $dataArr[3];
		}
		
				
		/**
		* @return void
		* @param userObj $userObj
		* @param mediaTypeObj $mediaTypeObj
		* @param int $disc_count
		* @param datetime $date_added
		* @desc Add an instance to the CD
		*/
		public function addInstance($userObj, $mediaTypeObj, $disc_count, $date_added) {
			array_push($this->ownersObjArr, $userObj);
			array_push($this->mediaTypeObjArr, $mediaTypeObj);
			array_push($this->arrDisc_count, $disc_count);
			array_push($this->arrDate_added, $date_added);
		}
		
		/**
		 * Enter description here...
		 *
		 * @param int $user_id
		 * @return array
		 */
		public function getInstancesByUserID($user_id) {
			$arrMediaType = array();
			$arrDiscount = array();
			for ($i = 0; $i < sizeof($this->ownersObjArr); $i++) {
				$userObj = $this->ownersObjArr[$i];
				
				if ($userObj->getUserID() == $user_id) { 
					array_push($arrMediaType, $this->mediaTypeObjArr[$i]);
					array_push($arrDiscount, $this->arrDisc_count[$i]);
				}
				
			}
			
			if (sizeof($arrMediaType) == 0) {
				return null;
			} else {
				return array("mediaTypes" => $arrMediaType, "discs" => $arrDiscount);					
			}
			
			
		}
			
			
		
		/**
		 * Get the number of media types available for this movie
		 *
		 * @return int
		 */
		public function getMediaTypeCount() {
			return sizeof($this->mediaTypeObjArr);
		}
		
		/**
		 * Get the category ID
		 *
		 * @return int
		 */
		public function getCategoryID() {
			return $this->category_id;
		}
		
		/**
		 * Get all cover objects associated with this movie
		 *
		 * @return array
		 */
		public function getCovers() {
			return $this->coversObjArr;
		}
		
		/**
		 * Get the number of covers associated with this movie
		 *
		 * @return int
		 */
		public function getCoverCount() {
			if (is_array($this->coversObjArr)) {
				return sizeof($this->coversObjArr);
			} else {
				return 0;
			}
		}
		
		/**
		 * Get a specific cover that is associated with this movie
		 *
		 * @param string $covername
		 * @return cdcoverObj
		 */
		public function getCover($covername) {
			foreach ($this->coversObjArr as $cdcoverObj) {
				
				if ($cdcoverObj instanceof cdcoverObj ) {
				
					if (strcmp(strtolower($cdcoverObj->getCoverTypeName()), strtolower($covername)) == 0) {
						return $cdcoverObj;
					}
				} else {
					VCDException::display('Object in CoversArray is not a cdcoverObj!');
					return false;
				}
			}
			
			return null;
		}
		
		/**
		 * Get the IMDBObj associated with this movie
		 *
		 * @return imdbObj
		 */
		public function getIMDB() {
			return $this->imdbObj;
		}
				
		/**
		 * Get the movieCategoryObj belonging to this movie
		 *
		 * @return movieCategoryObj
		 */
		public function getCategory() {
			if ($this->moviecategoryobj instanceof movieCategoryObj ) {
				return $this->moviecategoryobj;
			}
			return null;
		}
		
		
		/**
		 * Get the number of copies owned by users of the system
		 *
		 * @return int
		 */
		public function getNumCopies() {
			return sizeof($this->ownersObjArr);
		}
		
		/**
		 * Get the number of CD's this movie is on
		 *
		 * @return int
		 */
		public function getDiscCount() {
			if (sizeof($this->arrDisc_count) > 0) {
				return $this->arrDisc_count[0];
			} else {
				return null;
			}
		}
		
		/**
		 * Set the source site obj associated with this movie
		 *
		 * @param int $source_id
		 * @param string $external_id
		 */
		public function setSourceSite($source_id, $external_id) {
			$this->source_id = $source_id;
			$this->external_id = $external_id;
		}
		
		/**
		 * Get the ID of the sourceSiteObj associated with this movie
		 *
		 * @return int
		 */
		public function getSourceSiteID() {
			return $this->source_id;
		}
		
		/**
		 * Get the external ID of this movie, for example the IMDB ID or the DVDEmpire ID
		 *
		 * @return string
		 */
		public function getExternalID() {
			return $this->external_id;
		}
		
		/**
		 * Set the movieCategoryObj associated with this movie
		 *
		 * @param movieCategoryObj $obj
		 */
		public function setMovieCategory(movieCategoryObj $obj) {
			$this->moviecategoryobj = $obj;
		}
		
		/**
		 * Set the IMDBObj associated with this movie
		 *
		 * @param imdbObj $obj
		 */
		public function setIMDB(imdbObj $obj) {
			$this->imdbObj = $obj;
		}
		
		/**
		 * Set the pornstars associated with this movie.
		   param $pornstars can either be a pornstarObj or and
		   array containing pornstars objects.
		 *
		 * @param mixed $pornstars
		 */
		public function addPornstars($pornstars) {
			if ($pornstars instanceof pornstarObj) {
				array_push($this->arrPornstars, $pornstars);
			} else if (is_array($pornstars)) {
				$this->arrPornstars = $pornstars;
			}
		}
		
		/**
		 * Get an array of all pornstars associated with this movie.
		 *
		 * @return array
		 */
		public function getPornstars() {
			return $this->arrPornstars;
		}
		
		/**
		 * Get the porncategoryObj associated with this movie
		 *
		 * @param porncategoryObj $obj
		 */
		public function addAdultCategory(porncategoryObj $obj) {
			array_push($this->arrPorncategories, $obj);
		}
		
		/**
		 * Get all adultcategories associated with this movie
		 *
		 * @return array
		 */
		public function getAdultCategories() {
			return $this->arrPorncategories;
		}
		
		/**
		 * Add cdcover objects to this movie.
		 *
		 * @param array $coverArr
		 */
		public function addCovers($coverArr) {
			if (sizeof($this->coversObjArr) > 0) {
				$this->coversObjArr = array_merge($this->coversObjArr, $coverArr);
			} else {
				$this->coversObjArr = $coverArr;
			}
			
		}
		
		/**
		 * Get the mediaTypeObj associated with this movie
		 *
		 * @return array
		 */
		public function getMediaType() {
			if (sizeof($this->mediaTypeObjArr) > 0) {
				return $this->mediaTypeObjArr;
			} else {
				return null;
			}
		}
		
		/**
		 * Add mediaTypeObj to this movie
		 *
		 * @param mediaTypeObj $obj
		 */
		public function addMediaType(mediaTypeObj $obj) {
			array_push($this->mediaTypeObjArr, $obj);
		}
		
		/**
		 * Check if movie is an adult feature or not.
		 *
		 * @return bool
		 */
		public function isAdult() {
			if ($this->moviecategoryobj instanceof movieCategoryObj ) {
				return strcmp(strtolower($this->moviecategoryobj->getName()), "adult") == 0;
			}
			return false;
		}
		
		/**
		 * Set the adult studio ID of the movie
		 *
		 * @param int $sid
		 */
		public function setStudioID($sid) {
			$this->studio_id = $sid;
		}
		
		/**
		 * Get the adult studio ID of the movie
		 *
		 * @return int
		 */
		public function getStudioID() {
			return $this->studio_id;
		}
		
		
		/**
		 * Flag that this movie has screenshots available.
		 *
		 */
		public function setScreenshots() {
			$this->screenshots = true;
		}
		
		/**
		 * Check if this movie has screenshots.
		 *
		 * @return bool
		 */
		public function hasScreenshots() {
			return $this->screenshots;
		}
		
		/**
		 * Set the number of discs to the current movie instance.
		 *
		 * @param int $disccount
		 */
		public function setDiscCount($disccount) {
			array_push($this->arrDisc_count, $disccount);
		}
		
		/**
		 * Set the date of the movie submission to the database.
		 *
		 * @param datetime $date
		 */
		public function setDateAdded($date) {
			array_push($this->arrDate_added, $date);
		}
		
		/**
		 * Get a string containing all available mediatypes for this movie.
		 *
		 * @return string
		 */
		public function showMediaTypes() {
			
			$strMediaTypes = "";
			$i = 1;
			
			foreach ($this->mediaTypeObjArr as $mediaObj) {
				$strMediaTypes .= $mediaObj->getDetailedName();
				if ($i < $this->getMediaTypeCount()) {
					$strMediaTypes .= ", ";
				}
				$i++;
			}
			
			return $strMediaTypes;
		}

		/**
		 * Print out in a table all user copies of this movie.
		 *
		 */
		public function displayCopies() {
			
			global $language;
					
			print "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\">";
			print "<tr><td>".$language->show('M_MEDIA')."</td><td>".$language->show('M_NUM')."</td><td>".$language->show('M_DATE')."</td><td>".$language->show('M_OWNER')."</td></tr>";
			for ($i = 0; $i < sizeof($this->ownersObjArr); $i++) {
				$owner = $this->ownersObjArr[$i];
				$media = $this->mediaTypeObjArr[$i];
				print "<tr>";
				print "<td>".$media->getDetailedName()."</td>";
				print "<td>".$this->arrDisc_count[$i]."</td>";
				print "<td>".date("Y-d-m", $this->arrDate_added[$i])."</td>";
				print "<td>".$owner->getUsername()."</td>";
				print "</tr>";

				
			}
			print "</table>";
		}
		
				
		/**
		 * Get the RSS data for this movie.
		 *
		 * @return string
		 */
		public function getRSSData() {
			
			$link = "";
			$user = "";
			$date = "";
			
			if (isset($this->external_id)) {
				global $ClassFactory;
				$SettingsClass = $ClassFactory->getInstance('vcd_settings');
				$sObj = $SettingsClass->getSourceSiteByID($this->source_id);
				if ($sObj->getsiteID() == 1) {
					// Add "tt" for IMDB id's
					$external_link = str_replace('#', "tt".$this->external_id, $sObj->getCommand());
				} else {
					$external_link = str_replace('#', $this->external_id, $sObj->getCommand());
				}
				
				$link = $external_link;
			}
			
			$currOwner = $this->ownersObjArr[0];
			if ($currOwner instanceof userObj ) {
				$user = $currOwner->getUsername();
			}
			$date = $this->arrDate_added[0];
			
			return array('description' => $link, 'creator' => $user, 'date' => $date);
		}
		
		/**
		 * Get the XML representation of the object.
		 *
		 * @return string
		 */
		public function toXML() {
			
			$xmlstr  = "<movie>\n";
			$xmlstr .= "<id>".$this->id."</id>\n";
			$xmlstr .= "<title><![CDATA[".$this->title."]]></title>\n";
			$xmlstr .= "<category>".$this->moviecategoryobj->getName()."</category>\n";
			$xmlstr .= "<category_id>".$this->moviecategoryobj->getID()."</category_id>\n";
			$xmlstr .= "<year>".$this->year."</year>\n";
			$xmlstr .= "<cds>".$this->arrDisc_count[0]."</cds>\n";
			
			$mediaTypeObj = $this->mediaTypeObjArr[0];
			$xmlstr .= "<mediatype>".$mediaTypeObj->getDetailedName()."</mediatype>\n";
			$xmlstr .= "<mediatype_id>".$mediaTypeObj->getmediaTypeID()."</mediatype_id>\n";
			$xmlstr .= "<dateadded>".$this->arrDate_added[0]."</dateadded>\n";
			$xmlstr .= "<sourcesite_id>".$this->source_id."</sourcesite_id>\n";
			$xmlstr .= "<external_id>".$this->external_id."</external_id>\n";
			if ($this->imdbObj instanceof imdbObj) {
				$xmlstr .= $this->imdbObj->toXML();
			}
			
			if ($this->isAdult() && is_numeric($this->studio_id)) {
				global $ClassFactory;
				$PORNClass = $ClassFactory->getInstance('vcd_pornstar');
				$studioObj = $PORNClass->getStudioByID($this->studio_id);
				if ($studioObj instanceof studioObj ) {
					$xmlstr .= $studioObj->toXML();
				}
			}
			
			if ($this->isAdult() && sizeof($this->arrPorncategories) > 0) {
				$xmlstr .= "<adult_category>\n";
				foreach ($this->arrPorncategories as $pornCatObj) {
					$xmlstr .= $pornCatObj->toXML();
				}
				$xmlstr .= "</adult_category>\n";
			}
			
			if (sizeof($this->arrPornstars) > 0) {
				$xmlstr .= "<pornstars>\n";
				foreach ($this->arrPornstars as $starObj) {
					$xmlstr .= $starObj->toXML();
				}
				$xmlstr .= "</pornstars>\n";
			}
			
			
			$xmlstr .= "</movie>\n";
			
			return $xmlstr;
		
		}
		
		
		/* One time only needed function for insertion of a new vcdObj */
		/**
		 * Get the userid of the owner of this movie's instance
		 *
		 * @return int
		 */
		public function getInsertValueUserID() {
			if (sizeof($this->ownersObjArr) == 1) {
				return $this->ownersObjArr[0]->getUserID();
			} else {
				VCDException::display('No user has been added to CD<break>Cannot continue');
				return false;
			}
		}
		
		/**
		 * Get the mediatypeID for this intance.
		 *
		 * @return int
		 */
		public function getInsertValueMediaTypeID() {
			if (sizeof($this->mediaTypeObjArr) == 1) {
				return $this->mediaTypeObjArr[0]->getmediaTypeID();
			} else {
				VCDException::display('No media type has been added to CD<break>Cannot continue');
				return false;
			}
		}
		
		/**
		 * Get the disccount for this instance.
		 *
		 * @return int
		 */
		public function getInsertValueDiscCount() {
			if (sizeof($this->arrDisc_count) == 1) {
				return $this->arrDisc_count[0];
			} else {
				VCDException::display('No disc count has been added to CD<break>Cannot continue');
				return false;
			}
		}
		
				
		
	}


?>