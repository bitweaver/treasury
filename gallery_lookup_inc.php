<?php
global $gContent;

if( @BitBase::verifyId( $_REQUEST['structure_id'] ) ) {
	$gContent = new TreasuryGallery( $_REQUEST['structure_id'] );
	$gContent->load( TRUE );
} elseif( @BitBase::verifyId( $_REQUEST['content_id'] ) ) {
	$gContent = new TreasuryGallery( NULL, $_REQUEST['content_id'] );
	$gContent->load( TRUE );
} else {
	$gContent = new TreasuryGallery();
}

$gBitSmarty->assign_by_ref( 'gContent', $gContent );
?>
