<?php
global $feedback;

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
			'treasury_flv_backcolor' => array(
				'type'  => 'text',
			),
			'treasury_flv_frontcolor' => array(
				'type'  => 'text',
			),
		);

		foreach( $flvSettings as $item => $data ) {
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
?>
