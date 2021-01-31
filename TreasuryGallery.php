<?php
/**
 * @version      $Header$
 *
 * @author       xing  <xing@synapse.plus.com>
 * @version      $Revision$
 * created      Monday Jul 03, 2006   11:53:42 CEST
 * @package      treasury
 * @copyright    2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/

/**
 * Setup
 */
define( 'TREASURYGALLERY_CONTENT_TYPE_GUID', 'treasurygallery' );
require_once( TREASURY_PKG_PATH.'TreasuryBase.php' );

/**
 *   TreasuryGallery
 *
 * @package treasury
 * @uses TreasuryBase
 */
class TreasuryGallery extends TreasuryBase {
	/**
	 * Initiate class
	 *
	 * @param $pContentId content id of the treasury - use either one of the ids.
	 * @param $pStructureId structure id of the treasury - use either one of the ids.
	 * @return none
	 * @access public
	 **/
	function TreasuryGallery( $pStructureId = NULL, $pContentId = NULL ) {
		TreasuryBase::TreasuryBase();
		$this->registerContentType( TREASURYGALLERY_CONTENT_TYPE_GUID, array(
			'content_type_guid' => TREASURYGALLERY_CONTENT_TYPE_GUID,
			'content_name' => 'File Gallery',
			'content_name_plural' => 'File Galleries',
			'handler_class' => 'TreasuryGallery',
			'handler_package' => 'treasury',
			'handler_file' => 'TreasuryGallery.php',
			'maintainer_url' => 'http://www.bitweaver.org'
		) );
		$this->mContentId = $pContentId;
		$this->mStructureId = $pStructureId;
		$this->mContentTypeGuid = TREASURYGALLERY_CONTENT_TYPE_GUID;

		// Permission setup
		$this->mViewContentPerm  = 'p_treasury_view_gallery';
		$this->mCreateContentPerm  = 'p_treasury_create_gallery';
		$this->mUpdateContentPerm  = 'p_treasury_update_gallery';
		$this->mAdminContentPerm = 'p_treasury_admin';
	}

	/**
	 * load the treasury gallery
	 *
	 * @param $pExtras boolean - if set to true, treasury content is added as well
	 * @return bool TRUE on success, FALSE if it's not valid
	 * @access public
	 **/
	function load( $pContentId = NULL, $pPluginParams = NULL ) {
		if( @BitBase::verifyId( $this->mContentId ) || @BitBase::verifyId( $this->mStructureId ) ) {
			global $gBitSystem;

			$lookupColumn = ( @BitBase::verifyId( $this->mContentId ) ? 'lc.`content_id`' : 'ls.`structure_id`' );
			$lookupId = ( @BitBase::verifyId( $this->mContentId ) ? $this->mContentId : $this->mStructureId );

			$bindVars[] = $lookupId;
			$selectSql = $joinSql = $whereSql = '';
			$this->getServicesSql( 'content_load_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

			$query = "SELECT trg.*, ls.`root_structure_id`, ls.`parent_id`,
				lc.`title`, lc.`format_guid`, lc.`data`, lc.`user_id`, lc.`content_type_guid`,
				uue.`login` AS modifier_user, uue.`real_name` AS modifier_real_name,
				uuc.`login` AS creator_user, uuc.`real_name` AS creator_real_name $selectSql
				FROM `".BIT_DB_PREFIX."treasury_gallery` trg
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = trg.`content_id` )
					LEFT JOIN `".BIT_DB_PREFIX."liberty_structures` ls ON ( ls.`structure_id` = trg.`structure_id` )
					LEFT JOIN `".BIT_DB_PREFIX."users_users` uue ON ( uue.`user_id` = lc.`modifier_user_id` )
					LEFT JOIN `".BIT_DB_PREFIX."users_users` uuc ON ( uuc.`user_id` = lc.`user_id` ) $joinSql
				WHERE $lookupColumn = ? $whereSql";
			$result = $this->mDb->query( $query, $bindVars );

			if( $result && $row = $result->fetchRow() ) {
				$this->mInfo                  = $row;
				$this->mContentId             = $row['content_id'];
				$this->mStructureId           = $row['structure_id'];
				$this->mInfo['user']          = $row['creator_user'];
				$this->mInfo['real_name']     = ( isset( $row['creator_real_name'] ) ? $row['creator_real_name'] : $row['creator_user'] );
				$this->mInfo['display_name']  = BitUser::getDisplayNameFromHash( FALSE, $this->mInfo );
				$this->mInfo['editor']        = ( isset( $row['modifier_real_name'] ) ? $row['modifier_real_name'] : $row['modifier_user'] );
				$this->mInfo['display_url']   = $this->getDisplayUrl();
				$this->mInfo['thumbnail_url'] = liberty_fetch_thumbnails( array(
					'storage_path' => $this->getGalleryThumbBaseUrl(),
					'mime_image'   => FALSE
				));

				// get extra information if required
				if( $pPluginParams['extras'] ) {
					$this->mInfo['gallery_path']         = $this->getGalleryPath();
					$this->mInfo['gallery_display_path'] = $this->getDisplayPath( $this->mInfo['gallery_path'] );
				}
				LibertyContent::load();

				// parse the data after parent load so we have our html prefs
				$this->parseData();
			}
		}
		return( count( $this->mInfo ) );
	}

	/**
	 * Load all uploaded items in this gallery
	 *
	 * @param array $pListHash ListHash is passed on to TreasuryItem::getList();
	 * @access public
	 * @return TRUE on success, FALSE on failure - populates $this->mItems
	 */
	function loadItems( &$pListHash ) {
		$ret = FALSE;
		if( $this->isValid() ) {
			require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
			$treasuryItem = new TreasuryItem();
			if( empty( $pListHash['gallery_content_id'] ) ) {
				$pListHash['gallery_content_id'] = $this->mContentId;
			}

			if( $this->mItems = $treasuryItem->getList( $pListHash, $this->mStructureId )) {
				$ret = TRUE;
			} elseif( !empty( $treasuryItem->mErrors )) {
				error_log( "Error loading treasury items: ".vc( $treasuryItem->mErrors ));
			}
		}
		return $ret;
	}

	/**
	 * Get list of all treasury galleries
	 *
	 * @param $pListHash contains array of items used to limit search results
	 * @param $pListHash[sort_mode] column and orientation by which search results are sorted
	 * @param $pListHash[find] search for a gallery title - case insensitive
	 * @param $pListHash[max_records] maximum number of rows to return
	 * @param $pListHash[offset] number of results data is offset by
	 * @param $pListHash[title] gallery name
	 * @param $pListHash[parent_id] gallery parent_id
	 * @param $pListHash[get_sub_tree] get the subtree to every gallery
	 * @access public
	 * @return List of galleries
	 **/
	function getList( &$pListHash ) {
		global $gBitDbType, $gBitSystem, $gBitUser;
		LibertyContent::prepGetList( $pListHash );

		$ret = $bindVars = array();
		$selectSql = $joinSql = $orderSql = $whereSql = '';

		if( @BitBase::verifyId( $pListHash['root_structure_id'] ) ) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " ls.`root_structure_id`=? ";
			$bindVars[] = $pListHash['root_structure_id'];
		}

		if( !empty( $pListHash['get_sub_tree'] ) ) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " ls.`structure_id`=ls.`root_structure_id` ";
		}

		if( !empty( $pListHash['find'] ) ) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= " UPPER( lc.`title` ) LIKE ? ";
			$bindVars[] = '%'.strtoupper( $pListHash['find'] ).'%';
		}

		if ( isset( $pListHash['parent_id'] ) ) {
			$whereSql .= empty( $whereSql ) ? ' WHERE ' : ' AND ';
			$whereSql .= ' ls.`parent_id` = ? ';
			$bindVars[] = $pListHash['parent_id'];
		}

		if( !empty( $pListHash['sort_mode'] ) ) {
			$orderSql .= " ORDER BY ".$this->mDb->convertSortmode( $pListHash['sort_mode'] )." ";
		} else {
			// default sort mode makes list look nice
			$orderSql .= " ORDER BY ls.`root_structure_id`, ls.`structure_id` ASC";
		}

		// update query with service sql
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars );

		// get the number of files in this gallery
		if( $gBitDbType != 'mysql' && $gBitDbType != 'mysqli' ) {
			$subselect = ", (
				SELECT COUNT( trm.`item_content_id` )
				FROM `".BIT_DB_PREFIX."treasury_map` trm
				WHERE trm.`gallery_content_id`=trg.`content_id`
			) AS item_count";
		} else {
			$subselect = "";
		}

		// don't fetch trg.`is_private` for list, because it conflicts with gks.is_private
		// and it's not used on list anyway
		$query = "
			SELECT trg.`content_id`, trg.`structure_id`,
			ls.`root_structure_id`, ls.`parent_id`,
			lc.`title`, lc.`data`, lc.`user_id`, lc.`content_type_guid`, lc.`created`, lc.`format_guid`, lch.`hits`,
			uue.`login` AS modifier_user, uue.`real_name` AS modifier_real_name,
			uuc.`login` AS creator_user, uuc.`real_name` AS creator_real_name $subselect $selectSql
			FROM `".BIT_DB_PREFIX."treasury_gallery` trg
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = trg.`content_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lc.`content_id` = lch.`content_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uue ON ( uue.`user_id` = lc.`modifier_user_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uuc ON ( uuc.`user_id` = lc.`user_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_structures` ls ON ( ls.`structure_id` = trg.`structure_id` )
				$joinSql
			$whereSql
			$orderSql";

		$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );

		if( !empty( $pListHash['get_sub_tree'] ) ) {
			$struct = new LibertyStructure();
		}

		while( $aux = $result->fetchRow() ) {
			$hasUserPerm = TRUE;
			// check to see if we have premissions to do someing specific with this gallery
			if( !empty( $pListHash['content_permission'] ) ) {
				$gal = new TreasuryGallery( NULL, $aux['content_id'] );
			if( !$gal->hasUserPermission( $pListHash['content_permission'] )) {
					$hasUserPerm = FALSE;
				}
			}

			if( $hasUserPerm ) {
				$content_ids[]             = $aux['content_id'];
				$aux['user']               = $aux['creator_user'];
				$aux['real_name']          = ( isset( $aux['creator_real_name'] ) ? $aux['creator_real_name'] : $aux['creator_user'] );
				$aux['editor']             = ( isset( $aux['modifier_real_name'] ) ? $aux['modifier_real_name'] : $aux['modifier_user'] );
				$aux['display_name']       = BitUser::getDisplayNameFromHash( FALSE, $aux );
				$aux['display_url']        = self::getDisplayUrlFromHash( $aux );
				$aux['display_link']       = $this->getDisplayLink( $aux['title'], $aux );
				$aux['thumbnail_url']      = liberty_fetch_thumbnails( array(
					'storage_path' => $this->getGalleryThumbBaseUrl( $aux['content_id'] ),
					'mime_image'   => FALSE
				));
				// deal with the parsing
				$parseHash['format_guid']  = $aux['format_guid'];
				$parseHash['content_id']   = $aux['content_id'];
				$parseHash['user_id']      = $aux['user_id'];
				$parseHash['data']         = $aux['data'];
				$aux['parsed_data']        = self::parseDataHash( $parseHash );

				// sucky additional query to fetch item number without subselect
				if( $gBitDbType == 'mysql' || $gBitDbType == 'mysqli' ) {
					$item_count_query = "SELECT COUNT( trm.`item_content_id` ) FROM `".BIT_DB_PREFIX."treasury_map` trm WHERE trm.`gallery_content_id`=?";
					$aux['item_count'] = $this->mDb->getOne( $item_count_query, array( $aux['content_id'] ));
				}

				if( !empty( $pListHash['get_sub_tree'] ) ) {
					$aux['subtree']        = $struct->getSubTree( $aux['structure_id'] );
				}
				$ret[$aux['content_id']]   = $aux;
			}
		}

		$query = "SELECT COUNT( lc.`title` )
			FROM `".BIT_DB_PREFIX."treasury_gallery` trg
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( lc.`content_id` = trg.`content_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uue ON ( uue.`user_id` = lc.`modifier_user_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_users` uuc ON ( uuc.`user_id` = lc.`user_id` )
				INNER JOIN `".BIT_DB_PREFIX."liberty_structures` ls ON ( ls.`structure_id` = trg.`structure_id` )
			$joinSql $whereSql";
		$pListHash['cant'] = $this->mDb->getOne( $query, $bindVars );

		LibertyContent::postGetList( $pListHash );
		return $ret;
	}

	/**
	 * Store TreasuryGallery
	 *
	 * @param $pParamHash contains all data to store the gallery
	 * @param $pParamHash[title] title of the new gallery
	 * @param $pParamHash[edit] description of the gallery
	 * @param $pParamHash[root_structure_id] if this is set, it will add the gallery to this structure. if it's not set, a new structure / top level gallery is created
	 * @param $pParamHash[parent_id] set the structure_id that will server as the parent in the structure
	 * @return bool TRUE on success, FALSE if store could not occur. If FALSE, $this->mErrors will have reason why
	 * @access public
	 **/
	function store( &$pParamHash ) {
		$this->mDb->StartTrans();
		if( $this->verify( $pParamHash ) && LibertyContent::store( $pParamHash ) ) {
			$table = BIT_DB_PREFIX."treasury_gallery";

			// this really confusing, strange order way of saving items is due to strange behaviour of GenID
			// probably has to do with not null default nextval('public.liberty_structures_id_seq'::text)
			if( !empty( $pParamHash['update'] ) ) {
				if( !empty( $pParamHash['gallery_store'] ) ) {
					$result = $this->mDb->associateUpdate( $table, $pParamHash['gallery_store'], array( "content_id" => $this->mContentId ));
				}
				$pParamHash['structure_location_id'] = $this->mStructureId;
			} else {
				// update the gallery_store and structure_store content_id with the one from LibertyMime::store()
				$pParamHash['structure_store']['content_id'] = $pParamHash['content_id'];
				$pParamHash['gallery_store']['content_id'] = $pParamHash['content_id'];

				// we need to store the new structure node now
				global $gStructure;
				// create new object if needed
				if( empty( $gStructure ) ) {
					$gStructure = new LibertyStructure();
				}
				$pParamHash['structure_location_id'] = $gStructure->storeNode( $pParamHash['structure_store'] );

				// get the corrent structure_id
				// structure_id has to be done like this since it's screwed up in the schema
				$pParamHash['gallery_store']['structure_id'] =  $this->mDb->getOne( "SELECT MAX( `structure_id` ) FROM `".BIT_DB_PREFIX."liberty_structures`" );
				$result = $this->mDb->associateInsert( $table, $pParamHash['gallery_store'] );
			}

			$this->mDb->CompleteTrans();

			// process image upload
			if( empty( $this->mErrors )) {
				// now deal with the uploaded image
				if( !empty( $pParamHash['thumb']['tmp_name'] )) {
					$checkFunc = liberty_get_function( 'can_thumbnail' );
					if( $checkFunc( $pParamHash['thumb']['type'] )) {
						$fileHash = $pParamHash['thumb'];
						$fileHash['dest_branch'] = $this->getGalleryThumbBaseUrl();
						$fileHash['source_file'] = $fileHash['tmp_name'];
						liberty_clear_thumbnails( $fileHash );
						liberty_generate_thumbnails( $fileHash );
					} else {
						$this->mErrors['thumb'] = tra( "The file you uploaded doesn't appear to be a valid image. The reported mime type is" ).": ".$pParamHash['thumb']['type'];
					}
				}
			}

			$this->load();
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * Verify, clean up and prepare data to be stored
	 *
	 * @param $pParamHash all information that is being stored. will update $pParamHash by reference with fixed array of itmes
	 * @return bool TRUE on success, FALSE if store could not occur. If FALSE, $this->mErrors will have reason why
	 * @access private
	 **/
	function verify( &$pParamHash ) {
		// make sure we're all loaded up if everything is valid
		if( $this->isValid() && empty( $this->mInfo ) ) {
			$this->load();
		}

		// It is possible a derived class set this to something different
		if( empty( $pParamHash['content_type_guid'] ) ) {
			$pParamHash['content_type_guid'] = $this->mContentTypeGuid;
		}

		// make sure we know this is an update
		if( @BitBase::verifyId( $this->mContentId ) ) {
			$pParamHash['content_id'] = $this->mContentId;
			$pParamHash['update']     = TRUE;
		}

		// ---------- Gallery Store
		$pParamHash['gallery_store']['is_private'] = empty( $pParamHash['is_private'] ) ? 'n' : 'y';

		// ---------- Content store
		// check for name issues, truncate length if too long
		if( !empty( $pParamHash['title'] ) )  {
			if( !@BitBase::verifyId( $this->mContentId ) ) {
				$pParamHash['content_store']['title'] = substr( $pParamHash['title'], 0, BIT_CONTENT_MAX_TITLE_LEN );
			} else {
				$pParamHash['content_store']['title'] = ( isset( $pParamHash['title'] ) ) ? substr( $pParamHash['title'], 0, BIT_CONTENT_MAX_TITLE_LEN ) : $this->mInfo['title'];
			}
		} else {
			$this->mErrors['title'] = 'You must enter a name for this gallery.';
		}

		// sort out the description
		if( $this->isValid() && !empty( $this->mInfo['data'] ) && empty( $pParamHash['edit'] ) ) {
			$pParamHash['content_store']['data'] = '';
		} elseif( empty( $pParamHash['edit'] ) ) {
			unset( $pParamHash['edit'] );
		} else {
			$pParamHash['content_store']['data'] = substr( $pParamHash['edit'], 0, 250 );
		}

		// Individual gallery preference store - dealt with by LibertyContent::store();
		if( empty( $pParamHash['preferences']['allow_comments'] )) {
			$pParamHash['preferences']['allow_comments'] = NULL;
		}
		$pParamHash['preferences_store'] = !empty( $pParamHash['preferences'] ) ? $pParamHash['preferences'] : NULL;

		// structure store
		if( @BitBase::verifyId( $pParamHash['root_structure_id'] ) ) {
			$pParamHash['structure_store']['root_structure_id'] = $pParamHash['root_structure_id'];
		} else {
			$pParamHash['structure_store']['root_structure_id'] = NULL;
		}

		if( @BitBase::verifyId( $pParamHash['parent_id'] ) ) {
			$pParamHash['structure_store']['parent_id'] = $pParamHash['parent_id'];
		} else {
			$pParamHash['structure_store']['parent_id'] = NULL;
		}

		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * expunge a gallery
	 *
	 * @param array $pParamHash
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function expunge( $pForceDeleteItems = FALSE ) {
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();

			// get all items that are part of the sub tree
			require_once( LIBERTY_PKG_PATH.'LibertyStructure.php' );
			$struct = new LibertyStructure();
			$tree = $struct->getSubTree( $this->mStructureId );

			// include the current id as well - needed when there are no sub-galleries
			$galleryContentIds[] = $this->mContentId;
			foreach( $tree as $node ) {
				$galleryContentIds[] = $node['content_id'];
			}
			$galleryContentIds = array_unique( $galleryContentIds );

			// Create Item Object
			require_once( TREASURY_PKG_PATH.'TreasuryItem.php');
			$itemObject = new TreasuryItem();

			// Go through all galleries we want to remove
			foreach( $galleryContentIds as $gid ) {
				// make sure the gallery is fully loaded
				$this->mContentId = $gid;
				$this->load();

				$itemContentIds = $this->mDb->getCol( "SELECT `item_content_id` FROM `".BIT_DB_PREFIX."treasury_map` WHERE `gallery_content_id`=?", array( $gid ) );
				$itemContentIds = array_unique( $itemContentIds );

				// Delete items in galleries
				foreach( $itemContentIds as $iid ) {
					if( $pForceDeleteItems ) {
						// Remove item even if it exists in other galleries
						$count = 1;
					} else {
						// Only delete item if it doesn't exist in other galleries
						$count = $this->mDb->getOne( "SELECT COUNT( `item_content_id` ) FROM `".BIT_DB_PREFIX."treasury_map` WHERE `item_content_id`=?", array( $iid ) );
					}

					// Only delete item if it doesn't exist in other galleries
					if( $count == 1 ) {
						$itemObject->mContentId = $iid;
						$itemObject->load();
						if( !$itemObject->expunge() ) {
							$this->mErrors['expunge'][] = $itemObject->mErrors;
						}
					}
				}

				// Next, we remove any icons if they exist
				if( $thumbdir = $this->getGalleryThumbBaseUrl() ) {
					@unlink_r( BIT_ROOT_PATH.$thumbdir );
				}

				// Now that all the items are gone, we can start nuking gallery entries
				// remove gallery preferences
				$query = "DELETE FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id`=?";
				$result = $this->mDb->query( $query, array( $this->mContentId ) );

				// Remove map entries
				$sql = "DELETE FROM `".BIT_DB_PREFIX."treasury_map` WHERE `gallery_content_id`=?";
				$rs = $this->mDb->query( $sql, array( $gid ) );

				// Remove gallery entry
				$sql = "DELETE FROM `".BIT_DB_PREFIX."treasury_gallery` WHERE `content_id`=?";
				$rs = $this->mDb->query( $sql, array( $gid ) );

				// Let liberty remove all the content entries for this gallery
				if( !LibertyContent::expunge() ) {
					$errors = TRUE;
				}

				// Finally nuke the structure in liberty_structures
				$struct->removeStructureNode( $this->mStructureId, FALSE );
			}

			if( empty( $errors ) ) {
				$this->mDb->CompleteTrans();
				$ret = TRUE;
			} else {
				$this->mDb->RollbackTrans();
				$ret = FALSE;
			}
		}
		return $ret;
	}

	/**
	 * Returns HTML link to display a gallery or item
	 *
	 * @param $pTitle is the gallery we want to see
	 * @param $pContentId content id of the gallery in question
	 * @return the link to display the page.
	 **/
	function getDisplayLink( $pLinkText=NULL, $pMixed=NULL, $pAnchor=NULL ) {
		global $gBitSystem;
		if( empty( $pLinkText ) && !empty( $this ) ) {
			$pLinkText = $this->getTitle();
		}

		if( empty( $pMixed ) && !empty( $this ) ) {
			$pMixed = $this->mInfo;
		}

		$ret = $pLinkText;
		if( !empty( $pLinkText ) && !empty( $pMixed ) ) {
			if( $gBitSystem->isPackageActive( 'treasury' ) ) {
				$ret = '<a title="'.htmlspecialchars( $pLinkText ).'" href="'.TreasuryGallery::getDisplayUrlFromHash( $pMixed ).'">'.htmlspecialchars( $pLinkText ).'</a>';
			}
		}
		return $ret;
	}

	/**
	 * Generates the URL to this gallery from current object
	 * @return the link to display the page.
	 **/
	public function getDisplayUrl() {
		global $gBitSystem;
		$ret = NULL;

		if( $this->isValid() ) {
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) || $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ) {
				$ret = TREASURY_PKG_URL.'gallery/'.$this->mContentId;
			} else {
				$ret = TREASURY_PKG_URL.'view.php?content_id='.$this->mContentId;
			}
		}
		return $ret;
	}

	/**
	 * Generates the URL to this gallery
	 * @param $pContentId is the gallery we want to see
	 * @return the link to display the page.
	 **/
	public static function getDisplayUrlFromHash( &$pParamHash ) {
		global $gBitSystem;
		$ret = NULL;

		if( @BitBase::verifyId( $pParamHash['content_id'] ) ) {
			if( $gBitSystem->isFeatureActive( 'pretty_urls' ) || $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ) {
				$ret = TREASURY_PKG_URL.'gallery/'.$pParamHash['content_id'];
			} else {
				$ret = TREASURY_PKG_URL.'view.php?content_id='.$pParamHash['content_id'];
			}
		}
		return $ret;
	}

	/**
	 * Get the base path to where the gallery thumbnail is stored - create directory if needed
	 *
	 * @param numeric $pContentId Content ID of gallery in question
	 * @access public
	 * @return Path to thumbnail directory on success, FALSE on failure
	 */
	function getGalleryThumbBaseUrl( $pContentId = NULL ) {
		$ret = FALSE;
		if( !@BitBase::verifyId( $pContentId ) && $this->isValid() ) {
			$pContentId = $this->mContentId;
		}

		if( @BitBase::verifyId( $pContentId ) ) {
			// getStorageBranch is a private function, so this is a no-no but necessary until LA offers something better
			$ret = LibertyMime::getStorageBranch( 'gallery_thumbnails/'.$pContentId, NULL, TREASURY_PKG_NAME );
		}
		return $ret;
	}
	
	/**
	* Returns the create/edit url to a gallery
	* @param number $pContentId a valid content id
	* @param array $pMixed a hash of params to add to the url  
	*/
	function getEditUrl( $pContentId = NULL, $pMixed = NULL ){
		if( @BitBase::verifyId( $pContentId ) ) {
			$ret = TREASURY_PKG_URL.'edit_gallery.php?content_id='.$pContentId;
		} elseif( $this->isValid() ) {
			$ret = TREASURY_PKG_URL.'edit_gallery.php?content_id='.$this->mContentId;
		} else {
			$ret = TREASURY_PKG_URL.'edit_gallery.php'.(!empty( $pMixed )?"?":"");
		}
		foreach( $pMixed as $key => $value ){
			if( $key != "content_id" || ( $key == "content_id" && @BitBase::verifyId( $value ) ) ) {
				$ret .= (isset($amp)?"&":"").$key."=".$value;
			}
			$amp = TRUE;
		}
		return $ret;
	}
}
?>
