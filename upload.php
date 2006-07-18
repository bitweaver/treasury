<?php
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php');
require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'gallery_lookup_inc.php');

if( $gContent->loadPermissions() ) {
	$gContent->hasUserPermission( 'p_treasury_upload_item', TRUE, tra( 'You do not have the required permissions to upload files into this gallery.' ) );
} else {
	$gBitSystem->verifyPermission( 'p_treasury_upload_item' );
}

require_once( LIBERTY_PKG_PATH.'calculate_max_upload_inc.php' );

// turn the max_file_size value into megabytes
$gBitSmarty->assign_by_ref( 'feedback', $feedback = array() );

$listHash['load_only_root'] = TRUE;
$listHash['max_records']    = -1;
$galleryList = $gContent->getList( $listHash );

if( @is_array( $galleryList ) ) {
	foreach( $galleryList as $key => $gallery ) {
		if( empty( $gStructure ) ) {
			$gStructure = new LibertyStructure();
		}
		$galleryList[$key]['subtree'] = $gStructure->getSubTree( $gallery['root_structure_id'] );
	}
}
$gBitSmarty->assign( 'galleryList', $galleryList );

if( !empty( $_REQUEST['content_id'] ) ) {
	$galleryContentIds[] = $_REQUEST['content_id'];
	$gBitSmarty->assign( 'galleryContentIds', $galleryContentIds );
}

if( !empty( $_REQUEST['treasury_store'] ) && !empty( $_FILES ) ) {
	// transfer the form data to a store hash
	$storeHash = !empty( $_REQUEST['treasury'] ) ? $_REQUEST['treasury'] : array();

	// store each file individually
	$treasuryItem = new TreasuryItem();
	foreach( $_FILES as $upload ) {
		if( !empty( $upload['tmp_name'] ) ) {
			// add the file details to the store hash
			$storeHash['upload'] = $upload;
			if( !$treasuryItem->store( $storeHash ) ) {
				$feedback['error'] = $treasuryItem->mErrors;
			}
		}
	}

	if( empty( $feedback['error'] ) ) {
		header( 'Location: '.TREASURY_PKG_URL );
die;
	}
}

if( $gBitSystem->isPackageActive( 'gigaupload' ) ) {
	gigaupload_smarty_setup( FISHEYE_PKG_URL.'upload.php' );
} else {
	$gBitSmarty->assign( 'loadMultiFile', TRUE );
}

$gBitSmarty->assign( 'feedback', !empty( $feedback ) ? $feedback : NULL );

$gBitSystem->display( 'bitpackage:treasury/upload.tpl', tra( 'Upload File' ) );
?>
