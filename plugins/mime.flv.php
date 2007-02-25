<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_treasury/plugins/Attic/mime.flv.php,v 1.4 2007/02/25 08:10:59 squareing Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.4 $
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
	// if storing works, we process the video
	if( $ret = treasury_default_store( $pStoreRow, $pCommonObject )) {
		if( $gBitSystem->isFeatureActive( 'treasury_use_cron' )) {
			// if we want to use cron, we add a process, otherwise we convert video right away
			if( treasury_flv_add_process( $pStoreRow['content_id'] )) {
				// add an indication that this file is being processed
				touch( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."processing" );
			}
		} else {
			if( !treasury_flv_converter( $pStoreRow )) {
				$pStoreRow['errors'] = $pStoreRow['log'];
			}
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
	global $gBitSystem;
	// if storing works, we process the video
	if( $ret = treasury_default_update( $pStoreRow, $pCommonObject )) {
		// we only need to add a new process when we are actually uploading a new file
		if( !empty( $pStoreRow['upload']['tmp_name'] )) {
			// add an indication that this file is being processed
			touch( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."processing" );
			// remove any error file since this is a new video file
			@unlink( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."error" );
			// since this user is uploading a new video, we will remove the old flick.flv file
			@unlink( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."flick.flv" );

			// if we want to use cron, we add a process, otherwise we convert video right away
			if( $gBitSystem->isFeatureActive( 'treasury_use_cron' )) {
				treasury_flv_add_process( $pStoreRow['content_id'] );
			} else {
				if( !treasury_flv_converter( $pStoreRow )) {
					$pStoreRow['errors'] = $pStoreRow['log'][$pStoreRow['content_id']]['message'];
				}
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

/**
 * Convert a stored video file in treasury to flashvideo
 * 
 * @param array $pParamHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function treasury_flv_converter( &$pParamHash ) {
	global $gBitSystem;

	// video conversion can take a while
	ini_set( "max_execution_time", "1800" );

	// these are set in the treasury plugin admin screen
	$convert['ffmpeg']     = $gBitSystem->getConfig( 'treasury_flv_ffmpeg_path', shell_exec( 'which ffmpeg' ));
	$convert['video_rate'] = $gBitSystem->getConfig( 'treasury_flv_video_rate', 22050 );
	$convert['audio_rate'] = $gBitSystem->getConfig( 'treasury_flv_audio_rate', 32 );
	$convert['width']      = $gBitSystem->getConfig( 'treasury_flv_width', 320 );

	$ret = FALSE;

	if( @BitBase::verifyId( $pParamHash['content_id'] )) {
		$begin = date( 'U' );
		$log   = array();

		$item = new TreasuryItem( NULL, $pParamHash['content_id'] );
		$item->load();

		$source = $item->mInfo['source_file'];
		$dest_path = dirname( $item->mInfo['source_file'] );
		$dest_file = $dest_path.'/flick.flv';

		// we can do some nice stuff if ffmpeg-php is available
		if( extension_loaded( 'ffmpeg' )) {
			$movie = new ffmpeg_movie( $source );
			$info['duration']   = $movie->getDuration();
			$info['width']      = $movie->getFrameWidth();
			$info['height']     = $movie->getFrameHeight();
			// aspect ratio
			$info['aspect']     = $info['width'] / $info['height'];

			// size of flv - width is set to width
			$ratio              = $convert['width'] / $info['width'];
			$info['flv_width']  = $convert['width'];
			$info['flv_height'] = round( $ratio * $info['height'] );
			// height of video needs to be an even number
			if( $info['flv_height'] % 2 ) {
				$info['flv_height']++;
			}
			$info['size']       = "{$info['flv_width']}x{$info['flv_height']}";

			// screenshot offset is relative to flick length - we'll pick a frame somewhere in the middle
			// if we're dealing with a wmv file, we things get wonky - as to be expected with m$ in the game - gah!
			if( preg_match( "!\.wmv$!i", $source )) {
				if( $info['duration'] >= 240 ) {
					$info['offset'] = '00:01:00';
				} else {
					$info['offset'] = '00:00:'.floor( $info['duration'] / 4 );
				}
			} else {
				if( $info['duration'] >= 120 ) {
					$info['offset'] = '00:01:00';
				} else {
					$info['offset'] = '00:00:'.floor( $info['duration'] / 4 );
				}
			}
		} else {
			// set some default values if ffmpeg isn't available
			$info['aspect']     = "4:3";
			$info['flv_width']  = $convert['width'];
			$info['flv_height'] = round( $convert['width'] / 4 * 3 );
			$info['size']       = "{$info['flv_width']}x{$info['flv_height']}";
			$info['offset']     = '00:00:10';
		}

		// we store the video size as a content preference
		$prefNames = array( 'flv_height', 'flv_width', 'duration' );
		foreach( $prefNames as $name ) {
			if( !empty( $info[$name] )) {
				$item->storePreference( $name, $info[$name] );
			}
		}

		$log['debug'] = shell_exec( "{$convert['ffmpeg']} -i '$source' -acodec mp3 -ar {$convert['video_rate']} -ab {$convert['audio_rate']} -f flv -s {$info['size']} -aspect {$info['aspect']} -y '$dest_file'" );

		if( is_file( $dest_file ) && filesize( $dest_file ) > 1 ) {
			// since the flv conversion worked, we will create a preview screenshots to show.
			shell_exec( "{$convert['ffmpeg']} -i '$dest_file' -an -ss {$info['offset']} -t 00:00:01 -r 1 -y '$dest_path/preview%d.jpg'" );
			if( is_file( "$dest_path/preview1.jpg" )) {
				$fileHash['type']        = 'image/jpg';
				$fileHash['thumbsizes']  = array( 'icon', 'avatar', 'small', 'medium' );
				$fileHash['source_file'] = "$dest_path/preview1.jpg";
				$fileHash['dest_path']   = str_replace( BIT_ROOT_PATH, '', "$dest_path/" );
				liberty_generate_thumbnails( $fileHash );
			}
			$log['message'] = 'SUCCESS: Video converted to flash video';
		} else {
			// remove badly converted file
			@unlink( $dest_file );
			touch( $dest_path."/error" );
			$log['message'] = 'ERROR: There was a problem during video conversion. DEBUG OUTPUT: '.$log['debug'];
		}

		@unlink( $dest_path.'/processing' );
		$log['time']     = date( 'd/M/Y:H:i:s O' );
		$log['duration'] = date( 'U' ) - $begin;

		// return the log
		$pParamHash['log'] = $log;
	}
	return $ret;
}
?>
