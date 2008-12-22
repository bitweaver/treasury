<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/TreasuryItem.php,v 1.76 2008/12/22 12:06:15 squareing Exp $
 *
 * @author       xing  <xing@synapse.plus.com>
 * @version      $Revision: 1.76 $
 * created      Monday Jul 03, 2006   11:55:41 CEST
 * @package      treasury
 * @copyright   2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/

/**
 * Setup
 */ 
define( 'TREASURYITEM_CONTENT_TYPE_GUID', 'treasuryitem' );
require_once( TREASURY_PKG_PATH.'TreasuryBase.php' );
require_once( TREASURY_PKG_PATH.'TreasuryGallery.php');

/**
 *   TreasuryItem 
 * 
 * @package treasury
 * @uses TreasuryBase
 */
class TreasuryItem extends TreasuryBase {
	/**
	 * Initiate class
	 *
	 * @param $pContentId content id of the treasury - use either one of the ids.
	 * @return none
	 * @access public
	 **/
	function TreasuryItem( $pDummy = NULL, $pContentId = NULL ) {
		TreasuryBase::TreasuryBase();
		$this->registerContentType(
			TREASURYITEM_CONTENT_TYPE_GUID, array(
				'content_type_guid'   => TREASURYITEM_CONTENT_TYPE_GUID,
				'content_description' => 'Uploaded File',
				'handler_class'       => 'TreasuryItem',
				'handler_package'     => 'treasury',
				'handler_file'        => 'TreasuryItem.php',
				'maintainer_url'      => 'http://www.bitweaver.org'
			)
		);
		$this->mContentId = !empty( $pDummy ) ? $pDummy : $pContentId;
		$this->mContentTypeGuid = TREASURYITEM_CONTENT_TYPE_GUID;

		// Permission setup
		$this->mViewContentPerm  = 'p_treasury_view_item';
		$this->mCreateContentPerm  = 'p_treasury_upload_item';
		$this->mUpdateContentPerm  = 'p_treasury_update_item';
		$this->mAdminContentPerm = 'p_treasury_admin';
	}

	/**
	 * load the treasury item
	 *
	 * @param $pPluginParams parameters passed on to the plugin during laod
	 * @return bool TRUE on success, FALSE if it's not valid
	 * @access public
	 **/
	function load( $pPluginParams = NULL ) {
		if( @BitBase::verifyId( $this->mContentId )) {
			global $gBitSystem;

			$ret = array();

			$selectSql = $joinSql = $orderSql = '';
			if( @BitBase::verifyId( $this->mContentId )) {
				$whereSql = " WHERE trm.`item_content_id` = ? ";
				$bindVars[] = $this->mContentId;
			}
			$this->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

			$ret = array();
			$query = "
				SELECT
					lct.`content_description`,
					uu.`login`, uu.`real_name`,
					lc.`content_id`, lc.`format_guid`, lc.`last_modified`, lc.`user_id`, lc.`title`, lc.`content_type_guid`, lc.`created`, lc.`data`,
					lch.`hits`
					$selectSql
				FROM `".BIT_DB_PREFIX."treasury_map` trm
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = trm.`item_content_id` )
					INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON ( lc.`content_type_guid` = lct.`content_type_guid` )
					INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = lc.`user_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lch.`content_id` = lc.`content_id` )
				$joinSql $whereSql $orderSql";
			if( $aux = $this->mDb->getRow( $query, $bindVars ) ) {
				$this->mContentId            = $aux['content_id'];
				$this->mInfo                 = $aux;
				$this->mInfo['title']        = $this->getTitle( $aux );
				$this->mInfo['display_url']  = $this->getDisplayUrl();

				// LibertyMime will load the attachment details
				LibertyMime::load( NULL, $pPluginParams );

				// parse the data after parent load so we have our html prefs
				$this->mInfo['parsed_data'] = $this->parseData();

				// copy mStorage to mInfo for easy access
				if( !empty( $this->mStorage ) && count( $this->mStorage ) > 0 ) {
					reset( $this->mStorage );
					$this->mInfo = array_merge( current( $this->mStorage ), $this->mInfo );
				}

				// TODO: take comments on a gallery basis
				// work out if this gallery takes comments
				//$this->mInfo['allow_comments'] = $gBitSystem->isFeatureActive( "treasury_".$this->mInfo['attachment_plugin_guid']."_comments" );
			}
		}

		return( count( $this->mInfo ) );
	}

	/**
	 * getList 
	 * 
	 * @param array $pListHash 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getList( &$pListHash, $pStructureId = NULL ) {
		LibertyContent::prepGetList( $pListHash );

		$ret = $bindVars = array();
		$selectSql = $joinSql = $whereSql = "";

		if( @BitBase::verifyId( $pListHash['gallery_content_id'] ) ) {
			$whereSql   = " WHERE trm.`gallery_content_id` = ? ";
			$bindVars[] = $pListHash['gallery_content_id'];
		}

		if( @BitBase::verifyId( $pListHash['user_id'] ) ) {
			$whereSql  .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql  .= " lc.`user_id` = ? ";
			$bindVars[] = $pListHash['user_id'];
		}

		if( !empty( $pListHash['title'] ) && is_string( $pListHash['title'] ) ) {
			$whereSql  .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql  .= " trm.`item_content_id` = lc.`content_id` AND UPPER( lc.`title` ) = ?";
			$bindVars[] = strtoupper( $pListHash['title'] );
		}

		if( !empty( $pListHash['max_age'] ) && is_numeric( $pListHash['max_age'] ) ) {
			$whereSql  .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql  .= " lc.`created` > ? ";
			$bindVars[] = $pListHash['max_age'];
		}

		if( !empty( $pListHash['sort_mode'] ) ) {
			$orderSql   = " ORDER BY ".$this->mDb->convertSortmode( $pListHash['sort_mode'] )." ";
		} else {
			$orderSql   = " ORDER BY trm.`item_position` ASC ";
		}

		// only join attachments table when we need it for sorting
		if( strstr( $pListHash['sort_mode'], 'la.hits' ) !== FALSE ) {
			$joinSql .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON ( la.`content_id` = lc.`content_id` ) ";
		}

		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		$ret = array();
		$query = "
			SELECT
				lct.`content_description`,
				uu.`login`, uu.`real_name`,
				lc.`content_id`, lc.`last_modified`, lc.`user_id`, lc.`title`, lc.`content_type_guid`, lc.`created`, lc.`data`,
				lch.`hits` $selectSql
			FROM `".BIT_DB_PREFIX."treasury_map` trm
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = trm.`item_content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON ( lc.`content_type_guid` = lct.`content_type_guid` )
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = lc.`user_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lch.`content_id` = lc.`content_id` )
			$joinSql $whereSql $orderSql";
		$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );
		while( $aux = $result->fetchRow() ) {
			$item = new TreasuryItem( $aux['content_id'] );
			$item->load();
			$ret[] = $item;
		}

		$query = "SELECT COUNT( trm.`item_content_id` )
			FROM `".BIT_DB_PREFIX."treasury_map` trm
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = trm.`item_content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON ( lc.`content_type_guid` = lct.`content_type_guid` )
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = lc.`user_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lch.`content_id` = lc.`content_id` )
			$joinSql $whereSql";
		$pListHash['cant'] = $this->mDb->getOne( $query, $bindVars );
		LibertyContent::postGetList( $pListHash );

		return( count( $this->mErrors ) == 0 ) ? $ret : FALSE;
	}

	/**
	 * Get all galleries this item is part of
	 * 
	 * @access public
	 * @return array of gallery content ids
	 */
	function getParentGalleries() {
		if( $this->isValid() ) {
			$query = "SELECT ls.`content_id`
				FROM `".BIT_DB_PREFIX."treasury_map` trm
					INNER JOIN `".BIT_DB_PREFIX."treasury_gallery` trg ON ( trg.`content_id` = trm.`gallery_content_id` )
					INNER JOIN `".BIT_DB_PREFIX."liberty_structures` ls ON ( trg.`structure_id` = ls.`structure_id` )
				WHERE trm.`item_content_id`=?";
			$ret = $this->mDb->getCol( $query, array( $this->mContentId ) );
		}
		return( !empty( $ret ) ? $ret : FALSE );
	}

	/**
	 * Store TreasuryItem
	 *
	 * @param array $pStoreHash contains all data to store the gallery
	 * @param string $pStoreHash[title] title of the new upload
	 * @param string $pStoreHash[edit] description of the upload
	 * @param array $pStoreHash[galleryContentIds] (optional) Gallery Content IDs this item belongs to
	 * @return bool TRUE on success, FALSE if store could not occur. If FALSE, $this->mErrors will have reason why
	 * @access public
	 **/
	function store( &$pStoreHash ) {
		global $gBitUser;
		// make sure all the data is in order
		if( $this->verify( $pStoreHash ) ) {
			// short hand
			$this->mDb->StartTrans();

			// if this is an update, we remove entries in the map table first
			if( $this->isValid() ) {
				$this->expungeItemMap();
			}

			if( LibertyMime::store( $pStoreHash ) ) {
				// ---------- Map store
				// update entries in the map
				foreach( $pStoreHash['map_store']['galleryContentIds'] as $gcid ) {
					$storeRow = array(
						'gallery_content_id' => $gcid,
						'item_content_id' => $pStoreHash['content_id'],
					);
					$this->mDb->associateInsert( BIT_DB_PREFIX.'treasury_map', $storeRow );
				}
			}

			if( count( $this->mErrors ) == 0 ) {
				$this->mDb->CompleteTrans();
			} else {
				$this->mDb->RollbackTrans();
			}
		}

		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * Verify content that is about to be stored
	 * 
	 * @param array $pStoreHash hash of all data that needs to be stored in the database
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason
	 */
	function verify( &$pStoreHash ) {
		global $gBitSystem, $gBitUser;

		// ---------- Upload store - dealt with by LibertyMime
		// Deal with the uploaded file
		// we place it in [_files_override][0] that LibertyMime knows that it has to deal with it
		if( @is_array( $pStoreHash['upload'] ) ) {
			if( !empty( $pStoreHash['upload']['tmp_name'] ) && empty( $pStoreHash['upload']['error'] ) ) {
				$pStoreHash['_files_override'][0] = $pStoreHash['upload'];
			} else {
				$this->mErrors['upload'] = tra( "There was a problem processing the uploaded file. Please try again." );
			}
		}

		// Make sure we know what to update
		if( $this->isValid() ) {
			// these 2 entries will inform LibertyContent and LibertyMime that this is an update
			$pStoreHash['content_id'] = $this->mContentId;
			$pStoreHash['_files_override'][0]['attachment_id'] = $this->mInfo['attachment_id'];
		} elseif( empty( $pStoreHash['_files_override'][0]['tmp_name'] ) || !is_file( $pStoreHash['_files_override'][0]['tmp_name'] ) ) {
			// Make sure the upload went well. If not, we break off this mellarky here.
			$this->mErrors['upload'] = tra( 'Uploading this file has destroyed this website - possibly even the entire internet.' );
		}


		// ---------- Map store
		// make sure we have at least one gallery where we can add the file
		if( empty( $pStoreHash['galleryContentIds'] ) ) {
			$pStoreHash['galleryContentIds'][] = $this->getDefaultGalleryId();
		}

		// make sure we have the correct permissions to upload to this gallery
		foreach( $pStoreHash['galleryContentIds'] as $gcid ) {
			$gallery = new TreasuryGallery( NULL, $gcid );
			if( $gallery->hasUserPermission( 'p_treasury_upload_item' )) {
				$pStoreHash['map_store']['galleryContentIds'][] = $gcid;
			}
		}

		if( empty( $pStoreHash['map_store']['galleryContentIds'] ) ) {
			$this->mErrors['store'] = tra( 'No gallery available to insert uploaded files into. Please check the permissions of the gallery.' );
		}

		// ---------- Content store
		// let's add a default title
		if( empty( $pStoreHash['title'] ) && !empty( $pStoreHash['upload']['name'] ) ) {
			$pStoreHash['title'] = file_name_to_title( $pStoreHash['upload']['name'] );
		}
		$pStoreHash['title'] = substr( $pStoreHash['title'], 0, 160 );

		// sort out the description
		if( $this->isValid() && !empty( $this->mInfo['data'] ) && empty( $pStoreHash['edit'] ) ) {
			$pStoreHash['edit'] = '';
		} elseif( empty( $pStoreHash['edit'] ) ) {
			unset( $pStoreHash['edit'] );
		} else {
			$pStoreHash['edit'] = $pStoreHash['edit'];
		}

		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * batchStore 
	 * 
	 * @param array $pStoreHash 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 * note: files are taken from $_FILES directly
	 */
	function batchStore( &$pStoreHash ) {
		global $gBitUser, $gBitSystem;
		// we will use the information in $_FILES
		$i = 0;
		if( !empty( $pStoreHash['import']['file'] ) && $gBitUser->hasPermission( 'p_treasury_import_item' )) {
			// don't allow sneaky shits to import stuff outside our specified jail
			$jail = $gBitSystem->getConfig( 'treasury_file_import_path' );
			$file = realpath( $jail.$pStoreHash['import']['file'] );
			if( strpos( $file, $jail ) !== FALSE && is_file( $file )) {
				// this will copy a file instead of move it
				$import['copy_file'] = TRUE;
				$import['tmp_name']  = $file;
				$import['name']      = basename( $file );
				$import['size']      = filesize( $file );
				$import['error']     = 0;
				$import['type']      = $gBitSystem->verifyMimeType( $file );
				if( $import['type'] == 'application/binary' || $import['type'] == 'application/octet-stream' || $import['type'] == 'application/octetstream' ) {
					$import['type'] = $gBitSystem->lookupMimeType( basename( $file ));
				}
				// pass on details to correct place
				$pStoreHash['file'][] = $pStoreHash['import'];
				$_FILES[] = $import;
			} else {
				$this->mErrors['import'] = "The file path given was not valid.";
			}
		}

		foreach( $_FILES as $upload ) {
			if( !empty( $upload['tmp_name'] )) {
				// we start with a fresh copy every cycle to ensure that our store hash is pristine
				$item = new TreasuryItem();
				$storeHash = $pStoreHash;

				if( !empty( $storeHash['file'][$i] )) {
					$storeHash = array_merge( $storeHash, $storeHash['file'][$i] );
				}

				$storeHash['upload'] = $upload;
				$item->store( $storeHash );
			} else {
				$item->mErrors['upload'] = tra( "There was an error uploading the file: " ).$upload['name'];
			}
			$i++;
		}

		if( $i > 1 ) {
			$pStoreHash['redirect'] = TreasuryGallery::getDisplayUrl( $storeHash['galleryContentIds'][0] );
		} elseif( !empty( $storeHash['content_id'] )) {
			$pStoreHash['redirect'] = TreasuryItem::getDisplayUrl( $storeHash['content_id'] );
		}

		$this->mErrors = array_merge( $this->mErrors, $item->mErrors );
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * verifyGalleryPermissions will check for permissive permissions of all galleries owning a given item
	 * 
	 * @param array $pPermName 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function verifyGalleryPermissions( $pPermName ) {
		global $gBitSystem, $gBitUser;
		$ret = FALSE;
		if( $this->isValid() && !empty( $pPermName ) ) {
			// get all gallery content ids
			$galleryContentIds = $this->getParentGalleries();
			if( !empty( $galleryContentIds ) && is_array( $galleryContentIds ) ) {
				$gallery = new TreasuryGallery();
				foreach( $galleryContentIds as $gcid ) {
					// reduce load: we don't need to fully load the gallery to load the permissions
					$gallery->mContentId = $gcid;
					if( $gallery->hasUserPermission( $pPermName )) {
						// we only need one gallery that allows us to download the file
						return TRUE;
					}
				}
			}
		}

		$gBitSystem->fatalPermission( $pPermName );
		return $ret;
	}

	/**
	 * Returns HTML link to display a gallery or item
	 *
	 * @param string $pTitle Title of the file we want to view
	 * @param array $pMixed Hash of data used to create link
	 * @return Full HTML link to file
	 **/
	function getDisplayLink( $pTitle=NULL, $pMixed=NULL, $pStructureId=NULL ) {
		global $gBitSystem;
		if( empty( $pTitle ) && !empty( $this ) ) {
			$pTitle = $this->getTitle();
		}

		if( empty( $pMixed ) && !empty( $this ) ) {
			$pMixed = $this->mInfo;
		}

		$ret = $pTitle;
		if( !empty( $pTitle ) && !empty( $pMixed ) ) {
			if( $gBitSystem->isPackageActive( 'treasury' ) ) {
				$ret = '<a title="'.htmlspecialchars( $pTitle ).'" href="'.TreasuryItem::getDisplayUrl( $pMixed['content_id'], $pMixed, $pStructureId ).'">'.htmlspecialchars( $pTitle ).'</a>';
			}
		}
		return $ret;
	}

	/**
	 * Generate URL to view this item in detail
	 * 
	 * @param numeric $pContentId Content id of the item we want to create the url for
	 * @param array $pMixed Mixed hash of information
	 * @access public
	 * @return URL
	 */
	function getDisplayUrl( $pContentId=NULL, $pMixed=NULL, $pStructureId=NULL ) {
		global $gBitSystem;
		$ret = NULL;
		// try to get the correct content_id from anywhere possible
		if( !@BitBase::verifyId( $pContentId ) && $this->isValid() ) {
			$pContentId = $this->mContentId;
		} elseif( !@BitBase::verifyId( $pContentId ) && !empty( $pMixed['content_id'] ) ) {
			$pContentId = $pMixed['content_id'];
		}

		if( @BitBase::verifyId( $pContentId ) ) {
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) || $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ) {
				$ret = TREASURY_PKG_URL.'file/'.$pContentId.( !empty( $pStructureId ) ? "/$pStructureId" : "" );
			} else {
				$ret = TREASURY_PKG_URL.'view_item.php?content_id='.$pContentId.( !empty( $pStructureId ) ? "&structure_id=$pStructureId" : "" );
			}
		}
		return $ret;
	}

	/**
	 * getContentIdFromAttachmentId 
	 * 
	 * @param array $pAttachmentId Attachment id of which you want the content Id
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getContentIdFromAttachmentId( $pAttachmentId ) {
		if( @BitBase::verifyId( $pAttachmentId )) {
			return $this->mDb->getOne( "SELECT `content_id` FROM `".BIT_DB_PREFIX."liberty_attachments` WHERE `attachment_id` = ?", array( $pAttachmentId ));
		}
	}

	/**
	 * Expunge data associated with an uploaded file
	 * 
	 * @access public
	 * @param should the attachment be expunged. Defaults to true.
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 * TODO: make it possible to remove only items when they are not part of other galleries
	 */
	function expunge() {
		if( $this->isValid() ) {
			$this->mDb->StartTrans();

			// Remove map entries
			$this->expungeItemMap();

			// now we can deal with the entry in liberty_content
			if( LibertyMime::expunge() ) {
				$this->mDb->CompleteTrans();
			} else {
				$this->mErrors['expunge'] = tra( 'The item could not be completely removed.' );
				$this->mDb->RollbackTrans();
			}
		}

		if( count( $this->mErrors ) != 0 ) {
			error_log( "Error deleting treasury item: ".vc( $this->mErrors ));
		}

		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * Expunge all references to galleries in the treasury map with reference to a given item id
	 * 
	 * @param  numeric $pItemContenId ID of content id where we want to remove map entries
	 * @access public
	 * @return void
	 */
	function expungeItemMap() {
		if( $this->isValid() ) {
			$this->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."treasury_map` WHERE `item_content_id`=?", array( $this->mContentId ) );
		}
	}

	/**
	 * isCommentable 
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function isCommentable() {
		global $gGallery;

		// if we have a loaded gallery, we just use that to work out if we can add comments to this image
		if( is_object( $gGallery ) ) {
			return $gGallery->isCommentable();
		}

		$ret = FALSE;
		if( $parents = $this->getParentGalleries() ) {
			$gal = current( $parents );
			$query = "SELECT `pref_value` FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id` = ? AND `pref_name` = ?";
			$ret = ( $this->mDb->getOne( $query, array( $gal['content_id'], 'allow_comments' )) == 'y' );
		}
		return $ret;
	}
}
?>
