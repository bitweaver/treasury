<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_treasury/liberty_plugins/mime.import.php,v 1.3 2008/07/17 08:17:52 squareing Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.3 $
 * created		Thursday May 08, 2008
 * @package		liberty
 * @subpackage	liberty_mime_handler
 **/

/**
 * setup
 */
global $gLibertySystem;

/**
 *  This is the name of the plugin - max char length is 16
 * As a naming convention, the treasury mime handler definition should start with:
 * PLUGIN_MIME_GUID_
 */
define( 'PLUGIN_MIME_GUID_IMPORT', 'mimeimport' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'upload_function'     => 'mime_import_upload',
	'verify_function'     => 'mime_import_verify',
	'store_function'      => 'mime_import_store',
	// Brief description of what the plugin does
	'title'               => 'Import a an uploaded file',
	'description'         => 'This plugin allows you to import a file that has been uploaded to a location outside bitweaver. This might be useful if you upload files using FTP or SSH.',
	// Templates to display the files
	'upload_tpl'          => 'bitpackage:treasury/mime_import_upload_inc.tpl',
	// url to page with options for this plugin - don't use package constants here to avoid undefined constants error
	// the package name will be interpreted and converted to <package>_PKG_URL/...
	'plugin_settings_url' => 'treasury/admin/mime_import.php',
	// This should be the same for all mime plugins
	'plugin_type'         => MIME_PLUGIN,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'       => FALSE,
	// Help page on bitweaver.org
	//'help_page'           => 'LibertyMime+PBase+Plugin',
);
$gLibertySystem->registerPlugin( PLUGIN_MIME_GUID_IMPORT, $pluginParams );

/**
 * mime_import_upload 
 * 
 * @access public
 * @return void
 */
function mime_import_upload() {
	require_once( KERNEL_PKG_PATH.'ajax_file_browser_inc.php' );
}

/**
 * Sanitise and validate data before it's stored - this will also generate all 
 * required for the default verify function to be happy.
 * 
 * @param array $pStoreRow Hash of data that needs to be stored
 * @param array $pStoreRow['upload'] Hash passed in by $_FILES upload
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function mime_import_verify( &$pStoreRow ) {
	global $gBitSystem;
	$ret = FALSE;
	if( !empty( $pStoreRow['import_path'] ) && strstr( "../", $pStoreRow['import_path'] ) === FALSE ) {
		// don't allow sneaky shits to import stuff outside our specified jail
		if( $jail = $gBitSystem->getConfig( 'mime_import_file_import_path' )) {
			$file = realpath( $jail.$pStoreRow['import_path'] );
			if( strpos( $file, $jail ) !== FALSE && is_file( $file )) {
				// this will copy a file instead of move it
				$pStoreRow['upload']['copy_file'] = TRUE;
				// generate upload hash from the data we have
				$pStoreRow['upload']['tmp_name'] = $file;
				$pStoreRow['upload']['name'] = basename( $file );
				$pStoreRow['upload']['size'] = filesize( $file );
				$pStoreRow['upload']['error'] = 0;
				$pStoreRow['upload']['type'] = $gBitSystem->verifyMimeType( $file );
				if( $pStoreRow['upload']['type'] == 'application/binary' || $pStoreRow['upload']['type'] == 'application/octet-stream' || $pStoreRow['upload']['type'] == 'application/octetstream' ) {
					$pStoreRow['upload']['type'] = $gBitSystem->lookupMimeType( basename( $file ));
				}
				$ret = TRUE;
			} else {
				$pStoreRow['errors']['import'] = "The specified file could not be located.";
			}
		}
	} else {
		$pStoreRow['errors']['import'] = "The specified file could not be located.";
	}
	return $ret;
}

/**
 * Store the data in the database - this function will hand off the file to the 
 * correct plugin and use that to store the data. The import plugin will not be 
 * called again by this file.
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function mime_import_store( &$pStoreRow ) {
	global $gLibertySystem;
	$libertyMime = new LibertyMime();
	// let the correct plugin do the rest - this plugin should not be called again for this file
	$guid = $gLibertySystem->lookupMimeHandler( $pStoreRow['upload'] );
	if( $libertyMime->pluginStore( $pStoreRow, $guid, @BitBase::verifyId( $upload['attachment_id'] ))) {
		return TRUE;
	} else {
		$pStoreRow['errors'] = $libertyMime->mErrors;
	}
}
?>
