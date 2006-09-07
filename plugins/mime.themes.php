<?php
/**
 * @version:     $Header: /cvsroot/bitweaver/_bit_treasury/plugins/mime.themes.php,v 1.4 2006/09/07 15:51:31 squareing Exp $
 *
 * @author:      xing  <xing@synapse.plus.com>
 * @version:     $Revision: 1.4 $
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
	'description'        => 'This plugin will extract any archive and will search for a file called <style>/style_info/preview.<ext> and will try to create a thumbnail from it.',
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

/**
 * Sanitise and validate data before it's stored
 * 
 * @param array $pStoreRow Hash of data that needs to be stored
 * @param array $pStoreRow['upload'] Hash passed in by $_FILES upload
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_theme_verify( &$pStoreRow ) {
	global $gBitSystem, $gBitUser;
	$ret = FALSE;
	// make sure the file is valid
	if( !empty( $pStoreRow['upload']['tmp_name'] ) && is_file( $pStoreRow['upload']['tmp_name'] ) ) {
		// if this is a theme, we'll extract the archive and look for the theme image found as <style>/style_info/preview.<ext>
		if( !empty( $pStoreRow['plugin']['is_style'] ) ) {
			if( $pStoreRow['ext_path'] = liberty_process_archive( $pStoreRow['upload'] ) ) {
				if( $preview = treasury_theme_extract_preview( $pStoreRow['ext_path'] ) ) {
					$pStoreRow['thumb']['name']     = basename( $preview );
					$pStoreRow['thumb']['tmp_name'] = $preview;
					$pStoreRow['thumb']['type']     = $gBitSystem->lookupMimeType( $preview );
					$pStoreRow['thumb']['error']    = 0;
				}

				// check to see if we have screenshots - limit them to 3 screenshots / theme
				if( $sshots = treasury_theme_extract_screenshots( $pStoreRow['ext_path'] ) ) {
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

				// if this is an icon theme, we should end up with a number of icons
				$pStoreRow['icons'] = treasury_theme_extract_icons( $pStoreRow['ext_path'] );

				/*
				if( $pStoreRow['icons'] = treasury_theme_extract_icons( $pStoreRow['ext_path'] ) ) {
					foreach( $icons as $key => $icon ) {
						$pStoreRow['icons']['icon'.$key]['name']     = basename( $icon );
						$pStoreRow['icons']['icon'.$key]['tmp_name'] = $icon;
					}
				}
				 */
			}
		}
	}

	// content_id is only set when we are updating the file
	if( @BitBase::verifyId( $pStoreRow['content_id'] ) ) {
		// Generic values needed by the storing mechanism
		$pStoreRow['user_id'] = $gBitUser->mUserId;
		$pStoreRow['upload']['source_file'] = $pStoreRow['upload']['tmp_name'];

		// Store all uploaded files in the users storage area
		// TODO: allow users to create personal galleries
		$fileInfo = $gBitSystem->mDb->getRow( "
			SELECT la.`attachment_id`, lf.`file_id`, lf.`storage_path`
			FROM `".BIT_DB_PREFIX."liberty_attachments` la
				INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON( la.`foreign_id`=lf.`file_id` )
			WHERE `content_id`=?", array( $pStoreRow['content_id'] ) );
		$pStoreRow = array_merge( $pStoreRow, $fileInfo );
		$pStoreRow['upload']['dest_path'] = LibertyAttachable::getStorageBranch( $pStoreRow['attachment_id'], $gBitUser->mUserId );

		$ret = TRUE;

	} elseif( !empty( $pStoreRow['upload']['tmp_name'] ) && is_file( $pStoreRow['upload']['tmp_name'] ) ) {
		// try to generate thumbnails for the upload
		//$pStoreRow['upload']['thumbnail'] = !$gBitSystem->isFeatureActive( 'liberty_offline_thumbnailer' );
		$pStoreRow['upload']['thumbnail'] = TRUE;

		// specify thumbnail sizes
		// keep in mind we only create a few sizes here. storage.bitfile.php 
		// assumes we have all sizes present. we need to update that to 
		// generate thumbs on demand
		$pStoreRow['upload']['thumbsizes'] = array( 'icon', 'avatar', 'small', 'medium' );

		// Generic values needed by the storing mechanism
		$pStoreRow['user_id'] = $gBitUser->mUserId;
		$pStoreRow['upload']['source_file'] = $pStoreRow['upload']['tmp_name'];

		// Store all uploaded files in the users storage area
		// TODO: allow users to create personal galleries
		$pStoreRow['attachment_id'] = $gBitSystem->mDb->GenID( 'liberty_attachments_id_seq' );
		$pStoreRow['upload']['dest_path'] = LibertyAttachable::getStorageBranch( $pStoreRow['attachment_id'], $gBitUser->mUserId );

		$ret = TRUE;

	} else {
		$pStoreRow['errors']['upload'] = tra( 'There was a problem with the uploaded file.' );
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
	global $gBitSystem;
	// No changes in the database are needed - we only need to update the uploaded files
	$query = "SELECT `storage_path` FROM `".BIT_DB_PREFIX."liberty_files` lf WHERE `file_id` = ?";
	if( $storage_path = $gBitSystem->mDb->getOne( $query, array( $pStoreRow['file_id'] ) ) ) {
		// First we remove the old file
		@unlink( BIT_ROOT_PATH.$storage_path );

		// Now we process the uploaded file
		if( $storagePath = liberty_process_upload( $pStoreRow ) ) {
			$sql = "UPDATE `".BIT_DB_PREFIX."liberty_files` SET `storage_path` = ? WHERE `file_id` = ?";
			$gBitSystem->mDb->query( $sql, array( $pStoreRow['upload']['dest_path'].$pStoreRow['upload']['name'], $pStoreRow['file_id'] ) );
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

		// now that all is done, we can remove temporarily extracted files
		if( !empty( $pStoreRow['ext_path'] ) ) {
			unlink_r( $pStoreRow['ext_path'] );
		}

		return TRUE;
	}
}

/**
 * Store the data in the database
 * 
 * @param array $pStoreRow File data needed to store details in the database - sanitised and generated in the verify function
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_theme_store( &$pStoreRow ) {
	global $gBitSystem;
	$ret = FALSE;
	// take care of the uploaded file and insert it into the liberty_files and liberty_attachments tables
	if( $storagePath = liberty_process_upload( $pStoreRow ) ) {
		// add row to liberty_files
		// this is where we store any additional data - we don't need more info for regular uploads
		$pStoreRow['file_id'] = $gBitSystem->mDb->GenID( 'liberty_files_id_seq' );
		$sql = "INSERT INTO `".BIT_DB_PREFIX."liberty_files` ( `storage_path`, `file_id`, `mime_type`, `file_size`, `user_id` ) VALUES ( ?, ?, ?, ?, ? )";
		$gBitSystem->mDb->query( $sql, array( $pStoreRow['upload']['dest_path'].$pStoreRow['upload']['name'], $pStoreRow['file_id'],  $pStoreRow['upload']['type'], $pStoreRow['upload']['size'], $pStoreRow['user_id'] ) );

		// this will insert the entry in the liberty_attachments table, making the upload availabe during wiki page editing - PLUGIN_GUID_BIT_FILES is basically the default file handler in liberty
		// hardcode this for now
		if( @include_once( LIBERTY_PKG_PATH.'plugins/storage.bitfile.php' ) ) {
			$sql = "INSERT INTO `".BIT_DB_PREFIX."liberty_attachments` ( `attachment_id`, `attachment_plugin_guid`, `content_id`, `foreign_id`, `user_id` ) VALUES ( ?, ?, ?, ?, ? )";
			$gBitSystem->mDb->query( $sql, array( $pStoreRow['attachment_id'], PLUGIN_GUID_BIT_FILES, $pStoreRow['content_id'], $pStoreRow['file_id'], $pStoreRow['user_id'] ) );
		}

		// if this is a theme, we need to convert the preview image into thumbnails
		if( !empty( $pStoreRow['thumb'] ) ) {
			$pStoreRow['thumb']['source_file']    = $pStoreRow['thumb']['tmp_name'];
			$pStoreRow['thumb']['dest_path']      = $pStoreRow['upload']['dest_path'];
			$pStoreRow['thumb']['dest_base_name'] = $pStoreRow['thumb']['name'];
			$pStoreRow['thumb']['thumbsizes']     = $pStoreRow['upload']['thumbsizes'];
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
			mkdir( BIT_ROOT_PATH.$pStoreRow['upload']['dest_path'].'large' );
			foreach( $pStoreRow['icons'] as $icon ) {
				rename( $icon, BIT_ROOT_PATH.$pStoreRow['upload']['dest_path'].'large/'.basename( $icon ) );
			}
		}

		// now that all is done, we can remove temporarily extracted files
		if( !empty( $pStoreRow['ext_path'] ) ) {
			unlink_r( $pStoreRow['ext_path'] );
		}

		$ret = TRUE;
	} else {
		$pStoreRow['errors']['liberty_process'] = "There was a problem processing the file.";
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
	global $gBitSystem;
	$ret = FALSE;
	if( @BitBase::verifyId( $pFileHash['content_id'] ) ) {
		$query = "SELECT *
			FROM `".BIT_DB_PREFIX."liberty_attachments` la INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON ( lf.`file_id` = la.`foreign_id` )
			WHERE la.`content_id` = ?";
		if( $row = $gBitSystem->mDb->getRow( $query, array( $pFileHash['content_id'] ) ) ) {
			$canThumbFunc = liberty_get_function( 'can_thumbnail' );
			if( file_exists( BIT_ROOT_PATH.dirname( $row['storage_path'] ).'/small.jpg' ) ) {
				$pFileHash['thumbnail_url']['icon']   = BIT_ROOT_URL.dirname( $row['storage_path'] ).'/icon.jpg';
				$pFileHash['thumbnail_url']['avatar'] = BIT_ROOT_URL.dirname( $row['storage_path'] ).'/avatar.jpg';
				$pFileHash['thumbnail_url']['small']  = BIT_ROOT_URL.dirname( $row['storage_path'] ).'/small.jpg';
				$pFileHash['thumbnail_url']['medium'] = BIT_ROOT_URL.dirname( $row['storage_path'] ).'/medium.jpg';
				$pFileHash['thumbnail_url']['large']  = BIT_ROOT_URL.dirname( $row['storage_path'] ).'/large.jpg';
//			} elseif( $canThumbFunc( $row['mime_type'] ) ) {
//				$pFileHash['thumbnail_url']['icon']   = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
//				$pFileHash['thumbnail_url']['avatar'] = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
//				$pFileHash['thumbnail_url']['small']  = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
//				$pFileHash['thumbnail_url']['medium'] = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
//				$pFileHash['thumbnail_url']['large']  = LIBERTY_PKG_URL.'icons/generating_thumbnails.png';
			} else {
				$mime_thumbnail = LibertySystem::getMimeThumbnailURL( $row['mime_type'], substr( $row['storage_path'], strrpos( $row['storage_path'], '.' ) + 1 ) );
				$pFileHash['thumbnail_url']['icon']   = $mime_thumbnail;
				$pFileHash['thumbnail_url']['avatar'] = $mime_thumbnail;
				$pFileHash['thumbnail_url']['small']  = $mime_thumbnail;
				$pFileHash['thumbnail_url']['medium'] = $mime_thumbnail;
				$pFileHash['thumbnail_url']['large']  = $mime_thumbnail;
			}
			$pFileHash['filename']         = substr( $row['storage_path'], strrpos( $row['storage_path'], '/' ) + 1 );
			$pFileHash['source_file']      = BIT_ROOT_PATH.$row['storage_path'];
			$pFileHash['source_url']       = BIT_ROOT_URL.str_replace( '+', '%20', str_replace( '%2F', '/', urlencode( $row['storage_path'] ) ) );
			$pFileHash['mime_type']        = $row['mime_type'];
			$pFileHash['file_size']        = $row['file_size'];
			$pFileHash['attachment_id']    = $row['attachment_id'];
			$pFileHash['wiki_plugin_link'] = "{attachment id=".$row['attachment_id']."}";
			if( $sshots = treasury_theme_extract_screenshots( BIT_ROOT_PATH.dirname( $row['storage_path'] ) ) ) {
				for( $i = 0; $i < count( $sshots ); $i++ ) {
					$pFileHash['screenshots'][] = BIT_ROOT_URL.dirname( $row['storage_path'] ).'/'.basename( $sshots[$i] );
				}
			}

			if( $icons = treasury_theme_extract_icons( BIT_ROOT_PATH.dirname( $row['storage_path'] ) ) ) {
				$count = count( $icons );
				// get a maximum of 50 icons
				for( $i = 0; $i < 50; $i++ ) {
					$pFileHash['icons'][basename( $icons[$i] )] = BIT_ROOT_URL.dirname( $row['storage_path'] ).'/large/'.basename( $icons[$i] );
				}
				ksort( $pFileHash['icons'] );
			}

			$ret = TRUE;
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
	$ret = FALSE;

	header( "Accept-Ranges: bytes" );
	// this will get the browser to open the download dialogue - even when the 
	// browser could deal with the content type - not perfect, but works
	//header( "Content-Type: application/force-download" );
	header( "Content-type: ".$pFileHash['mime_type'] );
	header( "Content-Disposition: attachment; filename=".$pFileHash['filename'] );
	header( "Last-Modified: ".gmdate( "D, d M Y H:i:s", $pFileHash['last_modified'] )." GMT", true, 200 );
	header( "Content-Length: ".$pFileHash['file_size'] );
	header( "Content-Transfer-Encoding: binary" );

	// Check to see if the file actually exists
	if( is_readable( $pFileHash['source_file'] ) ) {
		readfile( $pFileHash['source_file'] );
		$ret = TRUE;
	} else {
		$pFileHash['errors'] = tra( 'No matching file found.' );
		header( "HTTP/1.1 404 Not Found" );
	}
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
	global $gBitSystem;
	$ret = FALSE;
	if( @BitBase::verifyId( $pParamHash['content_id'] ) ) {
		$ret = TRUE;

		$query = "SELECT *
			FROM `".BIT_DB_PREFIX."liberty_attachments` la INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON ( lf.`file_id` = la.`foreign_id` )
			WHERE la.`content_id` = ?";
		if( $row = $gBitSystem->mDb->getRow( $query, array( $pParamHash['content_id'] ) ) ) {
			// Make sure the storage path is pointing to a valid file
			if( is_file( BIT_ROOT_PATH.$row['storage_path'] ) ) {
				unlink_r( dirname( BIT_ROOT_PATH.$row['storage_path'] ) );
			}

			// Now remove all entries we made in the database - liberty_files and liberty_attachments
			$sql = "DELETE FROM `".BIT_DB_PREFIX."liberty_files` WHERE `file_id`=?";
			$gBitSystem->mDb->query( $sql, array( $row['foreign_id'] ) );
			$sql = "DELETE FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `attachment_id`=?";
			$gBitSystem->mDb->query( $sql, array( $row['attachment_id'] ) );
		}
	} else {
		$pParamHash['errors'] = tra( 'No valid content_id given.' );
	}
	return $ret;
}

/**
 * Extract style_info/preview.<ext> for theme icon
 * 
 * @param array $pPath Path to extracted archive
 * @access public
 * @return Path to preview image on success, FALSE on failure
 */
function treasury_theme_extract_preview( $pPath ) {
	static $ret;
	if( $dh = opendir( $pPath ) ) {
		while( FALSE !== ( $file = readdir( $dh ) ) ) {
			if( $file != '.' && $file != '..' ) {
				if( basename( $pPath ) == "style_info" && is_file( $pPath.'/'.$file ) && preg_match( "/^preview\.(png|gif|jpe?g)$/", $file ) ) {
					$ret = $pPath.'/'.$file;
				} elseif( is_dir( $pPath.'/'.$file ) ) {
					treasury_theme_extract_preview( $pPath.'/'.$file );
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
function treasury_theme_extract_screenshots( $pPath ) {
	static $ret;
	if( $dh = opendir( $pPath ) ) {
		while( FALSE !== ( $file = readdir( $dh ) ) ) {
			if( $file != '.' && $file != '..' ) {
				if( preg_match( "/^screenshot\d*.(png|gif|jpe?g)$/i", $file ) ) {
					$ret[] = $pPath.'/'.$file;
				} elseif( is_dir( $pPath.'/'.$file ) ) {
					treasury_theme_extract_screenshots( $pPath.'/'.$file );
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
function treasury_theme_extract_icons( $pPath ) {
	static $ret;
	if( $dh = opendir( $pPath ) ) {
		while( FALSE !== ( $file = readdir( $dh ) ) ) {
			if( preg_match( "/^[^\.]/", $file ) ) {
				if( basename( $pPath ) == "large" && preg_match( "/\.(png|gif|jpe?g)$/i", $file ) ) {
					$ret[] = $pPath.'/'.$file;
				} elseif( is_dir( $pPath.'/'.$file ) ) {
					treasury_theme_extract_icons( $pPath.'/'.$file );
				}
			}
		}
	}
	closedir( $dh );
	return( !empty( $ret ) ? $ret : FALSE );
}

?>
