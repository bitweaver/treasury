<?php
global $feedback;

// some flashvideo specific settings
if( $gTreasurySystem->isPluginActive( 'mime_flv' )) {
	if( function_exists( 'shell_exec' )) {
		$gBitSmarty->assign( 'ffmpeg_path', shell_exec( 'which ffmpeg' ));
	}

	$rates = array(
		'video_bitrate' => array(
			160000 => 200,
			240000 => 300,
			320000 => 400,
			400000 => 500,
		),
		'video_width' => array(
			240 => 240,
			320 => 320,
			480 => 480,
			640 => 640,
		),
		'display_size' => array(
			0 => tra( 'Same as encoded video' ),
			240 => tra( 'Small' ),
			320 => tra( 'Medium' ),
			480 => tra( 'Large' ),
			640 => tra( 'Huge' ),
		),
		'audio_bitrate' => array(
			16 => 16,
			32 => 32,
			64 => 64,
			96 => 96,
		),
		'audio_samplerate' => array(
			11025 => 11025,
			22050 => 22050,
			44100 => 44100,
		),
	);
	$gBitSmarty->assign( 'rates', $rates );

	if( !empty( $_REQUEST['plugin_settings'] )) {
		$flvSettings = array(
			'treasury_flv_ffmpeg_path' => array(
				'type'  => 'text',
			),
			'treasury_flv_video_bitrate' => array(
				'type'  => 'numeric',
			),
			'treasury_flv_audio_samplerate' => array(
				'type'  => 'numeric',
			),
			'treasury_flv_audio_bitrate' => array(
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
