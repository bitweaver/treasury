<?php
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );

require_once( TREASURY_PKG_PATH.'TreasuryGallery.php');
require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'gallery_lookup_inc.php' );

// replace any user permissions with custom ones if we have set them
$gContent->updateUserPermissions();
$gBitSystem->verifyPermission( 'p_treasury_view_gallery' );

if( !empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'remove' || !empty( $_REQUEST['confirm'] ) ) {
	$gBitSystem->verifyPermission( 'p_treasury_edit_gallery' );

	if( @BitBase::verifyId( $_REQUEST['content_id'] ) ) {
		if( !empty( $_REQUEST['confirm'] ) ) {
			if( $gContent->expunge( !empty( $_REQUEST['force_item_delete'] ) ) ) {
				header( "Location: ".TREASURY_PKG_URL );
				die;
			} else {
				$feedback['errors'] = $gContent->mErrors;
			}
		}
		$gBitSystem->setBrowserTitle( 'Confirm removal of '.$gContent->mInfo['title'] );
		$formHash['remove'] = TRUE;
		$formHash['content_id'] = $_REQUEST['content_id'];
		$formHash['action'] = 'remove';
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

// services
$displayHash = array( 'perm_name' => 'p_treasury_gallery_view' );
$gContent->invokeServices( 'content_display_function', $displayHash );

// set up structure related stuff
global $gStructure;
if( empty( $gContent->mInfo['root_structure_id'] ) || !@BitBase::verifyId( $gContent->mInfo['root_structure_id'] ) ) {
	header( "Location:".TREASURY_PKG_URL."index.php" );
}

$gStructure = new LibertyStructure( $gContent->mInfo['root_structure_id'] );
$gStructure->load();

// confirm that structure is valid
if( empty( $gStructure ) || !$gStructure->isValid() ) {
	$gBitSystem->fatalError( 'Invalid structure' );
}

$gBitSmarty->assign_by_ref( 'gStructure', $gStructure );
$gBitSmarty->assign( 'structureInfo', $gStructure->mInfo );
$gBitSmarty->assign( 'subtree', $gStructure->getSubTree( $gStructure->mStructureId ) );

$listHash = $_REQUEST;
$listHash['root_structure_id'] = $gContent->mInfo['root_structure_id'];
$listHash['structure_id']      = $gContent->mInfo['structure_id'];

$gContent->loadItems( $listHash );

// pagination related settings
$listHash['listInfo']['parameters']['structure_id'] = $gContent->mStructureId;
$gBitSmarty->assign( 'listInfo', $listHash['listInfo'] );

$gContent->addHit();

// Display the template
$gBitSystem->display( 'bitpackage:treasury/view_gallery.tpl', tra( 'View Gallery' ) );
?>
