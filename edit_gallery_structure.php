<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/edit_gallery_structure.php,v 1.2 2006/10/13 12:47:20 lsces Exp $
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
$gBitSystem->verifyPermission( 'p_treasury_edit_gallery' );

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php' );
require_once( TREASURY_PKG_PATH.'gallery_lookup_inc.php' );

$verifyStructurePermission = 'p_treasury_edit_gallery';
require_once( LIBERTY_PKG_PATH.'edit_structure_inc.php' );

$gBitSmarty->assign( 'loadDynamicTree', TRUE );

// Display the template
$gBitSystem->display( 'bitpackage:treasury/edit_gallery_structure.tpl', $gStructure->mInfo["title"] );
?>
