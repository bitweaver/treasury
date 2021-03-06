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
require_once( '../kernel/includes/setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php' );
require_once( TREASURY_PKG_INCLUDE_PATH.'gallery_lookup_inc.php' );

$gContent->verifyUpdatePermission();

$verifyStructurePermission = 'p_treasury_update_gallery';
require_once( LIBERTY_PKG_INCLUDE_PATH.'structure_edit_inc.php' );

// we need to load some javascript and css for this page
$gBitThemes->loadCss( UTIL_PKG_PATH.'javascript/libs/mygosu/DynamicTree.css' );
if( $gSniffer->_browser_info['browser'] == 'ie' && $gSniffer->_browser_info['maj_ver'] == 5 ) {
	$gBitThemes->loadJavascript( UTIL_PKG_PATH.'javascript/libs/mygosu/ie5.js' );
}
$gBitThemes->loadJavascript( UTIL_PKG_PATH.'javascript/libs/mygosu/DynamicTreeBuilder.js' );

// Display the template
$gBitSystem->display( 'bitpackage:treasury/edit_gallery_structure.tpl', $gStructure->mInfo["title"] , array( 'display_mode' => 'edit' ));
?>
