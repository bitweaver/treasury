<?php
/**
 * @version:      $Header: /cvsroot/bitweaver/_bit_treasury/TreasuryItem.php,v 1.11 2006/09/07 21:38:52 bitweaver Exp $
 *
 * @author:       xing  <xing@synapse.plus.com>
 * @version:      $Revision: 1.11 $
 * @created:      Monday Jul 03, 2006   11:55:41 CEST
 * @package:      treasury
 * @copyright:    2003-2006 bitweaver
 * @license:      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/
define( 'TREASURYITEM_CONTENT_TYPE_GUID', 'treasuryitem' );
require_once( TREASURY_PKG_PATH.'TreasuryBase.php' );
require_once( TREASURY_PKG_PATH.'TreasuryGallery.php');

/**
 *   TreasuryItem 
 * 
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
	function TreasuryItem( $pContentId=NULL ) {
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
		$this->mContentId = $pContentId;
		$this->mContentTypeGuid = TREASURYITEM_CONTENT_TYPE_GUID;
	}

	/**
	 * load the treasury item
	 *
	 * @param $pExtras boolean - if set to true, treasury content is added as well
	 * @return bool TRUE on success, FALSE if it's not valid
	 * @access public
	 **/
	function load( $pExtras = FALSE ) {
		if( @BitBase::verifyId( $this->mContentId ) ) {
			global $gTreasurySystem;

			$ret = array();

			$selectSql = $joinSql = $orderSql = '';
			$whereSql = " WHERE tri.`content_id` = ? ";
			$bindVars[] = $this->mContentId;
			$this->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

			$ret = array();
			$query = "SELECT tri.`plugin_guid`, tct.`content_description`, uu.`login`, uu.`real_name`, la.`attachment_id`,
					lc.`content_id`, lc.`format_guid`, lc.`last_modified`, lc.`user_id`, lc.`title`, lc.`content_type_guid`, lc.`created`, lc.`data`, lch.`hits`
				FROM `".BIT_DB_PREFIX."treasury_item` tri
					INNER JOIN `".BIT_DB_PREFIX."treasury_map` trm ON ( trm.`item_content_id` = tri.`content_id` )
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = tri.`content_id` )
					INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` tct ON ( lc.`content_type_guid` = tct.`content_type_guid` )
					INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = lc.`user_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lch.`content_id` = lc.`content_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON ( la.`content_id` = tri.`content_id` )
				$joinSql $whereSql $orderSql";
			if( $aux = $this->mDb->getRow( $query, $bindVars ) ) {
				$load_function = $gTreasurySystem->getPluginFunction( $aux['plugin_guid'], 'load_function' );
				if( empty( $load_function ) || !$load_function( $aux ) ) {
					$this->mErrors['load'] = tra( 'There was a ploblem loading the file data.' );
				}
				$this->mInfo                 = $aux;
				$this->mInfo['title']        = $this->getTitle( $aux );
				$this->mInfo['display_url']  = TREASURY_PKG_URL.'view_item.php?content_id='.$aux['content_id'];

				// get the gallery information
				if( $pExtras ) {
					$galleryContentIds = $this->getGalleriesFromItemContentId();
					if( @is_array( $galleryContentIds ) ) {
						$gallery = new TreasuryGallery();
						foreach( $galleryContentIds as $gid ) {
							$gallery->mContentId = $gid;
							$gallery->load();
							$this->mInfo['galleries'][$gid] = $gallery->mInfo;
						}
					}
				}
			}
		}
	}

	/**
	 * getList 
	 * 
	 * @param array $pListHash 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getList( &$pListHash ) {
		global $gTreasurySystem;
		LibertyContent::prepGetList( $pListHash );

		$ret = $bindVars = array();
		$selectSql = $joinSql = $orderSql = $whereSql = "";
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		if( @BitBase::verifyId( $pListHash['gallery_content_id'] ) ) {
			$whereSql = " WHERE trm.`gallery_content_id` = ? ";
			$bindVars[] = $pListHash['gallery_content_id'];
		}

		if( !empty( $pListHash['title'] ) && is_string( $pListHash['title'] ) ) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " tri.`content_id` = lc.`content_id` AND UPPER( lc.`title` ) = ?";
			$joinSql = ", `".BIT_DB_PREFIX."liberty_content` lc";
			$bindVars[] = strtoupper( $pListHash['title'] );
		}

		if( !empty( $pListHash['sort_mode'] ) ) {
			$orderSql .= " ORDER BY ".$this->mDb->convert_sortmode( $pListHash['sort_mode'] )." ";
		} else {
			$orderSql .= " ORDER BY trm.`item_position` ASC ";
		}

		$ret = array();
		$query = "SELECT tri.`plugin_guid`, tct.`content_description`, uu.`login`, uu.`real_name`, la.`attachment_id`,
				lc.`content_id`, lc.`last_modified`, lc.`user_id`, lc.`title`, lc.`content_type_guid`, lc.`created`, lc.`data`, lch.`hits`
			FROM `".BIT_DB_PREFIX."treasury_item` tri
				INNER JOIN `".BIT_DB_PREFIX."treasury_map` trm ON ( trm.`item_content_id` = tri.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON ( la.`content_id` = tri.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = tri.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` tct ON ( lc.`content_type_guid` = tct.`content_type_guid` )
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = lc.`user_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lch.`content_id` = lc.`content_id` )
			$joinSql $whereSql $orderSql";
		$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );
		while( $aux = $result->fetchRow() ) {
			$aux['title'] = $this->getTitle( $aux );
			$load_function = $gTreasurySystem->getPluginFunction( $aux['plugin_guid'], 'load_function' );
			if( empty( $load_function ) || !$load_function( $aux ) ) {
				$this->mErrors['load'] = tra( 'There was a ploblem loading the file data.' );
			}
			$aux['display_url']  = TREASURY_PKG_URL.'view_item.php?content_id='.$aux['content_id'];
			$aux['display_link'] = '<a href="'.$aux['display_url'].'">'.$aux['title'].'</a>';
			$ret[] = $aux;
		}

		$query = "SELECT COUNT( trm.`item_content_id` )
			FROM `".BIT_DB_PREFIX."treasury_item` tri
				INNER JOIN `".BIT_DB_PREFIX."treasury_map` trm ON ( trm.`item_content_id` = tri.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON ( la.`content_id` = tri.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = tri.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` tct ON ( lc.`content_type_guid` = tct.`content_type_guid` )
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = lc.`user_id` )
			$joinSql $whereSql";
		$pListHash['cant'] = $this->mDb->getOne( $query, $bindVars );
		LibertyContent::postGetList( $pListHash );

		// TODO: do the cant query
		return( !empty( $this->mErrors ) ? $this->mErrors : $ret );
	}

	/**
	 * Get all galleries this item is part of
	 * 
	 * @access public
	 * @return array of gallery content ids
	 */
	function getGalleriesFromItemContentId() {
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
		global $gTreasurySystem, $gBitUser;
		// make sure all the data is in order
		if( $this->verify( $pStoreHash ) ) {
			// short hand
			$this->mDb->StartTrans();
			// if this is an update, we bypass the plugin stuff - no changes there
			if( !empty( $pStoreHash['item_store']['content_id'] ) ) {
				// ########## Update
				if( LibertyContent::store( $pStoreHash ) ) {
					// remove all related entries in the treasury map
					$this->expungeItemMap();

					// ---------- Map store
					foreach( $pStoreHash['map_store']['galleryContentIds'] as $gid ) {
						$storeRow = array(
							'gallery_content_id' => $gid,
							'item_content_id' => $pStoreHash['item_store']['content_id'],
						);
						$this->mDb->associateInsert( BIT_DB_PREFIX.'treasury_map', $storeRow );
					}

					// Call the appropriate plugin to deal with the upload
					if( !empty( $pStoreHash['upload_store']['upload'] ) ) {
						$guid = $gTreasurySystem->lookupMimeHandler( $pStoreHash['upload_store']['upload'] );
						$verify_function = $gTreasurySystem->getPluginFunction( $guid, 'verify_function' );
						if( !empty( $verify_function) && $verify_function( $pStoreHash['upload_store'] ) ) {
							$update_function = $gTreasurySystem->getPluginFunction( $guid, 'update_function' );
							if( empty( $update_function ) || !$update_function( $pStoreHash['upload_store'] ) ) {
								$this->mErrors = $pStoreHash['upload_store']['errors'];
							}
						}
					}
				}
			} else {
				// ########## Insert
				// call the appropriate plugin to deal with the upload
				$guid = $gTreasurySystem->lookupMimeHandler( $pStoreHash['upload_store']['upload'] );
				$verify_function = $gTreasurySystem->getPluginFunction( $guid, 'verify_function' );
				// verify the uploaded file using the plugin
				if( !empty( $verify_function) && $verify_function( $pStoreHash['upload_store'] ) ) {
					if( LibertyContent::store( $pStoreHash ) ) {
						// ---------- Item store
						// we can now insert the data into the item table
						$pStoreHash['item_store']['plugin_guid'] = $guid;
						$pStoreHash['item_store']['content_id'] = $pStoreHash['upload_store']['content_id'] = $pStoreHash['content_id'];
						$this->mDb->associateInsert( BIT_DB_PREFIX.'treasury_item', $pStoreHash['item_store'] );

						// ---------- Map store
						foreach( $pStoreHash['map_store']['galleryContentIds'] as $gid ) {
							$storeRow = array(
								'gallery_content_id' => $gid,
								'item_content_id' => $pStoreHash['item_store']['content_id'],
							);
							$this->mDb->associateInsert( BIT_DB_PREFIX.'treasury_map', $storeRow );
						}

						// ---------- Plugin Store
						// The plugin is responsible for:
						//       - thumbnail creation - icon.jpg (48x48), avatar.jpg (100x100), small.jpg (400x300)
						//       - storing file data in liberty_attachments and liberty_files (if you want to use liberty)
						$store_function = $gTreasurySystem->getPluginFunction( $guid, 'store_function' );
						if( empty( $store_function ) || !$store_function( $pStoreHash['upload_store'] ) ) {
							$this->mErrors = $pStoreHash['upload_store']['errors'];
						}
					}
				} else {
					$this->mErrors = $pStoreHash['upload_store']['errors'];
				}
			}

			$this->mDb->CompleteTrans();
		}

		// reset everything in case we're in a loop
		unset( $pStoreHash['content_store'] );
		unset( $pStoreHash['upload_store'] );
		unset( $pStoreHash['map_store'] );
		unset( $pStoreHash['item_store'] );
		unset( $pStoreHash['content_id'] );

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

		// ---------- Upload store - dealt with by plugin
		// Deal with the uploaded file
		if( @is_array( $pStoreHash['upload'] ) ) {
			if( !empty( $pStoreHash['upload']['tmp_name'] ) && empty( $pStoreHash['upload']['error'] ) ) {
				$pStoreHash['upload_store']['upload'] = $pStoreHash['upload'];
			} else {
				$this->mErrors['upload'] = tra( "There was a problem processing the uploaded file. Please try again." );
			}
			if( !empty( $pStoreHash['plugin'] ) ) {
				$pStoreHash['upload_store']['plugin'] = $pStoreHash['plugin'];
			}
		}

		// Make sure we know what to update
		if( $this->isValid() ) {
			$pStoreHash['upload_store']['content_id']      =
				$pStoreHash['content_store']['content_id'] =
				$pStoreHash['item_store']['content_id']    = $this->mContentId;
		} elseif( empty( $pStoreHash['upload_store']['upload']['tmp_name'] ) || !is_file( $pStoreHash['upload_store']['upload']['tmp_name'] ) ) {
			// Make sure the upload went well. If not, we break off this mellarky here.
			$this->mErrors['upload'] = tra( 'Uploading this file has destroyed this website - possibly even the entire internet.' );
		}


		// ---------- Item store
		// the item data is collected during the TreasuryItem::store() process

		// ---------- Map store
		// make sure we have at least one gallery where we can add the file
		if( empty( $pStoreHash['galleryContentIds'] ) ) {
			$pStoreHash['galleryContentIds'][] = $this->getDefaultGalleryId();
		}

		// make sure we have the correct permissions to upload to this gallery
		foreach( $pStoreHash['galleryContentIds'] as $gid ) {
			$gallery = new TreasuryGallery( NULL, $gid );
			if( $gallery->loadPermissions() ) {
				if( $gallery->hasUserPermission( 'p_treasury_upload_item' ) ) {
					$pStoreHash['map_store']['galleryContentIds'][] = $gid;
				}
			} else {
				$pStoreHash['map_store']['galleryContentIds'][] = $gid;
			}
		}

		if( empty( $pStoreHash['map_store']['galleryContentIds'] ) ) {
			$this->mErrors['store'] = tra( 'No gallery available to insert uploaded files into. Please check the permissions of the gallery.' );
		}

		// ---------- Content store
		// let's add a default title
		if( empty( $pStoreHash['title'] ) && !empty( $pStoreHash['upload']['name'] ) ) {
			if( preg_match( '/^[A-Z]:\\\/', $pStoreHash['upload']['name'] ) ) {
				// MSIE shit file names if passthrough via gigaupload, etc.
				// basename will not work - see http://us3.php.net/manual/en/function.basename.php
				$tmp = preg_split( "[\\\]", $pStoreHash['upload']['name'] );
				$defaultName = $tmp[count($tmp) - 1];
			} elseif( strpos( '.', $pStoreHash['upload']['name'] ) ) {
				list( $defaultName, $ext ) = explode( '.', $pStoreHash['upload']['name'] );
			} else {
				$defaultName = $pStoreHash['upload']['name'];
			}
			$pStoreHash['content_store']['title'] = str_replace( '_', ' ', substr( $defaultName, 0, strrpos( $defaultName, '.' ) ) );
		} elseif( !empty( $pStoreHash['title'] ) ) {
			$pStoreHash['content_store']['title'] = substr( $pStoreHash['title'], 0, 160 );
		}

		// sort out the description
		if( $this->isValid() && !empty( $this->mInfo['data'] ) && empty( $pStoreHash['edit'] ) ) {
			$pStoreHash['edit'] = '';
		} elseif( empty( $pStoreHash['edit'] ) ) {
			unset( $pStoreHash['edit'] );
		} else {
			$pStoreHash['content_store']['data'] = $pStoreHash['edit'];
			//$pStoreHash['edit'] = substr( $pStoreHash['edit'], 0, 500 );
		}

		return( count( $this->mErrors ) == 0 );
	}

	function hasGalleryPermissions( $pPermName, $pFatalIfFalse = FALSE, $pFatalMessage = NULL ) {
		global $gBitSystem, $gBitUser;
		$ret = FALSE;
		if( $this->isValid() && !empty( $pPermName ) ) {
			// get all gallery content ids
			$galleryContentIds = $this->getGalleriesFromItemContentId();
			if( @is_array( $galleryContentIds ) ) {
				$gallery = new TreasuryGallery();
				foreach( $galleryContentIds as $gid ) {
					// reduce load: we don't need to fully load the gallery to load the permissions
					$gallery->mContentId = $gid;
					if( $gallery->hasUserPermission( $pPermName ) ) {
						// we only need one gallery that allows us to download the file
						return TRUE;
					}
				}
			}
		}

		if( !$ret && $pFatalIfFalse ) {
			$gBitSystem->fatalPermission( $pPermName, $pFatalMessage );
		}
		return $ret;
	}

	/**
	 * Returns HTML link to display a gallery or item
	 *
	 * @param string $pTitle Title of the file we want to view
	 * @param array $pMixed Hash of data used to create link
	 * @return Full HTML link to file
	 **/
	function getDisplayLink( $pTitle=NULL, $pMixed=NULL ) {
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
				$ret = '<a title="'.htmlspecialchars( $pTitle ).'" href="'.TreasuryItem::getDisplayUrl( $pMixed['content_id'] ).'">'.htmlspecialchars( $pTitle ).'</a>';
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
	function getDisplayUrl( $pContentId=NULL, $pMixed=NULL ) {
		global $gBitSystem;
		$ret = NULL;
		// try to get the correct content_id from anywhere possible
		if( !@BitBase::verifyId( $pContentId ) && $this->isValid() ) {
			$pContentId = $this->mContentId;
		} elseif( !@BitBase::verifyId( $pContentId ) && !empty( $pMixed['content_id'] ) ) {
			$pContentId = $pMixed['content_id'];
		}

		if( @BitBase::verifyId( $pContentId ) ) {
			$rewrite_tag = $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ? 'view/' : '';
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) || $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ) {
				$ret = TREASURY_PKG_URL.$rewrite_tag.$pContentId;
			} else {
				$ret = TREASURY_PKG_URL.'view_item.php?content_id='.$pContentId;
			}
		}
		return $ret;
	}

	/**
	 * Expunge data associated with an uploaded file
	 * 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function expunge() {
		global $gTreasurySystem;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();

			// Remove map entries
			$this->expungeItemMap();

			// Remove item entry
			$query = "DELETE FROM `".BIT_DB_PREFIX."treasury_item` WHERE `content_id` = ?";
			$rs = $this->mDb->query($query, array( $this->mContentId ) );

			// let the plugin do its thing
			$expunge_function = $gTreasurySystem->getPluginFunction( $this->mInfo['plugin_guid'], 'expunge_function' );
			if( !empty( $expunge_function ) && $expunge_function( $this->mInfo ) ) {
				// remove the remaining entries in liberty tables
				if( LibertyContent::expunge() ) {
					$this->mDb->CompleteTrans();
				} else {
					$this->mDb->RollbackTrans();
				}
			} else {
				$this->mErrors['expunge'] = $this->mInfo['errors'];
				$this->mDb->RollbackTrans();
			}
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
}
?>
