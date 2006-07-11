<?php
/**
 * @version:      $Header: /cvsroot/bitweaver/_bit_treasury/TreasuryBase.php,v 1.1 2006/07/11 13:43:54 squareing Exp $
 *
 * @author:       xing  <xing@synapse.plus.com>
 * @version:      $Revision: 1.1 $
 * @created:      Monday Jul 03, 2006   11:01:55 CEST
 * @package:      treasury
 * @copyright:    2003-2006 bitweaver
 * @license:      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/
require_once( LIBERTY_PKG_PATH.'LibertyAttachable.php' );
require_once( LIBERTY_PKG_PATH.'LibertyStructure.php' );

/**
 *   TreasuryBase 
 * 
 * @uses LibertyAttachable
 */
class TreasuryBase extends LibertyAttachable {
	/**
	 * Initiates class
	 *
	 * @param mixed $pStructureId structure id of the treasury - use either one of the ids.
	 * @param mixed $pContentId content id of the treasury - use either one of the ids.
	 * @access public
	 * @return void
	 */
	function TreasuryBase( $pStructureId=NULL, $pContentId=NULL ) {
		if( get_class( $this ) == 'treasurygallery' ) {
			LibertyContent::LibertyContent();
		} else {
			LibertyAttachable::LibertyAttachable();
		}
	}

	/**
	 * Get the path of the gallery
	 * 
	 * @param numeric $pStructureId ID of the gallery we want to find the path to
	 * @access public
	 * @return Gallery path on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getGalleryPath( $pStructureId = NULL ) {
		if( !@BitBase::verifyId( $pStructureId ) ) {
			$pStructureId = $this->mStructureId;
		}

		if( @BitBase::verifyId( $pStructureId ) ) {
			global $gStructure;
			// create new object if needed
			if( empty( $gStructure ) ) {
				$gStructure = new LibertyStructure();
			}
			// get the structure path
			$ret = $gStructure->getPath( $pStructureId );
		}
		return( !empty( $ret ) ? $ret : FALSE );
	}

	/**
	 * Get an HTML representation of the gallery path returned by getGalleryPath()
	 * 
	 * @param array $pPath Path returned by getGalleryPath()
	 * @access public
	 * @return HTML links
	 */
	function getDisplayPath( $pPath ) {
		$ret = '';
		if( !empty( $pPath ) && is_array( $pPath ) ) {
			foreach( $pPath as $node ) {
				$ret .= ( @BitBase::verifyId( $node['parent_id'] ) ? ' &raquo; ' : '' ).'<a title="'.htmlspecialchars( $node['title'] ).'" href="'.TREASURY_PKG_URL.'view.php?structure_id='.$node['structure_id'].'">'.htmlspecialchars( $node['title'] ).'</a>';
			}
		}
		return $ret;
	}

	/**
	 * Get the last gallery created by this user. If the user hasn't created a gallery, create one
	 * 
	 * @param string $pNewName Name of the new gallery
	 * @access public
	 * @return Gallery Id of the default gallery
	 */
	function getDefaultGalleryId( $pNewName = NULL ) {
		global $gBitUser, $gContent;
		$gal = new TreasuryGallery();
		// as default gallery, we pick the first one created
		$getHash = array( 'user_id' => $gBitUser->mUserId, 'max_records' => 1, 'sort_mode' => 'created_asc' );
		$upList = $gal->getList( $getHash );
		if( @BitBase::verifyId( key( $upList ) ) ) {
			$ret = key( $upList );
		} else {
			if( empty( $pNewName ) ) {
				$pNewName = "File Gallery";
			}
			$galleryHash = array( 'title' => $pNewName );
			if( $gal->store( $galleryHash ) ) {
				$ret = $gal->mContentId;
			}
		}
		if( !$gContent->isValid() ) {
			$gContent = new TreasuryGallery( $ret );
		}
		return $ret;
	}

	/**
	 * Update the position of an item in the gallery
	 * 
	 * @param numeric $pGalleryContentId Gallery content id of the gallery where we want to move around the order of things
	 * @param numeric $newPosition New position number
	 * @access public
	 * @return void
	 */
	function updatePosition( $pGalleryContentId, $newPosition = NULL ) {
		if( $pGalleryContentId && $newPosition && $this->verifyId( $this->mContentId ) ) {
			// SQL optimization to prevent stupid updates of identical data
			$sql = "UPDATE `".BIT_DB_PREFIX."fisheye_gallery_image_map` SET `item_position` = ?
					WHERE `item_content_id` = ? AND `gallery_content_id` = ? AND (`item_position` IS NULL OR `item_position`!=?)";
			$rs = $this->mDb->query( $sql, array( $newPosition, $this->mContentId, $pGalleryContentId, $newPosition ) );
		}
	}
}
?>
