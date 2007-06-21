<?php
/**
 * @author   xing <xing@synapse.plus.com>
 * @version  $Revision: 1.15 $
 * @package  Treasury
 * @subpackage functions
 */
global $gBitSystem, $gBitUser, $gBitSmarty;

$registerHash = array(
	'package_name' => 'treasury',
	'package_path' => dirname( __FILE__ ).'/',
	'homeable' => TRUE,
);
$gBitSystem->registerPackage( $registerHash );

define( 'TREASURY_CONTENT_TYPE_GUID', 'treasury' );

if( $gBitSystem->isPackageActive( 'treasury' ) ) {
	// set up treasury system
	require_once( TREASURY_PKG_PATH.'TreasurySystem.php' );
	global $gTreasurySystem;
	$gTreasurySystem = new TreasurySystem();
	$plugin_status = $gBitSystem->getConfig( TREASURY_PKG_NAME.'_plugin_status_'.TREASURY_DEFAULT_MIME_HANDLER );
	if( empty( $plugin_status ) || $plugin_status != 'y' ) {
		$gTreasurySystem->scanAllPlugins( NULL, "mime\." );
	} else {
		$gTreasurySystem->loadActivePlugins();
	}

	$gBitSmarty->assign_by_ref( 'gTreasurySystem', $gTreasurySystem );

	if( $gBitUser->hasPermission( 'p_treasury_view_gallery' ) ) {
		$menuHash = array(
			'package_name'       => TREASURY_PKG_NAME,
			'index_url'          => TREASURY_PKG_URL.'index.php',
			'menu_template'      => 'bitpackage:treasury/menu_treasury.tpl',
			'admin_comments_url' => TREASURY_PKG_URL.'admin/admin_plugins.php',
		);
		$gBitSystem->registerAppMenu( $menuHash );
	}
}
?>
