<?php
/**
 * @author   xing <xing@synapse.plus.com>
 * @version  $Revision: 1.11 $
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

	$gBitSmarty->assign( 'gTreasurySystem', $gTreasurySystem );

	if( $gBitUser->hasPermission( 'p_treasury_view_gallery' ) ) {
		$menuHash = array(
			'package_name'  => TREASURY_PKG_NAME,
			'index_url'     => TREASURY_PKG_URL.'index.php',
			'menu_template' => 'bitpackage:treasury/menu_treasury.tpl',
		);
		$gBitSystem->registerAppMenu( $menuHash );
	}

	// use this as a temp solution until we can work out how to allow firefox to download files even with gzip enabled
	if( strpos( $_SERVER['PHP_SELF'], TREASURY_PKG_URL.'download' ) !== FALSE ) {
		$gBitSystem->setConfig( 'site_output_obzip', FALSE );
	}
}
?>
