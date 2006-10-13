<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/Attic/list.php,v 1.2 2006/10/13 12:47:20 lsces Exp $
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
$gBitSystem->verifyPermission( 'p_treasury_view_gallery' );

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php');
require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'gallery_lookup_inc.php');

$listHash = $_REQUEST;
$galleryList = $gContent->getList( $listHash );
$gBitSmarty->assign( 'galleryList', $galleryList );
$gBitSmarty->assign( 'listInfo', $listHash['listInfo'] );
?>
