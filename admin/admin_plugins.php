<?php
require_once( '../../bit_setup_inc.php' );
$gBitSystem->verifyPermission( 'p_admin' );

include_once( KERNEL_PKG_PATH.'simple_form_functions_lib.php' );

// this will clear out all plugin settings in the database.
if( !empty( $_REQUEST['reset_all_plugins'] )) {
	$gTreasurySystem->resetAllPluginSettings();
	// reload page that everything is displayed as it actually is
	bit_redirect( TREASURY_PKG_URL."admin/admin_plugins.php" );
}

$gTreasurySystem->scanAllPlugins( NULL, "mime\." );

$feedback = array();
if( !empty( $_REQUEST['pluginsave'] )) {
	$gTreasurySystem->setActivePlugins( $_REQUEST['plugins'] );

	// this will make sure we remove all kernel_config entries when no comments are desired
	if( empty( $_REQUEST['comments'] )) {
		$_REQUEST['comments'] = array();
	}

	foreach( array_keys( $gTreasurySystem->mPlugins ) as $guid ) {
		if( in_array( $guid, array_keys( $_REQUEST['comments'] ))) {
			$gBitSystem->storeConfig( "treasury_{$guid}_comments", 'y', TREASURY_PKG_NAME );
		} else {
			$gBitSystem->storeConfig( "treasury_{$guid}_comments", NULL, TREASURY_PKG_NAME );
		}
	}

	$feedback['success'] = tra( 'The plugins were successfully updated' );
}

// some flashvideo specific settings
if( $gTreasurySystem->isPluginActive( 'mime_flv' )) {
	if( function_exists( 'shell_exec' )) {
		$gBitSmarty->assign( 'ffmpeg_path', shell_exec( 'which ffmpeg' ));
	}

	if( !empty( $_REQUEST['plugin_settings'] )) {
		$flvSettings = array(
			'treasury_flv_ffmpeg_path' => array(
				'type'  => 'text',
			),
			'treasury_flv_video_rate' => array(
				'type'  => 'numeric',
			),
			'treasury_flv_audio_rate' => array(
				'type'  => 'numeric',
			),
			'treasury_flv_width' => array(
				'type'  => 'numeric',
			),
			'treasury_flv_default_size' => array(
				'type'  => 'numeric',
			),
		);

		$treasuries = array_merge( $flvSettings );
		foreach( $treasuries as $item => $data ) {
			if( $data['type'] == 'checkbox' ) {
				simple_set_toggle( $item, TREASURY_PKG_NAME );
			} elseif( $data['type'] == 'numeric' ) {
				simple_set_int( $item, TREASURY_PKG_NAME );
			} else {
				$gBitSystem->storeConfig( $item, ( !empty( $_REQUEST[$item] ) ? $_REQUEST[$item] : NULL ), TREASURY_PKG_NAME );
			}
		}

		$feedback['success'] = tra( 'The plugins were successfully updated' );
	}
}
$gBitSmarty->assign( 'feedback', $feedback );

$gBitSystem->display( 'bitpackage:treasury/admin_plugins.tpl', tra( 'Plugins' ));
?>
