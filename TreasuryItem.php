<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/TreasuryItem.php,v 1.51 2007/07/05 06:14:21 squareing Exp $
 *
 * @author       xing  <xing@synapse.plus.com>
 * @version      $Revision: 1.51 $
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
	function TreasuryItem( $pDummy = NULL, $pContentId = NULL, $pPrimaryAttachmentId = NULL ) {
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
		$this->mPrimaryAttachmentId = $pPrimaryAttachmentId;
		$this->mContentTypeGuid = TREASURYITEM_CONTENT_TYPE_GUID;
	}

	/**
	 * load the treasury item
	 *
	 * @param $pPluginParameters parameters passed on to the plugin during laod
	 * @return bool TRUE on success, FALSE if it's not valid
	 * @access public
	 **/
	function load( $pPluginParameters = NULL ) {
		if( @BitBase::verifyId( $this->mContentId ) || @BitBase::verifyId( $this->mPrimaryAttachmentId )) {
			global $gTreasurySystem, $gBitSystem;

			$ret = array();

			$selectSql = $joinSql = $orderSql = '';
			if( @BitBase::verifyId( $this->mContentId )) {
				$whereSql = " WHERE tri.`content_id` = ? ";
				$bindVars[] = $this->mContentId;
			} elseif( @BitBase::verifyId( $this->mPrimaryAttachmentId )) {
				$whereSql = " WHERE lc.`primary_attachment_id` = ? AND lc.`content_type_guid` = ?";
				$bindVars[] = $this->mPrimaryAttachmentId;
				$bindVars[] = TREASURYITEM_CONTENT_TYPE_GUID;
			}
			$this->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

			$ret = array();
			$query = "
				SELECT
					tri.`plugin_guid`,
					lct.`content_description`,
					uu.`login`, uu.`real_name`,
					lc.`primary_attachment_id` AS `attachment_id`, lc.`content_id`, lc.`format_guid`, lc.`last_modified`, lc.`user_id`, lc.`title`, lc.`content_type_guid`, lc.`created`, lc.`data`,
					lch.`hits`
					$selectSql
				FROM `".BIT_DB_PREFIX."treasury_item` tri
					INNER JOIN `".BIT_DB_PREFIX."treasury_map` trm ON ( trm.`item_content_id` = tri.`content_id` )
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = tri.`content_id` )
					INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON ( lc.`content_type_guid` = lct.`content_type_guid` )
					INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = lc.`user_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lch.`content_id` = lc.`content_id` )
				$joinSql $whereSql $orderSql";
			if( $aux = $this->mDb->getRow( $query, $bindVars ) ) {
				// this is passed by reference as it's updated by the load function
				$this->mInfo                   = &$aux;
				$this->mContentId              = $aux['content_id'];
				$this->mPrimaryAttachmentId    = $aux['attachment_id'];
				$this->mInfo['title']          = $this->getTitle( $aux );
				$this->mInfo['display_url']    = $this->getDisplayUrl();
				$this->mInfo['allow_comments'] = $gBitSystem->isFeatureActive( "treasury_".$this->mInfo['plugin_guid']."_comments" );

				// we might have content preferences set by some plugin
				LibertyContent::load();

				// load details using plugin
				$load_function = $gTreasurySystem->getPluginFunction( $aux['plugin_guid'], 'load_function' );
				if( empty( $load_function ) || !$load_function( $aux, $this, $pPluginParameters ) ) {
					$this->mErrors['load'] = tra( 'There was a problem loading the file data.' );
				}

				if( !isset( $this->mInfo['download_url'] )) {
					$this->mInfo['download_url'] = $this->getDownloadUrl( $this->mInfo );
				}
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
		global $gTreasurySystem;
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
			$whereSql  .= " tri.`content_id` = lc.`content_id` AND UPPER( lc.`title` ) = ?";
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

		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		$ret = array();
		$query = "
			SELECT tri.`plugin_guid`,
				lct.`content_description`,
				uu.`login`, uu.`real_name`,
				lc.`primary_attachment_id` AS `attachment_id`, lc.`content_id`, lc.`last_modified`, lc.`user_id`, lc.`title`, lc.`content_type_guid`, lc.`created`, lc.`data`,
				lch.`hits` $selectSql
			FROM `".BIT_DB_PREFIX."treasury_item` tri
				INNER JOIN `".BIT_DB_PREFIX."treasury_map` trm ON ( trm.`item_content_id` = tri.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = tri.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON ( lc.`content_type_guid` = lct.`content_type_guid` )
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = lc.`user_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lch.`content_id` = lc.`content_id` )
			$joinSql $whereSql $orderSql";
		$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );
		while( $aux = $result->fetchRow() ) {
			$aux['title'] = $this->getTitle( $aux );
			if( $load_function = $gTreasurySystem->getPluginFunction( $aux['plugin_guid'], 'load_function' )) {
				// this is needed for php < 5
				$dummy = array();
				if( !$load_function( $aux, $dummy )) {
					$this->mErrors['plugin_load'] = tra( 'There was a problem loading the file data.' );
				}
			} else {
				$this->mErrors['load_function'] = tra( 'No suitable load function found.' );
			}
			$aux['display_url']  = $this->getDisplayUrl( $aux['content_id'], $aux, $pStructureId );
			$aux['display_link'] = $this->getDisplayLink( $aux['title'], $aux, $pStructureId );
			if( is_file( $aux['source_file'] )) {
				$aux['download_url'] = $this->getDownloadUrl( $aux );
			}
			$ret[] = $aux;
		}

		$query = "SELECT COUNT( trm.`item_content_id` )
			FROM `".BIT_DB_PREFIX."treasury_item` tri
				INNER JOIN `".BIT_DB_PREFIX."treasury_map` trm ON ( trm.`item_content_id` = tri.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = tri.`content_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_content_types` lct ON ( lc.`content_type_guid` = lct.`content_type_guid` )
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( uu.`user_id` = lc.`user_id` )
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
				if( LibertyContent::store( $pStoreHash['content_store'] ) ) {
					// remove all related entries in the treasury map
					$this->expungeItemMap();

					// ---------- Map store
					foreach( $pStoreHash['map_store']['galleryContentIds'] as $gcid ) {
						$storeRow = array(
							'gallery_content_id' => $gcid,
							'item_content_id' => $pStoreHash['item_store']['content_id'],
						);
						$this->mDb->associateInsert( BIT_DB_PREFIX.'treasury_map', $storeRow );
					}

					// Call the appropriate plugin to deal with the upload
					// if this is a newly uploaded file, we fetch the new plugin handler
					$guid = NULL;
					if( !empty( $pStoreHash['upload_store']['upload']['tmp_name'] ) ) {
						$guid = $gTreasurySystem->lookupMimeHandler( $pStoreHash['upload_store']['upload'] );
					} else {
						$guid = $this->mInfo['plugin_guid'];
					}

					// now we can pass the info on to the update function
					if( $verify_function = $gTreasurySystem->getPluginFunction( $guid, 'verify_function' )) {
						if( $verify_function( $pStoreHash['upload_store'] ) ) {
							if( $update_function = $gTreasurySystem->getPluginFunction( $guid, 'update_function' )) {
								if( !$update_function( $pStoreHash['upload_store'], $this ) ) {
									$this->mErrors = array_merge( $this->mErrors, $pStoreHash['upload_store']['errors'] );
								}
							} else {
								$this->mErrors['update_function'] = tra( 'No suitable update function found.' );
							}
						}
					} else {
						$this->mErrors['verify_function'] = tra( 'No suitable verify function found.' );
					}
				}
			} else {
				// ########## Insert
				// call the appropriate plugin to deal with the upload
				$guid = $gTreasurySystem->lookupMimeHandler( $pStoreHash['upload_store']['upload'] );
				if( $verify_function = $gTreasurySystem->getPluginFunction( $guid, 'verify_function' )) {
					// verify the uploaded file using the plugin
					if( $verify_function( $pStoreHash['upload_store'] ) ) {
						if( LibertyContent::store( $pStoreHash['content_store'] ) ) {
							// ---------- Item store
							// we can now insert the data into the item table
							$pStoreHash['item_store']['plugin_guid'] = $guid;
							$pStoreHash['item_store']['content_id'] = $pStoreHash['upload_store']['content_id'] = $pStoreHash['content_store']['content_id'];
							$this->mDb->associateInsert( BIT_DB_PREFIX.'treasury_item', $pStoreHash['item_store'] );

							// ---------- Map store
							foreach( $pStoreHash['map_store']['galleryContentIds'] as $gcid ) {
								$storeRow = array(
									'gallery_content_id' => $gcid,
									'item_content_id' => $pStoreHash['item_store']['content_id'],
								);
								$this->mDb->associateInsert( BIT_DB_PREFIX.'treasury_map', $storeRow );
							}

							// ---------- Plugin Store
							// The plugin is responsible for:
							//       - thumbnail creation - icon.jpg (48x48), avatar.jpg (100x100), small.jpg (400x300)
							//       - storing file data in liberty_attachments and liberty_files (if you want to use liberty)
							if( $store_function = $gTreasurySystem->getPluginFunction( $guid, 'store_function' )) {
								if( !$store_function( $pStoreHash['upload_store'], $this )) {
									$this->mErrors = array_merge( $this->mErrors, $pStoreHash['upload_store']['errors'] );
								}
							} else {
								$this->mErrors['verify_function'] = tra( 'No suitable store function found.' );
							}
						}
					} else {
						$this->mErrors = array_merge( $this->mErrors, $pStoreHash['upload_store']['errors'] );
					}
				} else {
					$this->mErrors['verify_function'] = tra( 'No suitable verify function found.' );
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
		}

		// make sure the plugin has it's custom information to deal with as needed
		if( !empty( $pStoreHash['plugin'] ) ) {
			$pStoreHash['upload_store']['plugin'] = $pStoreHash['plugin'];
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
		foreach( $pStoreHash['galleryContentIds'] as $gcid ) {
			$gallery = new TreasuryGallery( NULL, $gcid );
			if( $gallery->hasUserPermission( 'p_treasury_upload_item' ) ) {
				$pStoreHash['map_store']['galleryContentIds'][] = $gcid;
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
			if( strpos( $defaultName, '.' ) ) {
				$pStoreHash['content_store']['title'] = str_replace( '_', ' ', substr( $defaultName, 0, strrpos( $defaultName, '.' ) ) );
			} else {
				$pStoreHash['content_store']['title'] = str_replace( '_', ' ', $defaultName );
			}
		} elseif( !empty( $pStoreHash['title'] ) ) {
			$pStoreHash['content_store']['title'] = substr( $pStoreHash['title'], 0, 160 );
		}

		// sort out the description
		if( $this->isValid() && !empty( $this->mInfo['data'] ) && empty( $pStoreHash['edit'] ) ) {
			$pStoreHash['edit'] = '';
		} elseif( empty( $pStoreHash['edit'] ) ) {
			unset( $pStoreHash['edit'] );
		} else {
			$pStoreHash['content_store']['edit'] = $pStoreHash['edit'];
			//$pStoreHash['edit'] = substr( $pStoreHash['edit'], 0, 500 );
		}

		return( count( $this->mErrors ) == 0 );
	}

	function hasGalleryPermissions( $pPermName ) {
		global $gBitSystem, $gBitUser;
		$ret = FALSE;
		if( $this->isValid() && !empty( $pPermName ) ) {
			// get all gallery content ids
			$galleryContentIds = $this->getGalleriesFromItemContentId();
			if( !empty( $galleryContentIds ) && is_array( $galleryContentIds ) ) {
				$gallery = new TreasuryGallery();
				foreach( $galleryContentIds as $gcid ) {
					// reduce load: we don't need to fully load the gallery to load the permissions
					$gallery->mContentId = $gcid;
					if( $gallery->hasUserPermission( $pPermName ) ) {
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
	 * @param numeric $pFileHash['content_id'] Content id of the item we want to create the url for
	 * @param numeric $pFileHash['source_file'] Relative path to file in question
	 * @param array $pMixed Mixed hash of information
	 * @access public
	 * @return URL
	 */
	function getDownloadUrl( $pFileHash = NULL ) {
		global $gBitSystem;
		$ret = NULL;
		// try to get the correct content_id from anywhere possible
		if( @BitBase::verifyId( $pFileHash['content_id'] )) {
			$contentId = $pFileHash['content_id'];
		} elseif( $this->isValid() ) {
			$contentId = $this->mContentId;
		}

		// if we have a source_file to check and it doesn't exist, we don't return download url
		if( !empty( $pFileHash['source_file'] ) && !is_file( $pFileHash['source_file'] )) {
			$contentId = NULL;
		}

		if( @BitBase::verifyId( $contentId ) ) {
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) || $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ) {
				$ret = TREASURY_PKG_URL.'download/'.$contentId;
			} else {
				$ret = TREASURY_PKG_URL.'download.php?content_id='.$contentId;
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
	 * Expunge data associated with an uploaded file
	 * 
	 * @access public
	 * @param should the attachment be expunged. Defaults to true.
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 * TODO: make it possible to remove only items when they are not part of other galleries
	 */
	function expunge( $pExpungeAttachment = TRUE ) {
		global $gTreasurySystem;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();

			// Remove map entries
			$this->expungeItemMap();

			// Remove item entry
			$query = "DELETE FROM `".BIT_DB_PREFIX."treasury_item` WHERE `content_id` = ?";
			$rs = $this->mDb->query( $query, array( $this->mContentId ));

			// deal with the attachments if needed
			if( $pExpungeAttachment ) {
				// let the plugin do its thing
				$expunge_function = $gTreasurySystem->getPluginFunction( $this->mInfo['plugin_guid'], 'expunge_function' );
				if( empty( $expunge_function ) || !$expunge_function( $this->mInfo )) {
					// plugin passes errors into [errors] by reference
					$this->mErrors['expunge_plugin'] = $this->mInfo['errors'];
				}
			}

			// now we can deal with the entry in liberty_content
			if( count( $this->mErrors ) == 0 && LibertyContent::expunge() ) {
				$this->mDb->CompleteTrans();
			} else {
				$this->mErrors['expunge'] = tra( 'The item could not be removed completely' );
				$this->mDb->RollbackTrans();
			}
		}
		if( count( $this->mErrors ) != 0 ) {
			vd($this->mErrors);
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * Notification sent by LibertyAttachable that an attachment is being expunged.
	 * 
	 * @param  numeric $pAttachmentId ID of the attachmnet being deleted.
	 * @param  array $pContentIdArray IDs of the content that are attached to the attachment
	 * @access private
	 * @return void
	 */
	function expungingAttachment( $pAttachmentId, $pContentIdArray = FALSE ) {
		foreach( $pContentIdArray as $id ) {
			$this->mContentId = $id;
			// Unfortunately we have to load in order to get some info in place. :(
			if( $this->load() ) {
				// It is important that we not delete the attachment since it is already being deleted.
				$this->expunge( FALSE );
			}
		}
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
