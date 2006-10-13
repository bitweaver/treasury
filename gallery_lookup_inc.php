<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/gallery_lookup_inc.php,v 1.3 2006/10/13 12:47:20 lsces Exp $
 *
 * @author       xing  <xing@synapse.plus.com>
 * @package      treasury
 * @copyright    2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/

/**
 * Setup
 */ 
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
