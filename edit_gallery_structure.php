<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/edit_gallery_structure.php,v 1.3 2007/07/15 11:46:02 squareing Exp $
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

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php' );
require_once( TREASURY_PKG_PATH.'gallery_lookup_inc.php' );

$gContent->verifyPermission( 'p_treasury_edit_gallery' );

$verifyStructurePermission = 'p_treasury_edit_gallery';
require_once( LIBERTY_PKG_PATH.'edit_structure_inc.php' );

$gBitSmarty->assign( 'loadDynamicTree', TRUE );

// Display the template
$gBitSystem->display( 'bitpackage:treasury/edit_gallery_structure.tpl', $gStructure->mInfo["title"] );
?>
