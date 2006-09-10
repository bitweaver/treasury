<?php
//$gExclusiveScan = array( 'kernel', 'users', 'themes', 'liberty', 'treasury' );
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );

require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'item_lookup_inc.php');

// Make sure we have the correct permissions for this item to download it
$gContent->updateUserPermissions();
$gContent->hasGalleryPermissions( 'p_treasury_download_item', TRUE );
//$gBitSystem->fatalPermission( 'p_treasury_edit_gallery' );

// TODO: make sure this works reliably - not sure what is better:
//       - add hit after download is complete
//       - add hit as soon as user executes this script
//$gContent->addHit();

$download_function = $gTreasurySystem->getPluginFunction( $gContent->mInfo['plugin_guid'], 'download_function' );
if( $download_function( $gContent->mInfo ) ) {
	// add hit if download was successful
	$gContent->addHit();
} else {
	$gBitSystem->fatalError( $gContent->mInfo['errors'] );
}
?>
