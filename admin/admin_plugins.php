<?php
require_once( '../../bit_setup_inc.php' );
$gBitSystem->verifyPermission( 'p_admin' );

include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

// this will clear out all plugin settings in the database.
if( !empty( $_REQUEST['reset_all_plugins'] ) ) {
	$gTreasurySystem->resetAllPluginSettings();
	// reload page that everything is displayed as it actually is
	header( "Location: ".TREASURY_PKG_URL."admin/admin_plugins.php" );
}

$gTreasurySystem->scanAllPlugins( TREASURY_PKG_PATH.'plugins/' );

$feedback = array();
if( !empty( $_REQUEST['pluginsave'] ) ) {
	$gTreasurySystem->setActivePlugins( $_REQUEST['plugins'] );
	$feedback['success'] = tra( 'The plugins were successfully updated' );
}
$gBitSmarty->assign( 'feedback', $feedback );

$gBitSystem->display( 'bitpackage:treasury/admin_plugins.tpl', tra( 'Plugins' ) );
?>
