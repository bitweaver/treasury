<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_treasury/plugins/Attic/mime.flv.php,v 1.20 2007/06/11 17:38:31 squareing Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.20 $
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
			if( treasury_flv_add_process( $pStoreRow )) {
				// add an indication that this file is being processed
				touch( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path']."processing" );
			}
		} else {
			if( !treasury_flv_converter( $pStoreRow )) {
				$pStoreRow['errors'] = $pStoreRow['log'];
				$ret = FALSE;
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
				treasury_flv_add_process( $pStoreRow );
			} else {
				if( !treasury_flv_converter( $pStoreRow )) {
					$pStoreRow['errors'] = $pStoreRow['log']['message'];
				}
			}
		}
	}
	return $ret;
}

/**
 * Load file data from the database
 * 
 * @param array $pFileHash contains all file information
 * @param array $pCommonObject is the full object loaded. only set when we are actually loading the object, not just listing items
 * @param array $pPluginParameters is the full object loaded. only set when we are actually loading the object, not just listing items
 * @access public
 * @return TRUE on success, FALSE on failure - ['errors'] will contain reason for failure
 */
function treasury_flv_load( &$pFileHash, &$pCommonObject, $pPluginParameters = NULL ) {
	global $gBitSmarty, $gBitSystem, $gLibertySystem;
	if( $ret = treasury_default_load( $pFileHash, $pCommonObject )) {
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

		// if we are passed an object, we'll modify width and height according to our needs
		if( is_object( $pCommonObject )) {
			// set the width and height of the video
			$pCommonObject->setPreference( 'flv_width', $gBitSystem->getConfig( 'treasury_flv_width', 320 ));
			$pCommonObject->setPreference( 'flv_height', $gBitSystem->getConfig( 'treasury_flv_width', 320 ) / $pCommonObject->getPreference( 'aspect', 4 / 3 ));

			// now that we have the original width and height, we can get the displayed values
			treasury_flv_calculate_videosize( $pPluginParameters, $pCommonObject->mPrefs );

			// since pCommonObject is only set when the file is fully loaded, we can add a hit - hardly anyone will download the original if they can view the flv...
			$pCommonObject->addHit();
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
function treasury_flv_add_process( $pStoreRow ) {
	global $gBitSystem;
	$ret = FALSE;
	if( @BitBase::verifyId( $pStoreRow['content_id'] )) {
		$query = "
			UPDATE `".BIT_DB_PREFIX."liberty_process_queue`
			SET `process_status`=?
			WHERE `content_id`=? AND `process_status`=?";
		$gBitSystem->mDb->query( $query, array( 'defunkt', $pStoreRow['content_id'], 'pending' ));

		$storeHash = array (
			'content_id'           => $pStoreRow['content_id'],
			'queue_date'           => $gBitSystem->getUTCTime(),
			'process_status'       => 'pending',
			'processor'            => dirname( __FILE__ ).'/mime.flv.php',
			'processor_parameters' => treasury_flv_converter( $pStoreRow, TRUE ),
		);
		$gBitSystem->mDb->associateInsert( BIT_DB_PREFIX."liberty_process_queue", $storeHash );
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
function treasury_flv_converter( &$pParamHash, $pGetParameters = FALSE ) {
	global $gBitSystem;

	// video conversion can take a while
	ini_set( "max_execution_time", "1800" );

	$ret = FALSE;

	if( @BitBase::verifyId( $pParamHash['content_id'] )) {
		// these are set in the treasury plugin admin screen
		$ffmpeg     = trim( $gBitSystem->getConfig( 'treasury_flv_ffmpeg_path', shell_exec( 'which ffmpeg' )));
		$video_rate = trim( $gBitSystem->getConfig( 'treasury_flv_video_rate', 22050 ));
		$audio_rate = trim( $gBitSystem->getConfig( 'treasury_flv_audio_rate', 32 ));
		$width      = trim( $gBitSystem->getConfig( 'treasury_flv_width', 320 ));

		$begin = date( 'U' );
		$log   = array();

		// check to see if ffmpeg is available at all
		$item = new TreasuryItem( NULL, $pParamHash['content_id'] );
		if( !shell_exec( "$ffmpeg -h" )) {
			$log['time']     = date( 'Y-M-d - H:i:s O' );
			$log['duration'] = 0;
			$log['message']  = 'ERROR: ffmpeg does not seem to be available on your system at: '.$ffmpeg.' Please set the path to ffmpeg in the treasury administration screen.';
		} else {
			$item->load();
			$source = $item->mInfo['source_file'];
			$dest_path = dirname( $item->mInfo['source_file'] );
			$dest_file = $dest_path.'/flick.flv';

			// we can do some nice stuff if ffmpeg-php is available
			if( extension_loaded( 'ffmpeg' )) {
				$movie = new ffmpeg_movie( $source );
				$info['duration']   = round( $movie->getDuration() );
				$info['width']      = $movie->getFrameWidth();
				$info['height']     = $movie->getFrameHeight();

				// if the video can be processed by ffmpeg, width and height are greater than 1
				if( !empty( $info['width'] )) {
					// aspect ratio
					$info['aspect']     = $info['width'] / $info['height'];

					// size of flv - width is set to default width
					$ratio              = $width / $info['width'];
					$info['flv_width']  = $width;
					$info['flv_height'] = round( $ratio * $info['height'] );
					// height of video needs to be an even number
					if( $info['flv_height'] % 2 ) {
						$info['flv_height']++;
					}
					$info['size']       = "{$info['flv_width']}x{$info['flv_height']}";

					// screenshot offset is relative to flick length - we'll pick a frame somewhere in the middle
					// if we're dealing with a wmv file, things might get wonky - as to be expected with M$ in the game - gah!
					if( preg_match( "!\.wmv$!i", $source )) {
						$max_durartion = 240;
					} else {
						$max_durartion = 120;
					}

					if( $info['duration'] >= $max_durartion ) {
						$info['offset'] = '00:01:00';
					} else {
						$info['offset'] = '00:00:'.floor( $info['duration'] / 4 );
					}
				}
			}

			if( empty( $info['width'] )) {
				// set some default values if ffmpeg isn't available or ffmpeg-php couldn't get the relevant information
				$info['aspect']     = 4 / 3;
				$info['flv_width']  = $width;
				$info['flv_height'] = round( $width / 4 * 3 );
				$info['size']       = "{$info['flv_width']}x{$info['flv_height']}";
				$info['offset']     = '00:00:10';
			}

			// we keep the output of this that we can store it to the error file if we need to do so
			$parameters = "-i '$source' -acodec mp3 -ar $video_rate -ab $audio_rate -f flv -s {$info['size']} -aspect {$info['aspect']} -y '$dest_file'";
			if( $pGetParameters ) {
				return $parameters;
			} else {
				$debug = shell_exec( "$ffmpeg $parameters 2>&1" );
			}

			// make sure the conversion was successfull
			if( is_file( $dest_file ) && filesize( $dest_file ) > 1 ) {
				// store duration of video
				if( !empty( $info['duration'] )) {
					$item->storePreference( 'duration', $info['duration'] );
				}

				// only store aspect if aspect is different to 4:3
				$default = 4 / 3;
				if( !empty( $info['aspect'] ) && $info['aspect'] != $default ) {
					$item->storePreference( 'aspect', $info['aspect'] );
				}

				// since the flv conversion worked, we will create a preview screenshots to show.
				shell_exec( "$ffmpeg -i '$dest_file' -an -ss {$info['offset']} -t 00:00:01 -r 1 -y '$dest_path/preview%d.jpg'" );
				if( is_file( "$dest_path/preview1.jpg" )) {
					$fileHash['type']        = 'image/jpg';
					$fileHash['thumbsizes']  = array( 'icon', 'avatar', 'small', 'medium' );
					$fileHash['source_file'] = "$dest_path/preview1.jpg";
					$fileHash['dest_path']   = str_replace( BIT_ROOT_PATH, '', "$dest_path/" );
					liberty_generate_thumbnails( $fileHash );
				}
				$log['message'] = 'SUCCESS: Converted to flash video';
				$item->mLogs['flv_converter'] = "Converted to flashvideo in ".( date( 'U' ) - $begin )." seconds";
				$ret = TRUE;
			} else {
				// remove unsuccessfully converted file
				@unlink( $dest_file );
				$log['message'] = 'ERROR: The video you uploaded could not be converted by ffmpeg. DEBUG OUTPUT: '.nl2br( $debug );
				$item->mErrors['flv_converter'] = "Video could not be converted to flashvideo. An error dump was saved to: ".$dest_path.'/error';

				// write error message to error file
				$h = fopen( $dest_path."/error", 'w' );
				fwrite( $h, $debug );
				fclose( $h );
			}
			@unlink( $dest_path.'/processing' );
		}

		$log['time']     = date( 'd/M/Y:H:i:s O' );
		$log['duration'] = date( 'U' ) - $begin;

		// we'll add an entry in the action logs
		$item->storeActionLog();

		// return the log
		$pParamHash['log'] = $log;
	}
	return $ret;
}

/**
 * Calculate the display video size
 * 
 * @param array $pParamHash hash of data to be used to calculate video size
 * @access public
 * @return TRUE when there has been a video size change, FALSE if there has been no change
 */
function treasury_flv_calculate_videosize( $pParamHash, &$pVideoInfo ) {
	global $gBitSystem;
	$ret = FALSE;

	// if we want to display a different size
	if( !empty( $pParamHash['size'] )) {
		if( $pParamHash['size'] == 'small' ) {
			$new_width = 160;
			$pVideoInfo['digits'] = 'false';
		} elseif( $pParamHash['size'] == 'medium' ) {
			$new_width = 320;
		} elseif( $pParamHash['size'] == 'large' ) {
			$new_width = 480;
		} elseif( $pParamHash['size'] == 'huge' ) {
			$new_width = 600;
		}
	} else {
		$new_width = $gBitSystem->getConfig( 'treasury_flv_default_size' );
	}

	// if they set a custom width, we use that
	if( @BitBase::verifyId( $pParamHash['width'] )) {
		$new_width = $pParamHash['width'];
	}

	// if we want to change the video size
	if( !empty( $new_width )) {
		// if they set a custom height, we use that
		if( @BitBase::verifyId( $pParamHash['height'] )) {
			$pVideoInfo['flv_height'] = $pParamHash['height'];
		} else {
			$ratio = $pVideoInfo['flv_width'] / $new_width;
			$pVideoInfo['flv_height'] = round( $pVideoInfo['flv_height'] / $ratio );
		}

		// now that all calculations are done, we apply the width
		$pVideoInfo['flv_width']  = $new_width;

		$ret = TRUE;
	}

	return $ret;
}
?>
