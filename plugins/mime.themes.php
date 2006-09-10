<?php
/**
 * @version:     $Header: /cvsroot/bitweaver/_bit_treasury/plugins/mime.themes.php,v 1.8 2006/09/10 09:20:55 squareing Exp $
 *
 * @author:      xing  <xing@synapse.plus.com>
 * @version:     $Revision: 1.8 $
 * @created:     Sunday Jul 02, 2006   14:42:13 CEST
 * @package:     treasury
 * @subpackage:  treasury_mime_handler
 **/

global $gTreasurySystem;

// This is the name of the plugin - max char length is 16
// As a naming convention, the treasury mime handler definition should start with:
// TREASURY_MIME_GUID_
define( 'TREASURY_MIME_GUID_THEME', 'mime_theme' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'    => 'treasury_theme_verify',
	'store_function'     => 'treasury_theme_store',
	'update_function'    => 'treasury_theme_update',
	'load_function'      => 'treasury_theme_load',
	'download_function'  => 'treasury_theme_download',
	'expunge_function'   => 'treasury_theme_expunge',
	// Brief description of what the plugin does
	'title'              => 'Theme Mime Handler',
	'description'        => 'This plugin will extract any archive and will search for a file called <style>/style_info/preview.<ext> and will try to create a thumbnail from it. Requires the default plugin file to be present.',
	// Template used when viewing the item
	'view_tpl'           => 'bitpackage:treasury/view_theme_inc.tpl',
	// This should be the same for all mime plugins
	'plugin_type'        => TREASURY_MIME,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'      => FALSE,
	// TODO: allow archive processing and create galleries according to 
	// hierarchy of extracted files
	// Allow for additional processing options - passed in during verify and store
	'processing_options' => 
		'<label>
			<input type="checkbox" name="treasury[plugin][is_style]" value="true" /> '.
			tra( 'Check this box if you are uploading a bitweaver theme. Please view <a href="/wiki/Style+Uploads">Style Uploads</a> for details.' ).
		'</label>',
	// this should pick up all common archives
	'mimetypes'          => array(
		'#application/[a-z\-]*(rar|zip|tar|tgz|stuffit)[a-z\-]*#i',
	),
);

$gTreasurySystem->registerPlugin( TREASURY_MIME_GUID_THEME, $pluginParams );

// depending on the scan the default file might not be included yet. we need get it manually
require_once( 'mime.default.php' );

/**
 * Sanitise and validate data before it's stored
 * 
 * @param array $pStoreRow Hash of data that needs to be stored
 * @param array $pStoreRow['upload'] Hash passed in by $_FILES upload
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_theme_verify( &$pStoreRow ) {
	global $gBitSystem;
	$ret = treasury_default_verify( $pStoreRow );

	// If all of that went well, we're ready to do the style thing
	if( $ret ) {
		// if this is a theme, we'll extract the archive and look for the theme image found as <style>/style_info/preview.<ext>
		if( !empty( $pStoreRow['plugin']['is_style'] ) ) {
			if( $pStoreRow['ext_path'] = liberty_process_archive( $pStoreRow['upload'] ) ) {
				if( $preview = treasury_theme_get_preview( $pStoreRow['ext_path'] ) ) {
					$pStoreRow['thumb']['name']     = basename( $preview );
					$pStoreRow['thumb']['tmp_name'] = $preview;
					$pStoreRow['thumb']['type']     = $gBitSystem->lookupMimeType( $preview );
					$pStoreRow['thumb']['error']    = 0;
				}

				// check to see if we have screenshots - limit them to 3 screenshots / theme
				if( $sshots = treasury_theme_get_screenshots( $pStoreRow['ext_path'] ) ) {
					$i = 0;
					foreach( $sshots as $key => $sshot ) {
						if( $i < 3 ) {
							$pStoreRow['screenshots']['screenshot'.$key]['name']     = 'screenshot'.$key;
							$pStoreRow['screenshots']['screenshot'.$key]['tmp_name'] = $sshot;
							$pStoreRow['screenshots']['screenshot'.$key]['type']     = $gBitSystem->lookupMimeType( $sshot );
							$pStoreRow['screenshots']['screenshot'.$key]['error']    = 0;
							$i++;
						}
					}
				}

				// if this is an icon style, we should end up with a number of icons
				$pStoreRow['icons'] = treasury_theme_get_icons( $pStoreRow['ext_path'] );
			}
		}
	}

	return $ret;
}

/**
 * When a file is edited
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_theme_update( &$pStoreRow ) {
	$ret = treasury_default_update( $pStoreRow );

	// if that all went well, we can do our style thing
	if( $ret ) {
		treasury_theme_process_extracted_files( $pStoreRow );
	}
	return $ret;
}

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_theme_store( &$pStoreRow ) {
	$ret = treasury_default_store( $pStoreRow );

	// if that all went well, we can do our style thing
	if( $ret ) {
		treasury_theme_process_extracted_files( $pStoreRow );
	}
	return $ret;
}

/**
 * Load file data from the database
 * 
 * @param array $pRow 
 * @access public
 * @return TRUE on success, FALSE on failure - ['errors'] will contain reason for failure
 */
function treasury_theme_load( &$pFileHash ) {
	$ret = treasury_default_load( $pFileHash );
	if( $ret ) {
		// get extra stuff such as screenshots and icons
		if( $sshots = treasury_theme_get_screenshots( dirname( $pFileHash['source_file'] ) ) ) {
			for( $i = 0; $i < count( $sshots ); $i++ ) {
				$pFileHash['screenshots'][] = dirname( $pFileHash['source_file'] ).'/'.basename( $sshots[$i] );
			}
		}

		if( $icons = treasury_theme_get_icons( dirname( $pFileHash['source_file'] ), 'icons' ) ) {
			$count = count( $icons );
			// get a maximum of 50 icons
			for( $i = 0; $i < 50; $i++ ) {
				$pFileHash['icons'][basename( $icons[$i] )] = dirname( $pFileHash['source_url'] ).'/icons/'.basename( $icons[$i] );
			}
			ksort( $pFileHash['icons'] );
		}
	}
	return $ret ;
}

/**
 * Takes care of the entire download process. Make sure it doesn't die at the end.
 * in this functioin it would be possible to add download resume possibilites and the like
 * 
 * @param array $pFileHash Basically the same has as returned by the load function
 * @access public
 * @return TRUE on success, FALSE on failure - $pParamHash['errors'] will contain reason for failure
 */
function treasury_theme_download( &$pFileHash ) {
	$ret = treasury_default_download( $pStoreRow );
	return $ret;
}

/**
 * Nuke data in tables when content is removed
 * 
 * @param array $pParamHash The contents of TreasuryItem->mInfo
 * @access public
 * @return TRUE on success, FALSE on failure - $pParamHash['errors'] will contain reason for failure
 */
function treasury_theme_expunge( &$pParamHash ) {
	$ret = treasury_default_expunge( $pStoreRow );
	return $ret;
}

/**
 * Extract style_info/preview.<ext> for theme icon
 * 
 * @param array $pPath Path to extracted archive
 * @access public
 * @return Path to preview image on success, FALSE on failure
 */
function treasury_theme_get_preview( $pPath ) {
	static $ret;
	if( $dh = opendir( $pPath ) ) {
		while( FALSE !== ( $file = readdir( $dh ) ) ) {
			if( $file != '.' && $file != '..' ) {
				if( basename( $pPath ) == "style_info" && is_file( $pPath.'/'.$file ) && preg_match( "/^preview\.(png|gif|jpe?g)$/", $file ) ) {
					$ret = $pPath.'/'.$file;
				} elseif( is_dir( $pPath.'/'.$file ) ) {
					treasury_theme_get_preview( $pPath.'/'.$file );
				}
			}
		}
	}
	closedir( $dh );
	return( !empty( $ret ) ? $ret : FALSE );
}

/**
 * Extract screenshots found in the theme archive
 * 
 * @param array $pPath Path to extracted archive
 * @access public
 * @return Path to preview image on success, FALSE on failure
 */
function treasury_theme_get_screenshots( $pPath ) {
	static $ret;
	if( $dh = opendir( $pPath ) ) {
		while( FALSE !== ( $file = readdir( $dh ) ) ) {
			if( $file != '.' && $file != '..' ) {
				if( preg_match( "/^screenshot\d*.(png|gif|jpe?g)$/i", $file ) ) {
					$ret[] = $pPath.'/'.$file;
				} elseif( is_dir( $pPath.'/'.$file ) ) {
					treasury_theme_get_screenshots( $pPath.'/'.$file );
				}
			}
		}
	}
	closedir( $dh );
	return( !empty( $ret ) ? $ret : FALSE );
}

/**
 * Extract icons found in the theme archive
 * 
 * @param array $pPath Path to extracted archive
 * @access public
 * @return Path to preview image on success, FALSE on failure
 */
function treasury_theme_get_icons( $pPath, $pIconDir = 'large' ) {
	static $ret;
	if( $dh = opendir( $pPath ) ) {
		while( FALSE !== ( $file = readdir( $dh ) ) ) {
			if( preg_match( "/^[^\.]/", $file ) ) {
				if( basename( $pPath ) == $pIconDir && preg_match( "/\.(png|gif|jpe?g)$/i", $file ) ) {
					$ret[] = $pPath.'/'.$file;
				} elseif( is_dir( $pPath.'/'.$file ) ) {
					treasury_theme_get_icons( $pPath.'/'.$file, $pIconDir );
				}
			}
		}
	}
	closedir( $dh );
	return( !empty( $ret ) ? $ret : FALSE );
}

/**
 * All the files that have been extracted so far need to be processed and moved around
 * 
 * @param array $pStoreRow 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function treasury_theme_process_extracted_files( $pStoreRow ) {
	// if this is a theme, we need to convert the preview image into thumbnails
	if( !empty( $pStoreRow['thumb'] ) ) {
		$pStoreRow['thumb']['source_file']    = $pStoreRow['thumb']['tmp_name'];
		$pStoreRow['thumb']['dest_path']      = $pStoreRow['upload']['dest_path'];
		$pStoreRow['thumb']['dest_base_name'] = $pStoreRow['thumb']['name'];
		liberty_generate_thumbnails( $pStoreRow['thumb'] );
	}

	// if we have screenshots we better do something with them
	if( !empty( $pStoreRow['screenshots'] ) ) {
		foreach( $pStoreRow['screenshots'] as $key => $sshot ) {
			$resizeFunc = liberty_get_function( 'resize' );
			$sshot['source_file']       = $sshot['tmp_name'];
			$sshot['dest_base_name']    = $sshot['name'];
			$sshot['dest_path']         = $pStoreRow['upload']['dest_path'];
			$sshot['max_width']         = 400;
			$sshot['max_height']        = 300;
			$sshot['medium_thumb_path'] = BIT_ROOT_PATH.$resizeFunc( $sshot );
		}
	}

	// if we have icons, we should place them somewhere that we can display them
	if( !empty( $pStoreRow['icons'] ) ) {
		@mkdir( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path'].'icons' );
		foreach( $pStoreRow['icons'] as $icon ) {
			$dest = BIT_ROOT_PATH.$pStoreRow['upload']['dest_path'].'icons/'.basename( $icon );
			if( $icon != $dest ) {
				rename( $icon, $dest );
			}
		}
	}

	// now that all is done, we can remove temporarily extracted files
	if( !empty( $pStoreRow['ext_path'] ) ) {
		unlink_r( $pStoreRow['ext_path'] );
	}
}
?>
