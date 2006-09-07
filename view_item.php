<?php
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );
$gBitSystem->verifyPermission( 'p_treasury_view_item' );

require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
$extras = TRUE;
require_once( TREASURY_PKG_PATH.'item_lookup_inc.php');

if( @BitBase::verifyId( $_REQUEST['structure_id'] ) ) {
	$galleryDisplayPath = $gContent->getDisplayPath( $gContent->getGalleryPath( $_REQUEST['structure_id'] ) );
	$gBitSmarty->assign( 'galleryDisplayPath', $galleryDisplayPath );
} else {
	// if we don't have a structure id to go by, we just get one
	$galleryContentIds = $gContent->getGalleriesFromItemContentId( $gContent->mContentId );
	if( @BitBase::verifyId( $galleryContentIds[0] ) ) {
		$gallery = new TreasuryGallery( $galleryContentIds[0] );
		$galleryDisplayPath = $gContent->getDisplayPath( $gContent->getGalleryPath( $gallery->mStructureId ) );
		$gBitSmarty->assign( 'galleryDisplayPath', $galleryDisplayPath );
	}
}

$gBitSystem->display( "bitpackage:treasury/view_item.tpl", tra( "View File" ) );
?>
