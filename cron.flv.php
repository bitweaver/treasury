<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_treasury/Attic/cron.flv.php,v 1.4 2007/02/15 19:29:22 lsces Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.4 $
 * created		Sunday Jul 02, 2006   14:42:13 CEST
 * @package		treasury
 * @subpackage	treasury_mime_handler
 **/


/**
 * Body of file
 */
/* ================================= FFMPEG =================================
If you want to make use of the flv plugin, you need to install the svn version
of ffmpeg. only the latest version of ffmpeg supports the flv format.


--- INSTALL
You will need the svn version of ffmpeg and you will have to compile this
yourself:

cvs -z9 -d:pserver:anonymous@mplayerhq.hu:/cvsroot/ffmpeg co ffmpeg
cd ffmpeg
./configure --help | grep enable
./configure --enable-mp3lame --enable-gpl
make
make install


--- NOTE
Depending on the system, you might be able to support more formats. These are
the configure flags i used:
./configure --enable-mp3lame --enable-gpl --enable-a52 --enable-libogg \
--enable-vorbis --enable-xvid


--- CRON
Processing video files can take a while and this process is therefore designed
as a cron job. Set up a cron job similar to the following. This will check for
open conversion jobs every minute:
* * * * * apache /path/to/php -q /path/to/cron.flv.php [number of videos to process] >> /path/to/log/file.log
NOTE
- make sure apache is your apache user - you can check the 'User' setting in 
  your apache.conf file (usually located at /etc/apache2/httpd.conf)
- [number of videos to process] is optional, default is 3


================================= FFMPEG-PHP =================================
--- THIS EXTENSION IS NOT REQUIRED
ffmpeg-php is a php extension that makes it possible to easily access video 
information from within php. Please view the official site on how to add 
ffmpeg-php to your php setup: http://ffmpeg-php.sourceforge.net

all it allows us to do is check the aspect ratio and video length. This allows 
us to extract more appropriate video images and make more accurate conversions.

If you compile it as an extension, it should be recognised by this script. If 
you decide to compile it into your php, please modify the script below where it 
says:
if( extension_loaded( 'ffmpeg' )) {
}
*/


// =========================== Configuration Options ===========================
// set the absolute path to ffmpeg
$ffmpeg        = 'ffmpeg';
// sampling rate (valid values are 11025, 22050, 44100)
$sampling_rate = 22050;
// bit rate of audio (valid values are 16,32,64)
$bit_rate      = 32;
// width of video in pixels - height is adjusted automatically
// vaues need to be multiples of 4: e.g.: 320, 400, 520...
$width         = 320;



// ========================== Don't modify below here ==========================
// increase the execution time. depending on the size and length of a flick, it 
// can take quite some time before it is done
ini_set( "max_execution_time", "1800" );

global $gBitSystem, $gBitUser, $_SERVER;

$_SERVER['SCRIPT_URL']  = '';
$_SERVER['HTTP_HOST']   = '';
$_SERVER['HTTP_HOST']   = '';
$_SERVER['HTTP_HOST']   = '';
$_SERVER['SERVER_NAME'] = '';

if( !empty( $argc )) {
	// reduce feedback for command line to keep log noise way down
	define( 'BIT_PHP_ERROR_REPORTING', E_ERROR | E_PARSE );
}

// running from cron can cause us not to be in the right dir.
chdir( dirname( __FILE__ ));
require_once( '../bit_setup_inc.php' );
require_once( TREASURY_PKG_PATH.'TreasuryItem.php' );

// add some protection for arbitrary thumbail execution.
// if argc is present, we will trust it was exec'ed command line.
if( empty( $argc ) && !$gBitUser->isAdmin() ) {
	$gBitSystem->fatalError( 'You cannot run the thumbnailer' );
}

$gBitSystem->mDb->StartTrans();

$processLimit = ( !empty( $argv[1] )) ? $argv[1] : ( !empty( $_REQUEST['videos'] ) ? $_REQUEST['videos'] : 3 );
$query = "
	SELECT tpq.content_id AS hash_key, tpq.*
	FROM `".BIT_DB_PREFIX."treasury_process_queue` tpq
	WHERE tpq.begin_date IS NULL
	ORDER BY tpq.queue_date";
$result = $gBitSystem->mDb->query( $query, NULL, $processLimit );

$processList = array();
while( !$result->EOF ) {
	$processList[$result->fields['content_id']] = $result->fields;
	$query2 = "UPDATE `".BIT_DB_PREFIX."treasury_process_queue` SET `begin_date`=? WHERE `content_id`=?";
//	$result2 = $gBitSystem->mDb->query( $query2, array( date( 'U' ), $result->fields['content_id'] ));
	$result->MoveNext();
}

$gBitSystem->mDb->CompleteTrans();

$log   = array();
$total = date( 'U' );
$debug = FALSE;

// check to see if ffmpeg is available at all
if( !shell_exec( "$ffmpeg -h" )) {
	$log[0]['time']     = date( 'd/M/Y:H:i:s O' );
	$log[0]['duration'] = 0;
	$log[0]['message']  = 'ERROR: ffmpeg does not seem to be available on your system. Please see the comments at the beginning of this file on how to install and configure it.';
} else {
	foreach( array_keys( $processList ) as $contentId ) {
		$item = new TreasuryItem( NULL, $contentId );
		$item->load();
		$begin = date( 'U' );

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

			// size of flv - width is set to $width
			$ratio              = $width / $info['width'];
			$info['flv_width']  = $width;
			$info['flv_height'] = round( $ratio * $info['height'] );
			// height of video needs to be an even number
			if( $info['flv_height'] % 2 ) {
				$info['flv_height']++;
			}
			$info['size']       = "{$info['flv_width']}x{$info['flv_height']}";

			// screenshot offset is relative to flick length
			if( $info['duration'] >= 120 ) {
				$info['offset'] = '00:01:00';
			} else {
				$info['offset'] = '00:00:'.floor( $info['duration'] / 2 );
			}
		} else {
			// set some default values if ffmpeg isn't available
			$info['aspect']     = "4:3";
			$info['flv_width']  = $width;
			$info['flv_height'] = round( $width / 4 * 3 );
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

		$log[$contentId]['debug'] = shell_exec( "$ffmpeg -i '$source' -acodec mp3 -ar $sampling_rate -ab $bit_rate -f flv -s {$info['size']} -aspect {$info['aspect']} -y '$dest_file'" );

		if( is_file( $dest_file ) && filesize( $dest_file ) > 1 ) {
			// since the flv conversion worked, we will create a preview screenshots to show.
			shell_exec( "$ffmpeg -i '$dest_file' -an -ss {$info['offset']} -t 00:00:01 -r 1 -y '$dest_path/preview%d.jpg'" );
			if( is_file( "$dest_path/preview1.jpg" )) {
				$fileHash['type']        = 'image/jpg';
				$fileHash['thumbsizes']  = array( 'icon', 'avatar', 'small', 'medium' );
				$fileHash['source_file'] = "$dest_path/preview1.jpg";
				$fileHash['dest_path']   = str_replace( BIT_ROOT_PATH, '', "$dest_path/" );
				liberty_generate_thumbnails( $fileHash );
			}
			$log[$contentId]['message'] = 'SUCCESS: Video converted to flash video';
			$query3 = "UPDATE `".BIT_DB_PREFIX."treasury_process_queue` SET `begin_date`=?, `end_date`=? WHERE `content_id`=?";
			$result3 = $gBitSystem->mDb->query( $query3, array( $begin, $gBitSystem->getUTCTime(), $contentId ));
		} else {
			// remove badly converted file
			@unlink( $dest_file );
			touch( $dest_path."/error" );
			$log[$contentId]['message'] = 'ERROR: There was a problem during video conversion. DEBUG OUTPUT: '.$log[$contentId]['debug'];
		}

		@unlink( $dest_path.'/processing' );
		$log[$contentId]['time']     = date( 'd/M/Y:H:i:s O' );
		$log[$contentId]['duration'] = date( 'U' ) - $begin;
	}
}

// output some info
foreach( array_keys( $log ) as $contentId ) {
	// generate something that kinda looks like apache common log format
	print $contentId.' - - ['.$log[$contentId]['time'].'] "'.$log[$contentId]['message'].'" '.$log[$contentId]['duration']."seconds <br/>\n";
}

if( count( $processList )) {
	print '# '.count( $processList )." videos processed in ".( date( 'U' ) - $total )." seconds<br/>\n";
}
?>
