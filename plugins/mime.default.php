<?php
/**
 * @version:     $Header: /cvsroot/bitweaver/_bit_treasury/plugins/Attic/mime.default.php,v 1.15 2006/09/10 15:37:35 squareing Exp $
 *
 * @author:      xing  <xing@synapse.plus.com>
 * @version:     $Revision: 1.15 $
 * @created:     Sunday Jul 02, 2006   14:42:13 CEST
 * @package:     treasury
 * @subpackage:  treasury_mime_handler
 **/

// TODO: since plugins can do just about anything here, we might need the 
// option to create specific tables during install. if required we can scan for 
// files called:
// table.plugin_guid.php
// where plugins can insert their own tables

global $gTreasurySystem;

// This is the name of the plugin - max char length is 16
// As a naming convention, the treasury mime handler definition should start with:
// TREASURY_MIME_GUID_
define( 'TREASURY_MIME_GUID_DEFAULT', 'mime_default' );

$pluginParams = array (
	// Set of functions and what they are called in this paricular plugin
	// Use the GUID as your namespace
	'verify_function'    => 'treasury_default_verify',
	'store_function'     => 'treasury_default_store',
	'update_function'    => 'treasury_default_update',
	'load_function'      => 'treasury_default_load',
	'download_function'  => 'treasury_default_download',
	'expunge_function'   => 'treasury_default_expunge',
	// Brief description of what the plugin does
	'title'              => 'Default Mime Handler',
	'description'        => 'This mime handler can handle any file type, creates thumbnails when possible and will make the file available as an attachment.',
	// Template used when viewing the item
	'view_tpl'           => 'bitpackage:treasury/view_item_inc.tpl',
	// This should be the same for all mime plugins
	'plugin_type'        => TREASURY_MIME,
	// Set this to TRUE if you want the plugin active right after installation
	'auto_activate'      => TRUE,
	// TODO: allow archive processing and create galleries according to 
	// hierarchy of extracted files
	// Allow for additional processing options - passed in during verify and store
	//'processing_options' => '<label><input type="checkbox" name="treasury[plugin][process_archives]" value="true" /> '.tra( 'Process Archives' ).'</label>',

	// Here you can use a perl regular expression to pick out file extensions you want to handle
	// e.g.: Some image types: '#^image/(jpe?g|gif|png)#i'
	// This plugin will be picked if nothing matches.
	//'mimetypes'          => array( '/.*/' ),
);

$gTreasurySystem->registerPlugin( TREASURY_MIME_GUID_DEFAULT, $pluginParams );

/**
 * Sanitise and validate data before it's stored
 * 
 * @param array $pStoreRow Hash of data that needs to be stored
 * @param array $pStoreRow['upload'] Hash passed in by $_FILES upload
 * @access public
 * @return TRUE on success, FALSE on failure - $pStoreRow['errors'] will contain reason
 */
function treasury_default_verify( &$pStoreRow ) {
	global $gBitSystem, $gBitUser;
	$ret = FALSE;

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
function treasury_default_update( &$pStoreRow ) {
	global $gBitSystem;

	// get the data we need to deal with
	$query = "SELECT `storage_path` FROM `".BIT_DB_PREFIX."liberty_files` lf WHERE `file_id` = ?";
	if( $storage_path = $gBitSystem->mDb->getOne( $query, array( $pStoreRow['file_id'] ) ) ) {
		// First we remove the old file
		@unlink( BIT_ROOT_PATH.$storage_path );

		// Now we process the uploaded file
		if( $storagePath = liberty_process_upload( $pStoreRow ) ) {
			$sql = "UPDATE `".BIT_DB_PREFIX."liberty_files` SET `storage_path` = ?, `mime_type` = ?, `file_size` = ?, `user_id` = ? WHERE `file_id` = ?";
			$gBitSystem->mDb->query( $sql, array( $pStoreRow['upload']['dest_path'].$pStoreRow['upload']['name'], $pStoreRow['upload']['type'], $pStoreRow['upload']['size'], $pStoreRow['user_id'], $pStoreRow['file_id'] ) );
		}

		if( @include_once( LIBERTY_PKG_PATH.'plugins/storage.bitfile.php' ) ) {
			$sql = "UPDATE `".BIT_DB_PREFIX."liberty_attachments` SET `user_id` = ? WHERE `foreign_id` = ?";
			$gBitSystem->mDb->query( $sql, array( $pStoreRow['user_id'], $pStoreRow['file_id'] ) );
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
function treasury_default_store( &$pStoreRow ) {
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
function treasury_default_load( &$pFileHash ) {
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
			$pFileHash['filename']         = basename( $row['storage_path'] );
			$pFileHash['source_file']      = BIT_ROOT_PATH.$row['storage_path'];
			$pFileHash['source_url']       = BIT_ROOT_URL.str_replace( '+', '%20', str_replace( '%2F', '/', urlencode( $row['storage_path'] ) ) );
			$pFileHash['mime_type']        = $row['mime_type'];
			$pFileHash['file_size']        = $row['file_size'];
			$pFileHash['attachment_id']    = $row['attachment_id'];
			$pFileHash['wiki_plugin_link'] = "{attachment id=".$row['attachment_id']."}";

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
function treasury_default_download( &$pFileHash ) {
	global $gBitSystem;
	$ret = FALSE;

	// make sure we close off obzip compression if it's on
	if( $gBitSystem->isFeatureActive( 'site_output_obzip' ) ) {
		ob_end_clean();
	}

	// Check to see if the file actually exists
	if( is_readable( $pFileHash['source_file'] ) ) {
		header( "Cache Control: " );
		header( "Accept-Ranges: bytes" );
		// this will get the browser to open the download dialogue - even when the 
		// browser could deal with the content type - not perfect, but works
		//header( "Content-Type: application/force-download" );
		header( "Content-type: ".$pFileHash['mime_type'] );
		header( "Content-Disposition: attachment; filename=".$pFileHash['filename'] );
		header( "Last-Modified: ".gmdate( "D, d M Y H:i:s", $pFileHash['last_modified'] )." GMT", true, 200 );
		header( "Content-Length: ".filesize( $pFileHash['source_file'] ) );
		header( "Content-Transfer-Encoding: binary" );
		header( "Connection: close" );

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
function treasury_default_expunge( &$pParamHash ) {
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
?>
