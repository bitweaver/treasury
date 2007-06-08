<?php
/**
 * @version		$Header: /cvsroot/bitweaver/_bit_treasury/plugins/cron.flv.php,v 1.5 2007/06/08 20:06:30 squareing Exp $
 *
 * @author		xing  <xing@synapse.plus.com>
 * @version		$Revision: 1.5 $
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

some distros as examples:
- Gentoo
# emerge --sync
# USE="lame" emerge ffmpeg

- Fedora Core 6
at the time of writing this, the version in livna was faulty - i used the one 
in freshrpms:
# rpm -ihv http://ayo.freshrpms.net/fedora/linux/6/i386/RPMS.freshrpms/freshrpms-release-1.1-1.fc.noarch.rpm
# yum install ffmpeg

- generic instructions getting ffmpeg from svn for ubuntu or other distros
$ svn checkout svn://svn.mplayerhq.hu/ffmpeg/trunk ffmpeg
$ cd ffmpeg
$ ./configure --help | grep enable
$ ./configure --enable-libmp3lame --enable-gpl
$ make
# make install


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
require_once( '../../bit_setup_inc.php' );
require_once( TREASURY_PKG_PATH.'TreasuryItem.php' );

// add some protection for arbitrary thumbail execution.
// if argc is present, we will trust it was exec'ed command line.
if( empty( $argc ) && !$gBitUser->isAdmin() ) {
	$gBitSystem->fatalError( tra( 'You cannot run the video converter' ));
}

$gBitSystem->mDb->StartTrans();

$processLimit = ( !empty( $argv[1] )) ? $argv[1] : ( !empty( $_REQUEST['videos'] ) ? $_REQUEST['videos'] : 3 );
$query = "
	SELECT lpq.`content_id` AS `hash_key`, lpq.*
	FROM `".BIT_DB_PREFIX."liberty_process_queue` lpq
	WHERE lpq.begin_date IS NULL AND lpq.`process_status`=?
	ORDER BY lpq.`queue_date` ASC";
$result = $gBitSystem->mDb->query( $query, array( 'pending' ), $processLimit );

$processList = array();
while( !$result->EOF ) {
	$processList[$result->fields['content_id']] = $result->fields;
	$query = "UPDATE `".BIT_DB_PREFIX."liberty_process_queue` SET `process_status`=?, `begin_date`=? WHERE `process_id`=?";
	$gBitSystem->mDb->query( $query, array( 'processing', $gBitSystem->getUTCTime(), $result->fields['process_id'] ));
	$result->MoveNext();
}

$gBitSystem->mDb->CompleteTrans();

$log   = array();
$total = date( 'U' );

// we just zap through all these files and let the converter function worry about all the problems that might arise
foreach( $processList as $item ) {
	if( treasury_flv_converter( $item )) {
		$query = "UPDATE `".BIT_DB_PREFIX."liberty_process_queue` SET `end_date`=? WHERE `content_id`=?";
		$result = $gBitSystem->mDb->query( $query, array( $gBitSystem->getUTCTime(), $item['content_id'] ));
	}
	$log[$item['content_id']] = $item['log'];
}

// output some info
foreach( array_keys( $log ) as $contentId ) {
	// generate something that kinda looks like apache common log format
	print $contentId.' - - ['.$log[$contentId]['time'].'] "'.$log[$contentId]['message'].'" '.$log[$contentId]['duration']." seconds <br/>\n";
}

if( count( $processList )) {
	print '# '.count( $processList )." videos processed in ".( date( 'U' ) - $total )." seconds<br/>\n";
}
?>
