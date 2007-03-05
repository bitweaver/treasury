<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_treasury/plugins/Attic/mime.video.php,v 1.6 2007/03/05 11:56:26 squareing Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.6 $
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
define( 'TREASURY_MIME_GUID_VIDEO', 'mime_video' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'    => 'treasury_default_verify',
	'store_function'     => 'treasury_video_store',
	'update_function'    => 'treasury_default_update',
	'load_function'      => 'treasury_video_load',
	'download_function'  => 'treasury_default_download',
	'expunge_function'   => 'treasury_default_expunge',
	// Brief description of what the plugin does
	'title'              => 'Video Mime Handler',
	'description'        => 'This plugin will extract a few images from the uploaded video and display them as preview. This plugin requires mplayer to be installed on the server ( does not work with all video types ).',
	// Template used when viewing the item
	'view_tpl'           => 'bitpackage:treasury/view_video_inc.tpl',
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
$gTreasurySystem->registerPlugin( TREASURY_MIME_GUID_VIDEO, $pluginParams );

// depending on the scan the default file might not be included yet. we need get it manually
require_once( 'mime.default.php' );

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_video_store( &$pStoreRow, &$pCommonObject ) {
	// if storing works, we extract some frameshots
	if( $ret = treasury_default_store( $pStoreRow, $pCommonObject ) ) {
		treasury_video_extract_frameshots( $pStoreRow );
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
function treasury_video_load( &$pFileHash, &$pCommonObject ) {
	if( $ret = treasury_default_load( $pFileHash, $pCommonObject ) ) {
		// get extra stuff such as screenshots and icons
		if( $fshots = treasury_video_get_frameshots( dirname( $pFileHash['source_file'] ) ) ) {
			for( $i = 0; $i < count( $fshots ); $i++ ) {
				$pFileHash['frameshots'][] = dirname( $pFileHash['source_url'] ).'/'.basename( $fshots[$i] );
			}
		}
	}
	return $ret ;
}


// ================= additional functions
/**
 * Extract frameshots from video if possible
 * 
 * @param array $pStoreRow
 * @access public
 * @return set of frameshots if successful
 */
function treasury_video_extract_frameshots( $pStoreRow ) {
	$destDir = BIT_ROOT_PATH.$pStoreRow['upload']['dest_path'];
	$video   = $destDir.$pStoreRow['upload']['name'];
	$output  = shell_exec( "mplayer -vf screenshot -framedrop -vo jpeg:quality=50:outdir=$destDir -sstep 30 -af volume=-200 $video" );

	if( !empty( $output ) ) {
		// unfortunately i can't seem to fully control mplayer all the time so we simply remove all xs images
		// we keep a few and nuke the rest
		$frameshots = treasury_video_get_frameshots( $destDir );
		$count      = count( $frameshots );
		for( $i = 5; $i < $count; $i++ ) {
			unlink( $frameshots[$i] );
		}
		return $frameshots;
	}
	return FALSE;
}

/**
 * Get a list of images found in a given directory
 * 
 * @param array $pPath Path to extracted archive
 * @access public
 * @return Path to preview image on success, FALSE on failure
 */
function treasury_video_get_frameshots( $pPath ) {
	$ret = array();
	if( $dh = opendir( $pPath ) ) {
		while( FALSE !== ( $file = readdir( $dh ) ) ) {
			if( $file != '.' && $file != '..' && preg_match( "/.*\.jpg$/i", $file ) ) {
				$ret[] = $pPath.'/'.$file;
			}
		}
	}
	closedir( $dh );
	sort( $ret );
	return( !empty( $ret ) ? $ret : FALSE );
}
?>
