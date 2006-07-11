<?php
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyPackage( 'treasury' );
$gBitSystem->verifyPermission( 'p_treasury_edit_item' );

require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'item_lookup_inc.php');

if( !empty( $_REQUEST['refresh'] ) ) {
	$gBitSmarty->assign( 'refresh', '?refresh='.time() );
}

if( !empty( $_REQUEST['update_file'] ) ) {
	if( $gContent->store( $_REQUEST['treasury'] ) ) {
		// now deal with the uploaded icon
		if( !empty( $_REQUEST['reset_thumbnails'] ) ) {
			$fileHash['thumbsizes'] = array( 'icon', 'avatar', 'small' );
			$fileHash['dest_path'] = dirname( $gContent->mInfo['source_url'] ).'/';
			$fileHash['source_file'] = $gContent->mInfo['source_file'];
			$fileHash['type'] = $gContent->mInfo['mime_type'];
			liberty_clear_thumbnails( $fileHash );
			liberty_generate_thumbnails( $fileHash );
		}

		if( !empty( $_FILES['icon']['tmp_name'] ) ) {
			if( preg_match( '#^image/#i', strtolower( $_FILES['icon']['type'] ) ) ) {
				$fileHash = $_FILES['icon'];
				$fileHash['thumbsizes'] = array( 'icon', 'avatar', 'small' );
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
	$gContent->load();
}

// get a list of galleries this file is already part of
$galleryContentIds = $gContent->getGalleriesFromItemContentId();
$gBitSmarty->assign( 'galleryContentIds', $galleryContentIds );

$gallery = new TreasuryGallery();
$listHash['load_only_root'] = TRUE;
$listHash['max_records']    = -1;
$galleryList = $gallery->getList( $listHash );

if( @is_array( $galleryList ) ) {
	foreach( $galleryList as $key => $gallery ) {
		if( empty( $gStructure ) ) {
			$gStructure = new LibertyStructure();
		}
		$galleryList[$key]['subtree'] = $gStructure->getSubTree( $gallery['root_structure_id'] );
	}
}
$gBitSmarty->assign( 'galleryList', $galleryList );

$gBitSmarty->assign( 'feedback', ( !empty( $feedback ) ? $feedback : NULL ) );
$gBitSystem->display( "bitpackage:treasury/edit_item.tpl", tra( "View File" ) );
?>
