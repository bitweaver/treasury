<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/TreasuryBase.php,v 1.9 2008/05/08 18:33:59 squareing Exp $
 *
 * @author       xing  <xing@synapse.plus.com>
 * @version      $Revision: 1.9 $
 * created      Monday Jul 03, 2006   11:01:55 CEST
 * @package      treasury
 * @copyright    2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/
 
/**
 * Setup
 */ 
require_once( LIBERTY_PKG_PATH.'LibertyAttachable.php' );
require_once( LIBERTY_PKG_PATH.'LibertyStructure.php' );

/**
 *   TreasuryBase 
 * 
 * @package treasury
 * @uses LibertyAttachable
 */
class TreasuryBase extends LibertyAttachable {
	/**
	 * Initiates class
	 *
	 * @access public
	 * @return void
	 */
	function TreasuryBase() {
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
		$ret = '<a title="'.tra( 'Galleries' ).'" href="'.TREASURY_PKG_URL.'">'.tra( 'Galleries' ).'</a>';
		if( !empty( $pPath ) && is_array( $pPath ) ) {
			$ret .= " &raquo; ";
			foreach( $pPath as $node ) {
				$ret .= ( @BitBase::verifyId( $node['parent_id'] ) ? ' &raquo; ' : '' ).'<a title="'.htmlspecialchars( $node['title'] ).'" href="'.TreasuryGallery::getDisplayUrl( $node['content_id'] ).'">'.htmlspecialchars( $node['title'] ).'</a>';
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
		// as default gallery, we pick the first one created by this user
		$gal = new TreasuryGallery();
		$getHash = array( 'user_id' => $gBitUser->mUserId, 'max_records' => 1, 'sort_mode' => 'created_asc' );
		$upGal = $gal->getList( $getHash );

		if( @BitBase::verifyId( key( $upGal ) ) ) {
			$ret = key( $upGal );
		} elseif( $gBitUser->hasPermission( 'p_treasury_create_gallery' ) ) {
			// Since the user can create a new gallery, we simply create a new one
			if( empty( $pNewName ) ) {
				$pNewName = $gBitUser->getDisplayName()."'s File Gallery";
			}
			$galleryHash = array( 'title' => $pNewName );
			if( $gal->store( $galleryHash ) ) {
				$ret = $gal->mContentId;
			}
		} else {
			// if we reach this section, we'll simply pick the first gallery we can find and dump all files in there
			$getHash = array( 'max_records' => 1, 'sort_mode' => 'created_asc' );
			$upGal = $gal->getList( $getHash );
			if( @BitBase::verifyId( key( $upGal ) ) ) {
				$ret = key( $upGal );
			} else {
				// we need to report that there is absolutely no way we can place the gallery anywhere
				$this->mErrors['no_default'] = tra( 'We could not find a viable gallery where we can store your upload' );
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

	/**
	 * update the preference of all galleries at once
	 * 
	 * @param array $pPrefName 
	 * @param array $pPrefValue 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function batchStorePreference( $pPrefName, $pPrefValue = NULL ) {
		global $gBitSystem;
		// get all gallery contentIds
		if( $galleryContentIds = $gBitSystem->mDb->getCol( "SELECT `content_id` FROM `".BIT_DB_PREFIX."treasury_gallery`" ) ) {
			foreach( $galleryContentIds as $gid ) {
				$query    = "DELETE FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id`=? AND `pref_name`=?";
				$bindvars = array( $gid, $pPrefName );
				$result   = $this->mDb->query( $query, $bindvars );
				if( !is_null( $pPrefValue ) ) {
					$query = "INSERT INTO `".BIT_DB_PREFIX."liberty_content_prefs` (`content_id`,`pref_name`,`pref_value`) VALUES(?, ?, ?)";
					$bindvars[]=$pPrefValue;
					$result = $this->mDb->query( $query, $bindvars );
					$this->mPrefs[$pPrefName] = $pPrefValue;
				}
			}
		}
	}

	/**
	 * Treasury always needs to have pCheckGlobalPerm set to TRUE by default
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function hasUserPermission( $pPermName, $pVerifyAccessControl=TRUE, $pCheckGlobalPerm=TRUE ) {
		return parent::hasUserPermission( $pPermName, $pVerifyAccessControl, $pCheckGlobalPerm );
	}

	/**
	 * hasDownloadPermission will mimic hasViewPermission for downloads
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function hasDownloadPermission() {
		return( $this->hasEditPermission() || $this->hasUserPermission( 'p_treasury_download_item' ));
	}

	/**
	 * verifyDownloadPermission will mimic verifyViewPermission for downloads
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function verifyDownloadPermission() {
		if( $this->hasDownloadPermission() ) {
			return TRUE;
		} else {
			global $gBitSystem;
			$gBitSystem->fatalPermission( 'p_treasury_download_item' );
		}
	}
}
?>
