<?php
/**
 * @version:     $Header: /cvsroot/bitweaver/_bit_treasury/plugins/Attic/mime.flash.php,v 1.4 2006/12/22 21:59:22 squareing Exp $
 *
 * @author:      xing  <xing@synapse.plus.com>
 * @version:     $Revision: 1.4 $
 * @created:     Sunday Jul 02, 2006   14:42:13 CEST
 * @package:     treasury
 * @subpackage:  treasury_mime_handler
 **/

global $gTreasurySystem;

// depending on the scan the default file might not be included yet. we need get it manually
require_once( 'mime.default.php' );

// This is the name of the plugin - max char length is 16
// As a naming convention, the treasury mime handler definition should start with:
// TREASURY_MIME_GUID_
define( 'TREASURY_MIME_GUID_FLASH', 'mime_flash' );

$pluginParams = array(
	// simply refer to the default functions - we only want to use a custom view_tpl here
	'verify_function'    => 'treasury_default_verify',
	'store_function'     => 'treasury_default_store',
	'update_function'    => 'treasury_default_update',
	'load_function'      => 'treasury_flash_load',
	'download_function'  => 'treasury_default_download',
	'expunge_function'   => 'treasury_default_expunge',
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

/**
 * Load file data from the database
 * 
 * @param array $pRow 
 * @access public
 * @return TRUE on success, FALSE on failure - ['errors'] will contain reason for failure
 */
function treasury_flash_load( &$pFileHash ) {
	if( $ret = treasury_default_load( &$pFileHash ) ) {
		// override default download_url since we want to point users to the view_tpl
		$pFileHash['download_url'] = TreasuryItem::getDisplayUrl( $pFileHash['content_id'], $pFileHash );
		// this is the true download url we will use in the view_tpl
		$pFileHash['download_swf'] = TreasuryItem::getDownloadUrl( $pFileHash['content_id'] );
	}
	return $ret ;
}
?>
