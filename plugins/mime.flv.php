<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_treasury/plugins/Attic/mime.flv.php,v 1.26 2007/07/29 15:24:23 squareing Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.26 $
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
		} else {
			// so far this is the only plugin that can make use of the prefs being loaded in a list.
			$pFileHash['prefs'] = LibertyContent::loadPreferences( $pFileHash['content_id'] );
		}

		// we can use a special plugin if active to include flvs in wiki pages
		if( $gLibertySystem->isPluginActive( 'dataflashvideo' )) {
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

			// set some default values if ffpeg-php isn't available or fails
			$default['aspect']     = 4 / 3;
			$default['flv_width']  = $width;
			$default['flv_height'] = round( $width / 4 * 3 );
			$default['size']       = "{$default['flv_width']}x{$default['flv_height']}";
			$default['offset']     = '00:00:10';

			if( $pParamHash['upload']['type'] == 'video/x-flv' ) {
				// this is already an flv file - we'll just extract some information and store the video
				if( extension_loaded( 'ffmpeg' )) {
					$movie = new ffmpeg_movie( $source );
					$info['duration'] = round( $movie->getDuration() );
					$info['width']    = $movie->getFrameWidth();
					$info['height']   = $movie->getFrameHeight();
				}

				// if we have a width, ffmpeg-php was successful
				if( !empty( $info['width'] )) {
					$info['aspect']   = $info['width'] / $info['height'];
					$info['offset']   = strftime( "%T", round( $info['duration'] / 5 - ( 60 * 60 )));
				} else {
					$info = $default;
				}

				// store prefs and create thumbnails
				treasury_flv_store_preferences( $info, $item );
				treasury_flv_create_thumbnail( $source, $info['offset'] );
				rename( $source, $dest_file );
				$log['message'] = 'SUCCESS: Converted to flash video';
				$item->mLogs['flv_converter'] = "Flv video file was successfully uploaded and thumbnails extracted.";
				$ret = TRUE;
			} else {
				// we can do some nice stuff if ffmpeg-php is available
				if( extension_loaded( 'ffmpeg' )) {
					$movie = new ffmpeg_movie( $source );
					$info['duration'] = round( $movie->getDuration() );
					$info['width']    = $movie->getFrameWidth();
					$info['height']   = $movie->getFrameHeight();
				}

				// if the video can be processed by ffmpeg-php, width and height are greater than 1
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
				} else {
					$info = $default;
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
					// try to work out a reasonable timepoint where to extract a screenshot
					if( preg_match( '!Duration: ([\d:\.]*)!', $debug, $time )) {
						list( $h, $m, $s ) = explode( ':', $time[1] );
						$seconds = round( 60 * 60 * (int)$h + 60 * (int)$m + (float)$s );
						// we need to subract one hour from our time for strftime to return the correct value
						$info['offset'] = strftime( "%T", round( $seconds / 5 - ( 60 * 60 )));
					} else {
						$info['offset'] = "00:00:10";
					}
					// store some video specific settings
					treasury_flv_store_preferences( $info, $item );

					// since the flv conversion worked, we will create a preview screenshots to show.
					treasury_flv_create_thumbnail( $dest_file, $info['offset'] );

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
 * This function will create a thumbnail for a given video
 * 
 * @param string $pFile path to video file
 * @param numric $pOffset Offset in seconds to use to create thumbnail from
 * @access public
 * @return TRUE on success, FALSE on failure
 */
function treasury_flv_create_thumbnail( $pFile, $pOffset = 60 ) {
	global $gBitSystem;
	$ret = FALSE;
	if( !empty( $pFile )) {
		$dest_path = dirname( $pFile );

		// try to use an app designed specifically to extract a thumbnail
		if( shell_exec( shell_exec( 'which ffmpegthumbnailer' ).' -h' )) {
			$thumbnailer = trim( shell_exec( 'which ffmpegthumbnailer' ));
		} elseif( shell_exec( shell_exec( 'which ffmpegvideothumbnailer' ).' -h' )) {
			$thumbnailer = trim( shell_exec( 'which ffmpegvideothumbnailer' ));
		}

		if( !empty( $thumbnailer )) {
			shell_exec( "$thumbnailer -i '$pFile' -o '$dest_path/medium.jpg' -s 600" );
			if( is_file( "$dest_path/medium.jpg" )) {
				$fileHash['type']            = 'image/jpg';
				$fileHash['thumbnail_sizes'] = array( 'icon', 'avatar', 'small' );
				$fileHash['source_file']     = "$dest_path/medium.jpg";
				$fileHash['dest_path']       = str_replace( BIT_ROOT_PATH, '', "$dest_path/" );
				liberty_generate_thumbnails( $fileHash );
				$ret = TRUE;
			}
		} else {
			// fall back to using ffmepg
			$ffmpeg    = trim( $gBitSystem->getConfig( 'treasury_flv_ffmpeg_path', shell_exec( 'which ffmpeg' )));
			shell_exec( "$ffmpeg -i '$pFile' -an -ss $pOffset -t 00:00:01 -r 1 -y '$dest_path/preview%d.jpg'" );
			if( is_file( "$dest_path/preview1.jpg" )) {
				$fileHash['type']            = 'image/jpg';
				$fileHash['thumbnail_sizes'] = array( 'icon', 'avatar', 'small', 'medium' );
				$fileHash['source_file']     = "$dest_path/preview1.jpg";
				$fileHash['dest_path']       = str_replace( BIT_ROOT_PATH, '', "$dest_path/" );
				liberty_generate_thumbnails( $fileHash );
				$ret = TRUE;
			}
		}
	}
	return $ret;
}

/**
 * treasury_flv_store_preferences 
 * 
 * @param array $pVideoInfo Video information
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function treasury_flv_store_preferences( $pVideoInfo, $pObject ) {
	$ret = FALSE;
	if( !empty( $pObject ) && $pObject->isValid() ) {
		// store duration of video
		if( !empty( $pVideoInfo['duration'] )) {
			$pObject->storePreference( 'duration', $pVideoInfo['duration'] );
		}

		// only store aspect if aspect is different to 4:3
		$default = 4 / 3;
		if( !empty( $pVideoInfo['aspect'] ) && $pVideoInfo['aspect'] != $default ) {
			$pObject->storePreference( 'aspect', $pVideoInfo['aspect'] );
		}
		$ret = TRUE;
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
