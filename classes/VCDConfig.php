<?php
/**
 * VCD-db - a web based VCD/DVD Catalog system
 * Copyright (C) 2003-2007 Konni - konni.com
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * @author  Hákon Birgisson <konni@konni.com>
 * @package Kernel
 * @version $Id: VCDConfig.php 1062 2007-07-05 15:10:11Z konni $
 * @since  0.990
  */
?>
<?php
/**
 * A wrapper class for the config.php configuration file.
 *
 */
final class VCDConfig {

	/**
	 * Get the database type VCD-db is using
	 *
	 * @return string
	 */
	public static final function getDatabaseType() {
		if (!defined('DB_TYPE') || DB_TYPE=='') {
			return null;
		}
		return DB_TYPE;
	}
	
	/**
	 * Get the username to connect to the VCD-db database
	 *
	 * @return string
	 */
	public static final function getDatabaseUser() {
		if (!defined('DB_USER') || DB_USER=='') {
			return null;
		}
		return DB_USER;
	}
	
	/**
	 * Get the password to connect to the VCD-db database
	 *
	 * @return string
	 */
	public static final function getDatabasePassword() {
		if (!defined('DB_PASS')) {
			return null;
		}
		return DB_PASS;
	}
	
	/**
	 * Get the database host
	 *
	 * @return string
	 */
	public static final function getDatabaseHost() {
		if (!defined('DB_HOST') || DB_HOST=='') {
			return null;
		}
		return DB_HOST;
	}
	
	/**
	 * Get the name of the database VCD-db connects to
	 *
	 * @return string
	 */
	public static final function getDatabaseName() {
		if (!defined('DB_CATALOG') || DB_CATALOG=='') {
			return null;
		}
		return DB_CATALOG;
	}
	
	
	/**
	 * Check if VCD-db is connect to webservice proxy or database
	 *
	 * @return bool
	 */
	public static final function isUsingWebservice() {
		if (defined('VCDDB_USEPROXY') && (int)VCDDB_USEPROXY == 1) {
			return true;
		}
		return false;
	}
	
	/**
	 * Get the Webservice Endpoint that VCD-db should connect to if isUsingWebservice()
	 * returns true.
	 *
	 * @return string
	 */
	public static function getWebserviceEndpoint() {
		if (!defined('VCDDB_SOAPPROXY') || VCDDB_SOAPPROXY=='') {
			return null;
		}
		return (string)VCDDB_SOAPPROXY;
	}

	/**
	 * Get the password used to connect to the remote VCD-db instance via SOAP.
	 * This password is used when requests are being sent on behalf of unauthenticated
	 * users.  The SOAP password cannot be empty.
	 *
	 * @return string
	 */
	public static function getWebservicePassword() {
		if (!defined('VCDDB_SOAPSECRET') || VCDDB_SOAPSECRET=='') {
			return null;
		}
		return (string)VCDDB_SOAPSECRET;
	}
	
	/**
	 * Get the Cache Manager used to store cachable data from the remote webservice.
	 * Define-ing a cache manager is not required.
	 *
	 * @return string
	 */
	public static function getWebserviceCacheManager() {
		if (!defined('CACHE_MANAGER') || CACHE_MANAGER=='') {
			return null;
		}
		return (string)CACHE_MANAGER;
	}
	
	/**
	 * Get the web base directory where VCD-db lies.  Possible output could be '/' for root directory
	 * or '/webs/vcddb/' if VCD-db resides in webfolder webs/vcddb
	 *
	 * @return string
	 */
	public static function getWebBaseDir() {
		$base = dirname($_SERVER['PHP_SELF']);
		if (self::endsWith('/',$base)) {
			return $base;
		} else {
			return $base.'/';
		}
	}
	
	
	/**
	 * Check if VCD-db should use LDAP for authentication or not.
	 *
	 * @return bool
	 */
	public static function isLDAPAuthentication() {
		if (defined('LDAP_AUTH') && (int)LDAP_AUTH == 1) {
			return true;
		}
		return false;
	}
	
	/**
	 * Get the LDAP Directory host name
	 *
	 * @return string
	 */
	public static function getLDAPHost() {
		if (defined('LDAP_HOST')) {
			return (string)LDAP_HOST;
		}
		return null;
	}
	
	/**
	 * Get the LDAP Directory Base DN
	 *
	 * @return string
	 */
	public static function getLDAPBaseDN() {
		if (defined('LDAP_BASEDN')) {
			return (string)LDAP_BASEDN;
		}
		return null;
	}
	
	/**
	 * Check if the LDAP Directory server is Microsoft Active Directory or not
	 *
	 * @return bool
	 */
	public static function isLDAPActiveDirectory() {
		if (defined('LDAP_AD') && (int)LDAP_AD == 1) {
			return true;
		}
		return false;
	}
	
	/**
	 * Get the active Directory Domain name to use if isLDAPActiveDirectory()
	 * returns true.
	 *
	 * @return string
	 */
	public static function getLDAPActiveDirectoryDomain() {
		if (defined('AD_DOMAIN')) {
			return (string)AD_DOMAIN;
		}
		return null;
	}
	
	/**
	 * Check if the fetch classes should connect through a proxy server.
	 *
	 * @return bool
	 */
	public static function isUsingProxyServer() {
		if (defined('USE_PROXY') && (int)USE_PROXY == 1) {
			return true;
		}
		return false;
	}
	
	/**
	 * Get the host name of the proxy server to connect to.
	 *
	 * @return string
	 */
	public static function getProxyServerHostname() {
		if (defined('PROXY_URL')) {
			return (string)PROXY_URL;
		}
		return null;
	}
	
	/**
	 * Get the Port number to use when connecting to the proxy server.
	 *
	 * @return int
	 */
	public static function getProxyServerPort() {
		if (defined('PROXY_PORT')) {
			return (string)PROXY_PORT;
		}
		return null;
	}
	
	/**
	 * Get the default CSS Template folder VCD-db should use for UI rendering.
	 * If none has been defined, the default VCD-db template will be returned. 
	 *
	 * @return string
	 */
	public static function getDefaultStyleTemplate() {
		if (defined('STYLE') && STYLE != '') {
			return (string)STYLE;
		} else {
			return 'includes/templates/default/';
		}
	}
	
	/**
	 * Get the RSS cache timeout values, if cached item is older that the value
	 * a fresh copy of the RSS feed will be downloaded.  The value returned
	 * is in seconds.  If value has not been defined, defualt 4 hours will be returned. 
	 *
	 * @return double
	 */
	public static function getRSSCacheTimeout() {
		if (defined('RSS_CACHE_TIME') && is_numeric(RSS_CACHE_TIME)) {
			return (double)RSS_CACHE_TIME;
		} else {
			return (60*60*4);	// 4 hours
		}
	}
	
	/**
	 * Check if specified string ends with certain character
	 *
	 * @param string $str | The needle
	 * @param string $sub | The haystack
	 * @return bool
	 */
	private static function endsWith($str, $sub) {
   		return (substr($str, strlen($str) - strlen($sub)) === $sub );
	}
	
	/**
	 * Check if specified string begins with certain character
	 *
	 * @param string $str | The needle
	 * @param string $sub | The haustack
	 * @return bool
	 */
	private static function beginsWith($str, $sub) {
   		return (substr($str, 0, strlen($sub)) === $sub);
	}
	
}
?>