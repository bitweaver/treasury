<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/view_item.php,v 1.10 2007/03/02 09:32:09 lugie Exp $
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

require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'item_lookup_inc.php');

// check view permission as set for the gallery
$gContent->hasGalleryPermissions( 'p_treasury_view_item', TRUE );

if( empty( $gContent->mInfo ) ) {
	$gBitSystem->fatalError( "The requested file could not be found" );
}

// load the parent gallery as well
if( @BitBase::verifyId( $_REQUEST['structure_id'] ) ) {
	$gGallery = new TreasuryGallery( $_REQUEST['structure_id'] );
	$gGallery->load();
} else {
	// if we don't have a structure id to go by, we just get a gallery we can work with
	$galleryContentIds = $gContent->getGalleriesFromItemContentId( $gContent->mContentId );
	if( @BitBase::verifyId( $galleryContentIds[0] ) ) {
		$gGallery = new TreasuryGallery( NULL, $galleryContentIds[0] );
		$gGallery->load();
	}
}

$galleryDisplayPath = $gContent->getDisplayPath( $gContent->getGalleryPath( $gGallery->mStructureId ) );
$gBitSmarty->assign( 'galleryDisplayPath', $galleryDisplayPath );
$gBitSmarty->assign_by_ref( 'gGallery', $gGallery );

if( is_object( $gGallery ) && $gBitSystem->isFeatureActive('treasury_item_view_comments') == 'y' ) {
	$commentsParentId = $gContent->mContentId;
	$comments_vars = Array('treasuryitem');
	$comments_prefix_var='treasuryitem:';
	$comments_object_var='treasuryitem';
	$comments_return_url = $_SERVER['PHP_SELF']."?content_id=".$gContent->mContentId;
	include_once( LIBERTY_PKG_PATH.'comments_inc.php' );
}

$gBitSystem->display( "bitpackage:treasury/view_item.tpl", tra( "View File" ) );
?>
