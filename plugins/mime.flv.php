<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_treasury/plugins/Attic/mime.flv.php,v 1.2 2007/02/12 15:37:50 squareing Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.2 $
 * created		Sunday Jul 02, 2006   14:42:13 CEST
 * @package		treasury
 * @subpackage	treasury_mime_handler
 **/

/**
 * setup
 */
global $gTreasurySystem;

/**
 *  This is the name of the plugin - max char length is 16
 * As a naming convention, the treasury mime handler definition should start with:
 * TREASURY_MIME_GUID_
 */
define( 'TREASURY_MIME_GUID_FLV', 'mime_flv' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'    => 'treasury_default_verify',
	'store_function'     => 'treasury_flv_store',
	'update_function'    => 'treasury_flv_update',
	'load_function'      => 'treasury_flv_load',
	'download_function'  => 'treasury_default_download',
	'expunge_function'   => 'treasury_default_expunge',
	// Brief description of what the plugin does
	'title'              => 'Convert Video to Flash Video',
	'description'        => 'This plugin will use ffmpeg to convert any compatible uploaded video to flash video. It will also make the video available for viewing if you have flash installed. Please consult the README on how to use this plugin.',
	// Template used when viewing the item
	'view_tpl'           => 'bitpackage:treasury/view_flv_inc.tpl',
	// This should be the same for all mime plugins
	'plugin_type'        => TREASURY_MIME,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'      => FALSE,
	'processing_options' => '',
	// this should pick up all videos
	'mimetypes'          => array(
		'#video/.*#i',
	),
);
$gTreasurySystem->registerPlugin( TREASURY_MIME_GUID_FLV, $pluginParams );

// depending on the scan the default file might not be included yet. we need to get it manually
require_once( 'mime.default.php' );

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_flv_store( &$pStoreRow, &$pCommonObject ) {
	global $gBitSystem;
	// if storing works, we extract some frameshots
	if( $ret = treasury_default_store( $pStoreRow, $pCommonObject )) {
		if( treasury_flv_add_process( $pStoreRow['content_id'] )) {
			// add an indication that this file is being processed
			touch( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."processing" );
		}
	}
	return $ret;
}

/**
 * treasury_flv_update 
 * 
 * @param array $pStoreRow 
 * @param array $pCommonObject 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function treasury_flv_update( &$pStoreRow, &$pCommonObject ) {
	// if storing works, we extract some frameshots
	if( $ret = treasury_default_update( $pStoreRow, $pCommonObject )) {
		if( treasury_flv_add_process( $pStoreRow['content_id'] )) {
			// add an indication that this file is being processed
			touch( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."processing" );
			// remove any error file since this is a new video file
			@unlink( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."error" );

			if( !empty( $pStoreRow['upload']['tmp_name'] )) {
				// since this user is uploading a new video, we will remove the old flick.flv file
				@unlink( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."flick.flv" );
			}
		}
	}
	return $ret;
}

/**
 * Load file data from the database
 * 
 * @param array $pRow 
 * @access public
 * @return TRUE on success, FALSE on failure - ['errors'] will contain reason for failure
 */
function treasury_flv_load( &$pFileHash ) {
	global $gBitSmarty, $gLibertySystem;
	if( $ret = treasury_default_load( $pFileHash )) {
		// check for status of conversion
		if( is_file( dirname( $pFileHash['source_file'] ).'/error' )) {
			$pFileHash['status']['error'] = TRUE;
		} elseif( is_file( dirname( $pFileHash['source_file'] ).'/processing' )) {
			$pFileHash['status']['processing'] = TRUE;
		} elseif( is_file( dirname( $pFileHash['source_file'] ).'/flick.flv' )) {
			$pFileHash['flv_url'] = dirname( $pFileHash['source_url'] ).'/flick.flv';
			// we need some javascript for the flv player:
			$gBitSmarty->assign( 'treasuryFlv', TRUE );
		}

		// we can use a special plugin if active to include flvs in wiki pages
		if( defined( 'PLUGIN_GUID_DATAFLASHVIDEO' ) && !empty( $gLibertySystem->mPlugins[PLUGIN_GUID_DATAFLASHVIDEO] )) {
			$pFileHash['wiki_plugin_link'] = "{flashvideo id={$pFileHash['attachment_id']}}";
		}
	}
	return $ret ;
}

/**
 * This function will add an entry to the process queue for the cron job to take care of
 * 
 * @param array $pContentId 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function treasury_flv_add_process( $pContentId ) {
	global $gBitSystem;
	$ret = FALSE;
	if( @BitBase::verifyId( $pContentId )) {
		$query = "
			DELETE FROM `".BIT_DB_PREFIX."treasury_process_queue`
			WHERE `content_id`=?";
		$gBitSystem->mDb->query( $query, array( $pContentId ));
		$query = "
			INSERT INTO `".BIT_DB_PREFIX."treasury_process_queue`
			(`content_id`, `queue_date`) VALUES (?,?)";
		$gBitSystem->mDb->query( $query, array( $pContentId, $gBitSystem->getUTCTime() ));
		$ret = TRUE;
	}
	return $ret;
}
?>
