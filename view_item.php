<?php
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );
$gBitSystem->verifyPermission( 'p_treasury_view_item' );

require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
$extras = TRUE;
require_once( TREASURY_PKG_PATH.'item_lookup_inc.php');

if( !empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'remove' || !empty( $_REQUEST['confirm'] ) ) {
	if( !$gContent->isOwner() && !$gBitUser->isAdmin() ) {
		$gBitSmarty->assign( 'msg', tra( "You do not own this file." ) );
		$gBitSystem->display( "error.tpl" );
		die;
	}

	if( @BitBase::verifyId( $_REQUEST['content_id'] ) ) {
		if( !empty( $_REQUEST['confirm'] ) ) {
			if( $gContent->expunge( !empty( $_REQUEST['force_item_delete'] ) ) ) {
				header( "Location: ".TREASURY_PKG_URL );
				die;
			} else {
				$feedback['errors'] = $gContent->mErrors;
			}
		}
		$gBitSystem->setBrowserTitle( 'Confirm removal of '.$gContent->mInfo['title'] );
		$formHash['remove'] = TRUE;
		$formHash['content_id'] = $_REQUEST['content_id'];
		$formHash['action'] = 'remove';
//		$formHash['input'] = array(
//			'<label><input name="force_item_delete" value="" type="radio" checked="checked" /> '.tra( "Delete file only if it doesn't appear in other galleries." ).'</label>',
//			'<label><input name="force_item_delete" value="true" type="radio" /> '.tra( "Permanently delete file, even if it appears in other galleries." ).'</label>',
//		);
		$msgHash = array(
			'label' => 'Remove File',
			'confirm_item' => $gContent->mInfo['title'],
			'warning' => 'This will permanently remove the file.',
		);
		$gBitSystem->confirmDialog( $formHash, $msgHash );
	} else {
		$feedback['error'] = tra( 'No valid gallery content id given.' );
	}
}

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
