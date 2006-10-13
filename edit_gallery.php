<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/edit_gallery.php,v 1.7 2006/10/13 12:47:20 lsces Exp $
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
$gBitSystem->verifyPermission( 'p_treasury_edit_gallery' );

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php');
require_once( TREASURY_PKG_PATH.'gallery_lookup_inc.php');

// include edit structure file only when structure_id is known
if( !empty( $gContent->mStructureId ) ) {
	// prepare everything for structure loading
	$_REQUEST['structure_id'] = $gContent->mStructureId;

	// this interferes with the deletion
	$verifyStructurePermission = 'p_treasury_edit_gallery';
	include_once( LIBERTY_PKG_PATH.'edit_structure_inc.php' );

	// get all the nodes in this structure
	foreach( $rootTree as $node ) {
		$galleryStructure[$node['structure_id']] = str_repeat( '-', $node['level'] ).' '.$node['title'];
	}
	$gBitSmarty->assign( 'galleryStructure', $galleryStructure );
}

if( !empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'edit' ) {
	$gBitSmarty->assign_by_ref( 'galInfo', $gContent->mInfo );
}

if( !empty( $_REQUEST['treasury_store'] ) ) {
	$_REQUEST['treasury']['root_structure_id'] = !empty( $rootStructure->mStructureId ) ?  $rootStructure->mStructureId : NULL;
	$galleryStore = new TreasuryGallery( !empty( $_REQUEST['structure_id'] ) ? $_REQUEST['structure_id'] : NULL, !empty( $_REQUEST['content_id'] ) ? $_REQUEST['content_id'] : NULL );
	$galleryStore->load();
	if( $galleryStore->store( $_REQUEST['treasury'] ) ) {
		// process image upload
		if( $gBitSystem->isFeatureActive( 'treasury_gallery_list_thumb' ) ) {
			// now deal with the uploaded image
			if( !empty( $_FILES['icon']['tmp_name'] ) ) {
				if( preg_match( '#^image/#i', strtolower( $_FILES['icon']['type'] ) ) ) {
					$fileHash = $_FILES['icon'];
					$fileHash['thumbsizes'] = array( 'icon', 'avatar', 'small', 'medium' );
					$fileHash['dest_path'] = $galleryStore->getGalleryThumbBaseUrl();
					$fileHash['source_file'] = $fileHash['tmp_name'];
					liberty_clear_thumbnails( $fileHash );
					liberty_generate_thumbnails( $fileHash );
					$gBitSmarty->assign( 'refresh', '?refresh='.time() );
				} else {
					$feedback['error'] = tra( "The file you uploaded doesn't appear to be a valid image. The reported mime type is" ).": ".$_FILES['icon']['type'];
				}
			}
		}

		header( 'Location: '.$galleryStore->getDisplayUrl()."&refresh=1" );
	} else {
		$feedback['error'] = $galleryStore->mErrors;
	}
}

if( !empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'remove_gallery' || !empty( $_REQUEST['confirm'] ) ) {
	$gBitSystem->verifyPermission( 'p_treasury_edit_gallery' );

	if( @BitBase::verifyId( $_REQUEST['content_id'] ) ) {
		if( $_REQUEST['action'] == 'remove_gallery' && !empty( $_REQUEST['confirm'] ) ) {
			if( $gContent->expunge( !empty( $_REQUEST['force_item_delete'] ) ) ) {
				header( "Location: ".TREASURY_PKG_URL );
				die;
			} else {
				$feedback['errors'] = $gContent->mErrors;
			}
		}

		$gBitSystem->setBrowserTitle( 'Confirm removal of '.$gContent->mInfo['title'] );
		$formHash['remove_gallery'] = TRUE;
		$formHash['content_id'] = $_REQUEST['content_id'];
		$formHash['action'] = 'remove_gallery';
		$formHash['input'] = array(
			'<label><input name="force_item_delete" value="" type="radio" checked="checked" /> '.tra( "Delete only files that don't appear in other galleries." ).'</label>',
			'<label><input name="force_item_delete" value="true" type="radio" /> '.tra( "Permanently delete all contents, even if they appear in other galleries." ).'</label>',
		);
		$msgHash = array(
			'label' => 'Remove File Gallery',
			'confirm_item' => $gContent->mInfo['title'].'<br />'.tra( 'and any subgalleries' ),
			'warning' => 'This will remove the gallery, any syb-galleries and all associated files.',
		);
		$gBitSystem->confirmDialog( $formHash, $msgHash );
	} else {
		$feedback['error'] = tra( 'No valid gallery content id given.' );
	}
}

$gContent->invokeServices( 'content_edit_function' );

$imageSizes = array(
	'0'      => tra( 'Disable this feature' ),
	'icon'   => tra( 'Icon ( 48 x 48 pixels )' ),
	'avatar' => tra( 'Avatar ( 100 x 75 pixels )' ),
	'small'  => tra( 'Small ( 160 x 120 pixels )' ),
);
$gBitSmarty->assign( 'imageSizes', $imageSizes );

$gBitSmarty->assign( 'feedback', !empty( $feedback ) ? $feedback : NULL );

$gBitSystem->display( 'bitpackage:treasury/edit_gallery.tpl', tra( 'Edit File Gallery' ) );
?>
