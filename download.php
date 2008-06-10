<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/Attic/download.php,v 1.15 2008/06/10 17:59:08 squareing Exp $
 *
 * @author       xing  <xing@synapse.plus.com>
 * @package      treasury
 * @copyright    2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/

/**
 * Setup
 */ 
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );

require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'item_lookup_inc.php');

$gContent->verifyDownloadPermission();
$gContent->verifyGalleryPermissions( 'p_treasury_download_item' );

// TODO: make sure this works reliably - not sure what is better:
//       - add hit after download is complete
//       - add hit as soon as user executes this script
//$gContent->addHit();
if( $download_function = $gLibertySystem->getPluginFunction( $gContent->mInfo['plugin_guid'], 'download_function' )) {
	if( $download_function( $gContent->mInfo )) {
		// add hit if download was successful
		$gContent->addHit();
	} else {
		if( !empty( $gContent->mInfo['errors'] )) {
			$msg = '';
			foreach( $gContent->mInfo['errors'] as $error ) {
				$msg .= $error.'<br />';
			}
			$gBitSystem->fatalError( tra( $msg ));
		} else {
			$gBitSystem->fatalError( tra( 'There was an undetermined problem trying to prepare the file for download.' ));
		}
	}
} else {
	$gBitSystem->fatalError( tra( "No suitable download function found." ));
}
?>
