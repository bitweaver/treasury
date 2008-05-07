<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_treasury/plugins/Attic/mime.flash.php,v 1.11 2008/05/07 19:36:19 wjames5 Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.11 $
 * created		Sunday Jul 02, 2006   14:42:13 CEST
 * @package		treasury
 * @subpackage	treasury_mime_handler
 **/

/**
 * setup
 */
global $gTreasurySystem;

/**
 * depending on the scan the default file might not be included yet. we need get it manually
 */ 
require_once( 'mime.default.php' );

/**
 * This is the name of the plugin - max char length is 16
 * As a naming convention, the treasury mime handler definition should start with:
 * TREASURY_MIME_GUID_
 */
define( 'TREASURY_MIME_GUID_FLASH', 'mime_flash' );

$pluginParams = array(
	// simply refer to the default functions - we only want to use a custom view_tpl here
	'verify_function'    => 'treasury_default_verify',
	'store_function'     => 'treasury_flash_store',
	'update_function'    => 'treasury_flash_update',
	'load_function'      => 'treasury_flash_load',
	'download_function'  => 'treasury_default_download',
	'expunge_function'   => 'treasury_default_expunge',
	// Brief description of what the plugin does
	'title'              => 'Macromedia Flash',
	'description'        => 'Allow upload and viewing of flash files.',
	// Template used when viewing the item
	'view_tpl'           => 'bitpackage:treasury/view_flash_inc.tpl',
	// This should be the same for all mime plugins
	'plugin_type'        => TREASURY_MIME,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'      => FALSE,
	// Allow for additional processing options - passed in during verify and store
	'processing_options' =>
		'<label>'.tra( "Width" ).': <input type="text" size="5" name="plugin[swf_width]" />px </label><br />'.
		'<label>'.tra( "Height" ).': <input type="text" size="5" name="plugin[swf_height]" />px </label><br />'.
		tra( 'If this is a flash file please insert the width and hight.' ),
	// this should pick up all videos
	'mimetypes'          => array(
		'#application/x-shockwave-flash#i',
	),
);

$gTreasurySystem->registerPlugin( TREASURY_MIME_GUID_FLASH, $pluginParams );

/**
 * Update file settings - taken over by treasury_default_store appart from the width and height settings
 * 
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_flash_update( &$pStoreRow, &$pCommonObject ) {
	global $gBitSystem;
	if( $ret = treasury_default_update( $pStoreRow, $pCommonObject ) ) {
		if( @BitBase::verifyId( $pStoreRow['plugin']['swf_width'] )) {
			$pCommonObject->storePreference( 'swf_width', $pStoreRow['plugin']['swf_width'] );
		}

		if( @BitBase::verifyId( $pStoreRow['plugin']['swf_height'] )) {
			$pCommonObject->storePreference( 'swf_height', $pStoreRow['plugin']['swf_height'] );
		}
	}
	return $ret;
}

/**
 * Store file settings - taken over by treasury_default_store appart from the width and height settings
 * 
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_flash_store( &$pStoreRow, &$pCommonObject ) {
	global $gBitSystem;
	if( $ret = treasury_default_store( $pStoreRow, $pCommonObject ) ) {
		list( $width, $height, $type, $attr ) =  getimagesize( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path'].$pStoreRow['upload']['name'] );
		if( @BitBase::verifyId( $width )) {
			$pCommonObject->storePreference( 'swf_width', $width );
		}

		if( @BitBase::verifyId( $height )) {
			$pCommonObject->storePreference( 'swf_height', $height );
		}
	}
	return $ret;
}

/**
 * Load file data from the database
 * 
 * @param array $pFileHash contains all file information
 * @param array $pCommonObject is the full object loaded. only set when we are actually loading the object, not just listing items
 * @access public
 * @return TRUE on success, FALSE on failure - ['errors'] will contain reason for failure
 */
function treasury_flash_load( &$pFileHash, &$pCommonObject ) {
	if( $ret = treasury_default_load( $pFileHash, $pCommonObject ) ) {
		// override default download_url since we want to point users to the view_tpl
		$pFileHash['download_url'] = TreasuryItem::getDisplayUrl( $pFileHash['content_id'], $pFileHash );
		// this is the true download url we will use in the view_tpl
		$pFileHash['download_swf'] = TreasuryItem::getDownloadUrl( $pFileHash['content_id'] );
	}
	return $ret ;
}
?>
