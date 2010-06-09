<?php
/**
 * @version      $Header$
 *
 * @author       xing  <xing@synapse.plus.com>
 * @package      treasury
 * @copyright    2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/

/**
 * Setup
 */ 
require_once( '../kernel/setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php');
require_once( TREASURY_PKG_PATH.'gallery_lookup_inc.php');

if( $gContent->isValid() ){
	$gContent->verifyUpdatePermission();
}else{
	$gContent->verifyCreatePermission();
}

// include edit structure file only when structure_id is known
if( !empty( $gContent->mStructureId ) ) {
	// prepare everything for structure loading
	$_REQUEST['structure_id'] = $gContent->mStructureId;

	// this interferes with the deletion
	$verifyStructurePermission = 'p_treasury_update_gallery';
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
	// $rootStructure is created in edit_structure_inc.php
	$_REQUEST['root_structure_id'] = !empty( $rootStructure->mStructureId ) ?  $rootStructure->mStructureId : NULL;
	$galleryStore = new TreasuryGallery( NULL, !empty( $_REQUEST['gallery_content_id'] ) ? $_REQUEST['gallery_content_id'] : NULL );
	$galleryStore->load();
	// pass thumbnail upload on to storage hash
	if( !empty( $_FILES['icon']['tmp_name'] )) {
		$_REQUEST['thumb'] = $_FILES['icon'];
	}
	if( $galleryStore->store( $_REQUEST ) ) {
		bit_redirect( $galleryStore->getDisplayUrl()."&refresh=1" );
	} else {
		$feedback['error'] = $galleryStore->mErrors;
	}
}

if( !empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'remove_gallery' || !empty( $_REQUEST['confirm'] ) ) {
	if( @BitBase::verifyId( $_REQUEST['content_id'] ) ) {
		if( $_REQUEST['action'] == 'remove_gallery' && !empty( $_REQUEST['confirm'] ) ) {
			if( $gContent->expunge( !empty( $_REQUEST['force_item_delete'] ) ) ) {
				bit_redirect( TREASURY_PKG_URL );
			} else {
				$feedback['error'] = $gContent->mErrors;
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
			'confirm_item' => $gContent->mInfo['title'] . ' ' . tra( 'and any subgalleries' ),
			'warning' => tra('This will remove the gallery, any sub-galleries and all associated files.'),
		);
		$gBitSystem->confirmDialog( $formHash, $msgHash );
	} else {
		$feedback['error'] = tra( 'No valid gallery content id given.' );
	}
}

$gContent->invokeServices( 'content_edit_function' );
$gBitSmarty->assign( 'imageSizes', get_image_size_options() );
$gBitSmarty->assign( 'feedback', !empty( $feedback ) ? $feedback : NULL );

$gBitSystem->display( 'bitpackage:treasury/edit_gallery.tpl', tra( 'Edit File Gallery' ) , array( 'display_mode' => 'edit' ));
?>
