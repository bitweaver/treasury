<?php
require_once( '../kernel/setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php');
require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_INCLUDE_PATH.'gallery_lookup_inc.php' );

$gContent->verifyViewPermission();

$listHash = $_REQUEST;
$listHash['get_sub_tree'] = TRUE;
$listHash['object_permission'] = 'p_treasury_view_gallery';
$galleryList = $gContent->getList( $listHash );
$gBitSmarty->assign( 'galleryList', $galleryList );
$gBitSmarty->assign( 'listInfo', $listHash['listInfo'] );

$gBitSystem->display( 'bitpackage:treasury/list_galleries.tpl', tra( 'File Galleries' ) , array( 'display_mode' => 'list' ));
?>
