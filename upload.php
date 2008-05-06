<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/upload.php,v 1.22 2008/05/06 20:02:50 nickpalmer Exp $
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

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php');
require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'gallery_lookup_inc.php');
require_once( LIBERTY_PKG_PATH.'calculate_max_upload_inc.php' );

// turn the max_file_size value into megabytes
$gBitSmarty->assign_by_ref( 'feedback', $feedback = array() );

$listHash['get_sub_tree']       = TRUE;
$listHash['max_records']        = -1;
$listHash['content_permission'] = 'p_treasury_upload_item';
$galleryList = $gContent->getList( $listHash );
$gBitSmarty->assign( 'galleryList', $galleryList );

// if we have no gallery we can upload our stuff into and we can't create a new one, we can't upload a file
if( empty( $galleryList ) && !$gBitUser->hasPermission( 'p_treasury_create_gallery' )) {
	$gBitSystem->fatalPermission( 'p_treasury_upload_item' );
}

if( !empty( $_REQUEST['content_id'] ) ) {
	$galleryContentIds[] = $_REQUEST['content_id'];
	$gBitSmarty->assign( 'galleryContentIds', $galleryContentIds );
}

if( !empty( $_REQUEST['treasury_store'] ) && !empty( $_FILES ) ) {
	// first of all set the execution time for this process to unlimited
	set_time_limit( 0 );

	$i = 0;
	foreach( $_FILES as $upload ) {
		if( !empty( $upload['tmp_name'] ) ) {
			// store each file individually
			$treasuryItem = new TreasuryItem();

			// transfer the form data to a store hash
			$storeHash = !empty( $_REQUEST['file'][$i] ) ? $_REQUEST['file'][$i] : array();

			// transfer galleryContentIds as well
			$storeHash['galleryContentIds'] = !empty( $_REQUEST['galleryContentIds'] ) ? $_REQUEST['galleryContentIds'] : array();

			// transfer plugin settings
			$storeHash['plugin'] = !empty( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : array();

			// add the file details to the store hash
			$storeHash['upload'] = $upload;

			if( !$treasuryItem->store( $storeHash ) ) {
				$feedback['error'] = $treasuryItem->mErrors;
			}
			$i++;
		}
		else {
		  $feedback['error'] = tr("There was an error uploading the file: ") . $upload['name'];
		}
	}

	if( empty( $feedback['error'] )) {
		if( $i > 1 ) {
			bit_redirect( TreasuryGallery::getDisplayUrl( $storeHash['galleryContentIds'][0] ));
		} else {
			bit_redirect( TreasuryItem::getDisplayUrl( $treasuryItem->mContentId ));
		}
	}
}

if( $gBitSystem->isPackageActive( 'gigaupload' ) ) {
	gigaupload_smarty_setup( TREASURY_PKG_URL.'upload.php' );
} elseif( $gBitSystem->isFeatureActive( 'treasury_extended_upload_slots' ) ) {
	$gBitThemes->loadAjax( 'mochikit' );
} else {
	$gBitThemes->loadJavascript( UTIL_PKG_PATH.'javascript/libs/multifile.js', TRUE );
}

$gContent->invokeServices( 'content_edit_function' );

$gBitSmarty->assign( 'feedback', !empty( $feedback ) ? $feedback : NULL );

$gBitSystem->display( 'bitpackage:treasury/upload.tpl', tra( 'Upload File' ) );
?>
