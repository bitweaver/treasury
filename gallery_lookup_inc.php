<?php
/**
 * @version      $Header$
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

$gBitSmarty->assignByRef( 'gContent', $gContent );
