<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/item_lookup_inc.php,v 1.3 2006/12/15 20:47:05 squareing Exp $
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

if( !@BitBase::verifyId( $_REQUEST['content_id'] ) ) {
	header( "Location:".TREASURY_PKG_URL );
} else {
	$gContent = new TreasuryItem( $_REQUEST['content_id'] );
	$gContent->load();
}

$gBitSmarty->assign_by_ref( 'gContent', $gContent );
?>
