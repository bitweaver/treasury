<?php
require_once( '../../kernel/setup_inc.php' );
include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

$gBitSystem->verifyPermission( 'p_admin' );

$settings = array(
	'mime_import_file_import_path' => array(
		'label' => 'Root Import Directory',
		'note' => 'Absolute path to directory you want to use as base to import files from. <strong>For security reasons this has to be the true path and can not have any symbolic links in it. <em>Requires trailing slash</em></strong>. e.g.: /home/ftp/public/',
		'type' => 'text',
	),
);
$gBitSmarty->assign( 'settings', $settings );

$feedback = array();
if( !empty( $_REQUEST['settings_store'] )) {
	foreach( $settings as $item => $data ) {
		if( $data['type'] == 'checkbox' ) {
			simple_set_toggle( $item, TREASURY_PKG_NAME );
		} elseif( $data['type'] == 'numeric' ) {
			simple_set_int( $item, TREASURY_PKG_NAME );
		} else {
			if( $item == 'mime_import_file_import_path' ) {
				if( is_dir( $_REQUEST[$item] )) {
					$gBitSystem->storeConfig( $item, str_replace( "//", "/", $_REQUEST[$item]."/" ), TREASURY_PKG_NAME );
				} elseif( empty( $_REQUEST[$item] )) {
					$gBitSystem->storeConfig( $item, NULL, TREASURY_PKG_NAME );
				} else {
					$feedback['error'] = "You did not specify a valid path.";
				}
			} else {
				$gBitSystem->storeConfig( $item, ( !empty( $_REQUEST[$item] ) ? $_REQUEST[$item] : NULL ), TREASURY_PKG_NAME );
			}
		}
	}
}
$gBitSmarty->assign( 'feedback', $feedback );
$gBitSystem->display( 'bitpackage:treasury/admin_mime_import.tpl', tra( 'Import Plugin Settings' ), array( 'display_mode' => 'admin' ));
?>
