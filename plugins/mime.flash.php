<?php
/**
 * @version:     $Header: /cvsroot/bitweaver/_bit_treasury/plugins/Attic/mime.flash.php,v 1.2 2006/12/06 18:45:17 squareing Exp $
 *
 * @author:      xing  <xing@synapse.plus.com>
 * @version:     $Revision: 1.2 $
 * @created:     Sunday Jul 02, 2006   14:42:13 CEST
 * @package:     treasury
 * @subpackage:  treasury_mime_handler
 **/

global $gTreasurySystem;

// This is the name of the plugin - max char length is 16
// As a naming convention, the treasury mime handler definition should start with:
// TREASURY_MIME_GUID_
define( 'TREASURY_MIME_GUID_FLASH', 'mime_flash' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'    => 'treasury_flash_verify',
	'store_function'     => 'treasury_flash_store',
	'update_function'    => 'treasury_flash_update',
	'load_function'      => 'treasury_flash_load',
	'download_function'  => 'treasury_flash_download',
	'expunge_function'   => 'treasury_flash_expunge',
	// Brief description of what the plugin does
	'title'              => 'Macromedia Flash File Handler',
	'description'        => 'Allow upload and viewing of flash files.',
	// Template used when viewing the item
	'view_tpl'           => 'bitpackage:treasury/view_flash_inc.tpl',
	// This should be the same for all mime plugins
	'plugin_type'        => TREASURY_MIME,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'      => FALSE,
	'processing_options' => '',
	// this should pick up all videos
	'mimetypes'          => array(
		'#application/x-shockwave-flash#i',
	),
);

$gTreasurySystem->registerPlugin( TREASURY_MIME_GUID_FLASH, $pluginParams );

// depending on the scan the default file might not be included yet. we need get it manually
require_once( 'mime.default.php' );

/**
 * Sanitise and validate data before it's stored
 * 
 * @param array $pStoreRow Hash of data that needs to be stored
 * @param array $pStoreRow['upload'] Hash passed in by $_FILES upload
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_flash_verify( &$pStoreRow ) {
	$ret = treasury_default_verify( $pStoreRow );
	return $ret;
}

/**
 * When a file is edited
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_flash_update( &$pStoreRow ) {
	$ret = treasury_default_update( $pStoreRow );
	return $ret;
}

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_flash_store( &$pStoreRow ) {
	$ret = treasury_default_store( $pStoreRow );
	return $ret;
}

/**
 * Load file data from the database
 * 
 * @param array $pRow 
 * @access public
 * @return TRUE on success, FALSE on failure - ['errors'] will contain reason for failure
 */
function treasury_flash_load( &$pFileHash ) {
	$ret = treasury_default_load( $pFileHash );
	return $ret ;
}

/**
 * Takes care of the entire download process. Make sure it doesn't die at the end.
 * in this functioin it would be possible to add download resume possibilites and the like
 * 
 * @param array $pFileHash Basically the same has as returned by the load function
 * @access public
 * @return TRUE on success, FALSE on failure - $pParamHash['errors'] will contain reason for failure
 */
function treasury_flash_download( &$pFileHash ) {
	$ret = treasury_default_download( $pFileHash );
	return $ret;
}
?>
