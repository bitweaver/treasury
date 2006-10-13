<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/view_item.php,v 1.6 2006/10/13 12:47:20 lsces Exp $
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
$gBitSystem->verifyPermission( 'p_treasury_view_item' );

require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
$extras = TRUE;
require_once( TREASURY_PKG_PATH.'item_lookup_inc.php');

// load the parent gallery as well
if( @BitBase::verifyId( $_REQUEST['structure_id'] ) ) {
	$gGallery = new TreasuryGallery( $_REQUEST['structure_id'] );
	$gGallery->load();
} else {
	// if we don't have a structure id to go by, we just get one
	$galleryContentIds = $gContent->getGalleriesFromItemContentId( $gContent->mContentId );
	if( @BitBase::verifyId( $galleryContentIds[0] ) ) {
		$gGallery = new TreasuryGallery( NULL, $galleryContentIds[0] );
		$gGallery->load();
	}
}

$galleryDisplayPath = $gContent->getDisplayPath( $gContent->getGalleryPath( $gGallery->mStructureId ) );
$gBitSmarty->assign( 'galleryDisplayPath', $galleryDisplayPath );
$gBitSmarty->assign_by_ref( 'gGallery', $gGallery );

$gBitSystem->display( "bitpackage:treasury/view_item.tpl", tra( "View File" ) );
?>
