<?php
global $gContent;

if( !@BitBase::verifyId( $_REQUEST['content_id'] ) ) {
	header( "Location:".TREASURY_PKG_URL );
} else {
	$gContent = new TreasuryItem( $_REQUEST['content_id'] );
	$gContent->load( !empty( $extras ) );
}

$gBitSmarty->assign_by_ref( 'gContent', $gContent );
?>
