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
require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
require_once( TREASURY_PKG_PATH.'gallery_lookup_inc.php' );

$feedback = array();
$gContent->verifyViewPermission();

if( !empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'remove' || !empty( $_REQUEST['confirm'] ) ) {
	if( !empty( $_REQUEST['confirm'] ) ) {
		$feedback['success'] = '';
		foreach( $_REQUEST['del_content_ids'] as $contentId ) {
			if( @BitBase::verifyId( $contentId ) ) {
				if( $galleryItem = $gLibertySystem->getLibertyObject( $contentId ) ) {
					$galleryItem->load();
					$title = $galleryItem->getTitle();
					if( $galleryItem->expunge() ) {
						$feedback['success'] .= "<li>$title</li>";
					}
				}
			}
		}

		if( !empty( $feedback['success'] ) ) {
			$feedback['success'] = tra( 'The following items were successfully deleted' ).':<ul>'.$feedback['success'].'</ul>';
		}
	} else {
		$gBitSystem->setBrowserTitle( 'Confirm removal of '.$gContent->mInfo['title'] );
		foreach( $_REQUEST['del_content_ids'] as $cid ) {
			$item = new TreasuryItem( NULL, $cid );
			$itemInfo = $item->load();
			$formHash['input'][] = '<input type="hidden" name="del_content_ids[]" value="'.$cid.'"/>'."<strong>{$item->mInfo['title']}</strong> - {$item->mInfo['mime_type']} - {$item->mInfo['file_size']} bytes";
		}
		$formHash['action'] = 'remove';
		$formHash['structure_id'] = $_REQUEST['structure_id'];
		$msgHash = array(
			'label' => tra('Remove Files'),
			'warning' => tra('This will permanently remove these files.'),
		);
		$gBitSystem->confirmDialog( $formHash, $msgHash );
	}
}

// used to display the newly updated version of an image
if( !empty( $_REQUEST['refresh'] ) ) {
	$gBitSmarty->assign( 'refresh', '?refresh='.time() );
}

// services
$displayHash = array( 'perm_name' => 'p_treasury_view_gallery' );
$gContent->invokeServices( 'content_display_function', $displayHash );

// set up structure related stuff
global $gStructure;
if( empty( $gContent->mInfo['root_structure_id'] ) || !@BitBase::verifyId( $gContent->mInfo['root_structure_id'] ) ) {
	bit_redirect( TREASURY_PKG_URL."index.php" );
}

$gStructure = new LibertyStructure( $gContent->mInfo['root_structure_id'] );
$gStructure->load();

// confirm that structure is valid
if( empty( $gStructure ) || !$gStructure->isValid() ) {
	$gBitSystem->fatalError( tra( 'Invalid structure' ));
}

$gBitSmarty->assignByRef( 'gStructure', $gStructure );
$gBitSmarty->assign( 'structureInfo', $gStructure->mInfo );
$gBitSmarty->assign( 'subtree', $gStructure->getSubTree( $gStructure->mStructureId ) );

$listHash = $_REQUEST;
$listHash['root_structure_id'] = $gContent->mInfo['root_structure_id'];
$listHash['structure_id']      = $gContent->mInfo['structure_id'];
$listHash['sort_mode']         = !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : 'created_desc';

$gContent->loadItems( $listHash );

// pagination related settings
$listHash['listInfo']['parameters']['structure_id'] = $gContent->mStructureId;
$gBitSmarty->assign( 'listInfo', $listHash['listInfo'] );
$gBitSmarty->assign( 'feedback', $feedback );

$gContent->addHit();

// Display the template
$gBitSystem->display( 'bitpackage:treasury/view_gallery.tpl', tra( 'View Gallery' ) , array( 'display_mode' => 'list' ));
?>
