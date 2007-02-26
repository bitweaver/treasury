<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/item_lookup_inc.php,v 1.4 2007/02/26 16:30:35 squareing Exp $
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
	$gContent = new TreasuryItem( NULL, $_REQUEST['content_id'] );
	$gContent->load();
}

$gBitSmarty->assign_by_ref( 'gContent', $gContent );
?>
