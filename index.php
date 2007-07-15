<?php
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php');
require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'gallery_lookup_inc.php' );

$gContent->verifyPermission( 'p_treasury_view_gallery' );

$listHash = $_REQUEST;
$listHash['get_sub_tree'] = TRUE;
$listHash['object_permission'] = 'p_treasury_view_gallery';
$galleryList = $gContent->getList( $listHash );
$gBitSmarty->assign( 'galleryList', $galleryList );
$gBitSmarty->assign( 'listInfo', $listHash['listInfo'] );

$gBitSystem->display( 'bitpackage:treasury/list_galleries.tpl', tra( 'File Galleries' ) );
?>
