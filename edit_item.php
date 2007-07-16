<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/edit_item.php,v 1.19 2007/07/16 15:59:42 squareing Exp $
 *
 * @author       xing  <xing@synapse.plus.com>
 * @package      treasury
 * @copyright    2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/

/**
 * Setup
 */ 
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );

require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'item_lookup_inc.php');

if( !$gContent->hasEditPermission() ) {
	$gBitSmarty->assign( 'msg', tra( "You can not edit this file." ) );
	$gBitSystem->display( "error.tpl" );
	die;
}

if( !empty( $_REQUEST['refresh'] ) ) {
	$gBitSmarty->assign( 'refresh', '?refresh='.time() );
}

if( !empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'remove' || !empty( $_REQUEST['confirm'] ) ) {
	if( @BitBase::verifyId( $_REQUEST['content_id'] ) ) {
		if( !empty( $_REQUEST['confirm'] )) {
			// now we are sure that we want to remove this item
			if( $gContent->expunge( TRUE )) {
				bit_redirect( TREASURY_PKG_URL );
			} else {
				$feedback['error'] = $gContent->mErrors;
			}
		}
		$gBitSystem->setBrowserTitle( 'Confirm removal of '.$gContent->mInfo['title'] );
		$formHash['remove'] = TRUE;
		$formHash['content_id'] = $_REQUEST['content_id'];
		$formHash['action'] = 'remove';
//		$formHash['input'] = array(
//			'<label><input name="force_item_delete" value="" type="radio" checked="checked" /> '.tra( "Delete file only if it doesn't appear in other galleries." ).'</label>',
//			'<label><input name="force_item_delete" value="true" type="radio" /> '.tra( "Permanently delete file, even if it appears in other galleries." ).'</label>',
//		);
		$msgHash = array(
			'label' => 'Remove File',
			'confirm_item' => $gContent->mInfo['title'],
			'warning' => 'This will permanently remove the file.',
		);
		$gBitSystem->confirmDialog( $formHash, $msgHash );
	} else {
		$feedback['error'] = tra( 'No valid content id given.' );
	}
}

// delete icon if needed
if( !empty( $_REQUEST['delete_thumbnails'] ) ) {
	$fileHash['dest_path']   = dirname( $gContent->mInfo['source_url'] ).'/';
	$fileHash['source_file'] = $gContent->mInfo['source_file'];
	$fileHash['type']        = $gContent->mInfo['mime_type'];
	liberty_clear_thumbnails( $fileHash );
	$gContent->load();
}

// set up everything for re-processing
if( !empty( $_REQUEST['reprocess_upload'] ) ) {
	// first we need to move the file out of the way
	$tmpfile = str_replace( "//", "/", tempnam( TEMP_PKG_PATH, TREASURY_PKG_NAME ) );
	rename( $gContent->mInfo['source_file'], $tmpfile );

	// now that the file has been moved, we need to remove all the files in the storage dir
	unlink_r( dirname( $gContent->mInfo['source_file'] ) );

	// fill the upload hash with the file details
	$fileHash['tmp_name'] = $tmpfile;
	$fileHash['name']     = $gContent->mInfo['filename'];
	$fileHash['size']     = $gContent->mInfo['file_size'];
	$fileHash['type']     = $gContent->mInfo['mime_type'];
	$fileHash['error']    = 0;

	$_REQUEST['upload']      = $fileHash;
	$_REQUEST['update_file'] = TRUE;
}

if( !empty( $_REQUEST['update_file'] ) ) {
	if( !empty( $_FILES['file']['tmp_name'] ) ) {
		$_REQUEST['upload'] = $_FILES['file'];
	}

	if( $gContent->store( $_REQUEST ) ) {
		// this will override any thumbnails created by the plugin
		if( !empty( $_FILES['icon']['tmp_name'] ) ) {
			if( preg_match( '#^image/#i', strtolower( $_FILES['icon']['type'] ) ) ) {
				$fileHash = $_FILES['icon'];
				$fileHash['dest_path'] = dirname( $gContent->mInfo['source_url'] ).'/';
				$fileHash['source_file'] = $fileHash['tmp_name'];
				liberty_clear_thumbnails( $fileHash );
				liberty_generate_thumbnails( $fileHash );
			} else {
				$feedback['error'] = tra( "The file you uploaded doesn't appear to be a valid image. The reported mime type is" ).": ".$_FILES['icon']['type'];
			}
		}
		$feedback['success'] = tra( 'The settings were successfully applied.' );
	}

	// get everything up to date
	$gContent->load();

	// new icons need to be displayed
	$gBitSmarty->assign( 'refresh', '?refresh='.time() );
}

if( !empty( $_REQUEST['reprocess_upload'] ) && !empty( $tmpfile ) && is_file( $tmpfile ) ) {
	// move file back to where it should be
	rename( $tmpfile, $gContent->mInfo['source_file'] );
}

// get a list of galleries this file is already part of
$galleryContentIds = $gContent->getGalleriesFromItemContentId();
$gBitSmarty->assign( 'galleryContentIds', $galleryContentIds );

$gallery = new TreasuryGallery();
$listHash['get_sub_tree']   = TRUE;
$listHash['max_records']    = -1;
$galleryList = $gallery->getList( $listHash );
$gBitSmarty->assign( 'galleryList', $galleryList );

$gContent->invokeServices( 'content_edit_function' );

$gBitSmarty->assign( 'feedback', ( !empty( $feedback ) ? $feedback : NULL ) );
$gBitSystem->display( "bitpackage:treasury/edit_item.tpl", tra( "Edit File" ) );
?>
