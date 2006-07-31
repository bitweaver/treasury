<?php
require_once( '../../bit_setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

$gTreasurySystem->scanAllPlugins( TREASURY_PKG_PATH.'plugins/' );

$feedback = array();
if( !empty( $_REQUEST['pluginsave'] ) ) {
	$gTreasurySystem->setActivePlugins( $_REQUEST['plugins'] );
	$feedback['success'] = tra( 'The plugins were successfully updated' );
}
$gBitSmarty->assign( 'feedback', $feedback );

//vd($gTreasurySystem->mPlugins);

$gBitSystem->display( 'bitpackage:treasury/admin_plugins.tpl', tra( 'Plugins' ) );
?>
