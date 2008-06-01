<?php
/**
 * @author   xing <xing@synapse.plus.com>
 * @version  $Revision: 1.18 $
 * @package  Treasury
 * @subpackage functions
 */
global $gBitSystem, $gBitUser, $gBitSmarty, $gBitThemes;

$registerHash = array(
	'package_name' => 'treasury',
	'package_path' => dirname( __FILE__ ).'/',
	'homeable' => TRUE,
);
$gBitSystem->registerPackage( $registerHash );

define( 'TREASURY_CONTENT_TYPE_GUID', 'treasury' );

if( $gBitSystem->isPackageActive( 'treasury' ) ) {
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
