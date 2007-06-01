<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/plugins/Attic/form.flv.php,v 1.6 2007/06/01 08:10:09 squareing Exp $
 *
 * @author       xing  <xing@synapse.plus.com>
 * @package      treasury
 * @copyright    2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/

/**
 * Setup
 */ 
require_once( '../../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );

require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'item_lookup_inc.php');

if( $gContent->isOwner() || $gBitUser->isAdmin() || !empty( $_REQUEST['confirm'] ) && @BitBase::verifyId( $_REQUEST['content_id'] )) {
	if( !empty( $_REQUEST['aspect'] )) {
		if( $_REQUEST['aspect'] == 4 / 3 ) {
			$gContent->storePreference( 'aspect', NULL );
		} else {
			$gContent->storePreference( 'aspect', $_REQUEST['aspect'] );
		}
	} elseif( !empty( $_REQUEST['remove_original'] )) {

		if( !empty( $_REQUEST['confirm'] ) ) {
			@unlink( $gContent->mInfo['source_file'] );
			$gContent->mLogs['original'] = tra( 'Source file removed' );
			$gContent->storeActionLog();
			bit_redirect( $gContent->getDisplayUrl() );
		}

		$gBitSystem->setBrowserTitle( 'Confirm removal of '.$gContent->mInfo['title'] );
		$formHash['remove_original'] = TRUE;
		$formHash['content_id'] = $_REQUEST['content_id'];
		require_once $gBitSmarty->_get_plugin_filepath( 'modifier', 'display_bytes' );
		$msgHash = array(
			'label'        => 'Remove Original File',
			'confirm_item' => $gContent->mInfo['title'].
			'<br />'.tra( 'Filename' ).": ".$gContent->mInfo['filename'].
			"<br /><small>(".$gContent->mInfo['mime_type']." ".smarty_modifier_display_bytes( $gContent->mInfo['file_size'] ).")</small>",
				'warning'      => 'This will remove the original file from the server. The falsh video will remain and you can still view the video but you cannot download the original anymore.',
			);
		$gBitSystem->confirmDialog( $formHash, $msgHash );
die;
	}
}

bit_redirect( $gContent->getDisplayUrl() );
?>
