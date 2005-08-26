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
<? 
// All constants used by VCD-db will be placed here
define("VCDDB_VERSION","0.973");					// VCD-db version
define("STYLE","includes/templates/default/");		// Path to current template
define("TEMP_FOLDER","upload/");					// Temp folder used by VCD-db
define("CACHE_FOLDER","upload/cache/");				// Fetch cache folder
define("THUMBNAIL_PATH","upload/thumbnails/");		// Thumbnail path
define("COVER_PATH","upload/covers/");				// Covers path
define("PORNSTARIMAGE_PATH","upload/pornstars/");	// Pornstar images

// Proxy settings | if using proxy server, define it here below
define("USE_PROXY",  0);				// Change to "1" if using proxy server
define("PROXY_URL",  "");			// Url of your proxy server
define("PROXY_PORT", 8080);			// Proxy port


// RSS Settings
define("RSS_CACHE_TIME",7200);						// 2 hours

// IMDB fetch settings
define("IMDB_MAXRESULT",50);						// Maximum count of results to display from the imdb search

// Database settings
define("DB_TYPE",	"sqlite");
define("DB_USER",	"n/a");
define("DB_PASS",	"n/a");
define("DB_HOST",	"localhost");
define("DB_CATALOG","vcddb.sqlite");

?>