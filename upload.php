<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/upload.php,v 1.33 2010/02/08 21:27:26 wjames5 Exp $
 *
 * @author       xing  <xing@synapse.plus.com>
 * @package      treasury
 * @copyright    2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/

/**
 * Setup
 */ 
require_once( '../kernel/setup_inc.php' );

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

if( !empty( $_REQUEST['treasury_store'] )) {
	$treasuryItem = new TreasuryItem();
	if( $treasuryItem->batchStore( $_REQUEST )) {
		bit_redirect( $_REQUEST['redirect'] );
	} else {
		vd( $treasuryItem->mErrors );
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

// get the ajax file browser working
if( $gBitSystem->isFeatureActive( 'treasury_file_import_path' ) && $gBitUser->hasPermission( 'p_treasury_import_item' )) {
	//$_REQUEST['ajax_path_conf'] = 'treasury_file_import_path';
	require_once( KERNEL_PKG_PATH.'ajax_file_browser_inc.php' );
}

$gBitSystem->display( 'bitpackage:treasury/upload.tpl', tra( 'Upload File' ) , array( 'display_mode' => 'upload' ));
?>
