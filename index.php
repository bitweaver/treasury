<?php
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );
$gBitSystem->verifyPermission( 'p_treasury_view_gallery' );

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php');
require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'gallery_lookup_inc.php' );

$listHash = $_REQUEST;
$listHash['load_only_root'] = TRUE;
$galleryList = $gContent->getList( $listHash );
if( @is_array( $galleryList ) && $gBitSystem->isFeatureActive( 'treasury_gallery_list_structure' ) ) {
	foreach( $galleryList as $key => $gallery ) {
		if( empty( $gStructure ) ) {
			$gStructure = new LibertyStructure();
		}
		$galleryList[$key]['subtree'] = $gStructure->getSubTree( $gallery['root_structure_id'] );
	}
}
$gBitSmarty->assign( 'galleryList', $galleryList );
$gBitSmarty->assign( 'listInfo', $listHash['listInfo'] );

$gBitSystem->display( 'bitpackage:treasury/list_galleries.tpl', tra( 'File Galleries' ) );
?>
