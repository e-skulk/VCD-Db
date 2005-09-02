CREATE TABLE vcd ( vcd_id INTEGER NOT NULL, title VARCHAR(250), category_id INTEGER NOT NULL, year INTEGER, PRIMARY KEY (vcd_id) )
CREATE TABLE vcd_Borrowers ( borrower_id INTEGER NOT NULL, owner_id INTEGER NOT NULL, name VARCHAR(100) NOT NULL, email VARCHAR(50) NOT NULL, PRIMARY KEY (borrower_id) )
CREATE TABLE vcd_Comments ( comment_id INTEGER NOT NULL, vcd_id INTEGER NOT NULL, user_id INTEGER NOT NULL, comment_date DATE NOT NULL, comment VARCHAR(250), isPrivate DECIMAL(1) NOT NULL, PRIMARY KEY (comment_id) )
CREATE TABLE vcd_CoverTypes ( cover_type_id INTEGER NOT NULL, cover_type_name VARCHAR(50) NOT NULL, cover_type_description VARCHAR(100), PRIMARY KEY (cover_type_id) )
CREATE TABLE vcd_Covers ( cover_id INTEGER NOT NULL, vcd_id INTEGER NOT NULL, cover_type_id INTEGER NOT NULL, cover_filename VARCHAR(100) NOT NULL, cover_filesize INTEGER, user_id INTEGER NOT NULL, date_added DATE NOT NULL, image_id INTEGER, PRIMARY KEY (cover_id) )
CREATE TABLE vcd_CoversAllowedOnMediatypes ( cover_type_id INTEGER NOT NULL, media_type_id INTEGER NOT NULL )
CREATE TABLE vcd_IMDB ( imdb VARCHAR(32) NOT NULL, title VARCHAR(250), alt_title1 VARCHAR(250), alt_title2 VARCHAR(250), image VARCHAR(100), year INTEGER, plot VARCHAR(250), director VARCHAR(100), fcast VARCHAR(250), rating VARCHAR(5), runtime INTEGER, country VARCHAR(80), genre VARCHAR(100), PRIMARY KEY (imdb) )
CREATE TABLE vcd_Images ( image_id INTEGER NOT NULL, name VARCHAR(80) NOT NULL, image_type VARCHAR(12) NOT NULL, image_size INTEGER NOT NULL, image VARCHAR, PRIMARY KEY (image_id) )
CREATE TABLE vcd_MediaTypes ( media_type_id INTEGER NOT NULL, media_type_name VARCHAR(50) NOT NULL, parent_id INTEGER, media_type_description VARCHAR(100), PRIMARY KEY (media_type_id) )
CREATE TABLE vcd_MetaData ( metadata_id INTEGER NOT NULL, record_id INTEGER NOT NULL, user_id INTEGER NOT NULL, metadata_name VARCHAR(50) NOT NULL, metadata_value VARCHAR(150) NOT NULL, PRIMARY KEY (metadata_id) )
CREATE TABLE vcd_MovieCategories ( category_id INTEGER NOT NULL, category_name VARCHAR(100) NOT NULL, PRIMARY KEY (category_id) )
CREATE TABLE vcd_PornCategories ( category_id INTEGER NOT NULL, category_name VARCHAR(50) NOT NULL, PRIMARY KEY (category_id) )
CREATE TABLE vcd_PornStudios ( studio_id INTEGER NOT NULL, studio_name VARCHAR(250), PRIMARY KEY (studio_id) )
CREATE TABLE vcd_Pornstars ( pornstar_id INTEGER NOT NULL, name VARCHAR(250), homepage VARCHAR(100), image_name VARCHAR(100), biography VARCHAR(250), PRIMARY KEY (pornstar_id) )
CREATE TABLE vcd_PropertiesToUser ( user_id INTEGER NOT NULL, property_id INTEGER NOT NULL )
CREATE TABLE vcd_RssFeeds ( feed_id INTEGER NOT NULL, user_id INTEGER NOT NULL, feed_name VARCHAR(60) NOT NULL, feed_url VARCHAR(150) NOT NULL, PRIMARY KEY (feed_id) )
CREATE TABLE vcd_Screenshots ( vcd_id INTEGER NOT NULL, PRIMARY KEY (vcd_id) )
CREATE TABLE vcd_Sessions ( session_id VARCHAR(50) NOT NULL, session_user_id INTEGER NOT NULL, session_start INTEGER NOT NULL, session_ip VARCHAR(15) NOT NULL )
CREATE TABLE vcd_Settings ( settings_id INTEGER NOT NULL, settings_key VARCHAR(50) NOT NULL, settings_value VARCHAR(250), settings_description VARCHAR(250), isProtected DECIMAL(1), settings_type VARCHAR(20), PRIMARY KEY (settings_id) )
CREATE TABLE vcd_SourceSites ( site_id INTEGER NOT NULL, site_name VARCHAR(50) NOT NULL, site_alias VARCHAR(50), site_homepage VARCHAR(80) NOT NULL, site_getCommand VARCHAR(100), site_isFetchable DECIMAL(1) NOT NULL, PRIMARY KEY (site_id) )
CREATE TABLE vcd_UserLoans ( loan_id INTEGER NOT NULL, vcd_id INTEGER NOT NULL, owner_id INTEGER NOT NULL, borrower_id INTEGER NOT NULL, date_out DATE NOT NULL, date_in DATE, PRIMARY KEY (loan_id) )
CREATE TABLE vcd_UserProperties ( property_id INTEGER NOT NULL, property_name VARCHAR(50) NOT NULL, property_description VARCHAR(80), PRIMARY KEY (property_id) )
CREATE TABLE vcd_UserRoles ( role_id INTEGER NOT NULL, role_name VARCHAR(50) NOT NULL, role_description VARCHAR(100), PRIMARY KEY (role_id) )
CREATE TABLE vcd_UserWishList ( user_id INTEGER NOT NULL, vcd_id INTEGER NOT NULL )
CREATE TABLE vcd_Users ( user_id INTEGER NOT NULL, user_name VARCHAR(80) NOT NULL, user_password VARCHAR(32) NOT NULL, user_fullname VARCHAR(100) NOT NULL, user_email VARCHAR(80) NOT NULL, role_id INTEGER NOT NULL, is_deleted DECIMAL(1) NOT NULL, date_created DATE NOT NULL, PRIMARY KEY (user_id) )
CREATE TABLE vcd_VcdToPornCategories ( vcd_id INTEGER NOT NULL, category_id INTEGER NOT NULL )
CREATE TABLE vcd_VcdToPornStudios ( vcd_id INTEGER NOT NULL, studio_id INTEGER NOT NULL )
CREATE TABLE vcd_VcdToPornstars ( vcd_id INTEGER NOT NULL, pornstar_id INTEGER NOT NULL )
CREATE TABLE vcd_VcdToSources ( vcd_id INTEGER NOT NULL, site_id INTEGER NOT NULL, external_id VARCHAR(32) NOT NULL )
CREATE TABLE vcd_VcdToUsers ( vcd_id INTEGER NOT NULL, user_id INTEGER NOT NULL, media_type_id INTEGER NOT NULL, disc_count INTEGER NOT NULL, date_added DATE NOT NULL )