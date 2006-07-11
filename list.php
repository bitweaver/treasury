<?php
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );
$gBitSystem->verifyPermission( 'p_treasury_view_gallery' );

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php');
require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'gallery_lookup_inc.php');

$listHash = $_REQUEST;
$galleryList = $gContent->getList( $listHash );
$gBitSmarty->assign( 'galleryList', $galleryList );
$gBitSmarty->assign( 'listInfo', $listHash['listInfo'] );
?>
