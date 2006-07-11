<?php
//$gExclusiveScan = array( 'kernel', 'users', 'themes', 'liberty', 'treasury' );
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );
$gBitSystem->verifyPermission( 'p_treasury_view_item' );
$gBitSystem->verifyPermission( 'p_treasury_download_item' );

require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'item_lookup_inc.php');

$download_function = $gTreasurySystem->getPluginFunction( $gContent->mInfo['plugin_guid'], 'download_function' );


// TODO: make sure this works reliably - not sure what is better:
//       - add hit after download is complete
//       - add hit as soon as user executes this script
//$gContent->addHit();

if( $download_function( $gContent->mInfo ) ) {
	// add hit if download was successful
	$gContent->addHit();
}

// make sure script stops here.
die;
?>
