<?php
/**
 * @author   xing <xing@synapse.plus.com>
 * @version  $Revision: 1.3 $
 * @package  Treasury
 * @subpackage functions
 */
global $gBitSystem, $gBitUser, $gBitSmarty;

$registerHash = array(
	'package_name' => 'treasury',
	'package_path' => dirname( __FILE__ ).'/',
);
$gBitSystem->registerPackage( $registerHash );

define( 'TREASURY_CONTENT_TYPE_GUID', 'treasury' );

if( $gBitSystem->isPackageActive( 'treasury' ) ) {
	// set up treasury system
	require_once( TREASURY_PKG_PATH.'TreasurySystem.php' );
	global $gTreasurySystem;
	$gTreasurySystem = new TreasurySystem();
	$gTreasurySystem->loadActivePlugins();
	$gBitSmarty->assign( 'gTreasurySystem', $gTreasurySystem );
	//vd($gTreasurySystem);

	if( $gBitUser->hasPermission( 'p_treasury_view_gallery' ) ) {
		$gBitSystem->registerAppMenu( TREASURY_PKG_DIR, $gBitSystem->getConfig( 'treasury_menu_text', ucfirst( TREASURY_PKG_DIR ) ), TREASURY_PKG_URL.'index.php', 'bitpackage:treasury/menu_treasury.tpl', 'Treasury' );
	}
}
?>
