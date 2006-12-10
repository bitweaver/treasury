<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_treasury/treasury_rss.php,v 1.1 2006/12/10 15:15:09 squareing Exp $
 * @package treasury
 * @subpackage functions
 */

/**
 * Initialization
 */
require_once( "../bit_setup_inc.php" );
require_once( RSS_PKG_PATH."rss_inc.php" );
require_once( TREASURY_PKG_PATH."TreasuryItem.php" );

$gBitSystem->verifyPackage( 'treasury' );
$gBitSystem->verifyPackage( 'rss' );
$gBitSystem->verifyFeature( 'treasury_rss' );

$rss->title       = $gBitSystem->getConfig( 'treasury_rss_title', $gBitSystem->getConfig( 'site_title' ).' - '.tra( 'File Galleries' ) );
$rss->description = $gBitSystem->getConfig( 'treasury_rss_description', $gBitSystem->getConfig( 'site_title' ).' - '.tra( 'RSS Feed' ) );

// check permission to view treasury item
if( !$gBitUser->hasPermission( 'p_treasury_view_item' ) ) {
	require_once( RSS_PKG_PATH."rss_error.php" );
} else {
	$listHash = array(
		'max_records'        => $gBitSystem->getConfig( 'treasury_rss_max_records', 10 ),
		'sort_mode'          => 'last_modified_desc',
		'gallery_content_id' => !empty( $_REQUEST['gallery_content_id'] ) ? $_REQUEST['gallery_content_id'] : NULL,
		'user_id'            => !empty( $_REQUEST['user_id'] ) ? $_REQUEST['user_id'] : NULL,
	);

	// check if we want to use the cache file
	$cacheFile = TEMP_PKG_PATH.RSS_PKG_NAME.'/'.TREASURY_PKG_NAME.'_'."g{$listHash['gallery_content_id']}_u{$listHash['user_id']}_".$rss_version_name.'.xml';
	//$rss->useCached( $rss_version_name, $cacheFile ); // use cached version if age < 1 hour

	// if we have a gallery we can work with - load it
	if( @BitBase::verifyId( $_REQUEST['gallery_content_id'] ) ) {
		$gallery = new TreasuryGallery( NULL, $_REQUEST['gallery_content_id'] );
		$gallery->load();
		$rss->title .= " - {$gallery->getTitle()}";
	}

	$treasury = new TreasuryItem();
	$feeds = $treasury->getList( $listHash );

	// set the rss link
	$rss->link = 'http://'.$_SERVER['HTTP_HOST'].TREASURY_PKG_URL;

	require_once $gBitSmarty->_get_plugin_filepath( 'modifier', 'kbsize' );
	// get all the data ready for the feed creator
	foreach( $feeds as $feed ) {
		$item               = new FeedItem();
		$item->title        = $feed['title'];
		$item->link         = $feed['display_url'];
		$item->description  = '<a href="'.$feed['display_url'].'"><img src="'.$feed['thumbnail_url']['medium'].'" /></a>';
		$item->description .= "<ul>";
		if( !empty( $feed['data'] ) ) {
			$item->description .= "<li>".tra( 'Description' ).": {$feed['data']}</li>";
		}
		$item->description .= "<li>".tra( 'Filename' ).": {$feed['filename']} [".smarty_modifier_kbsize( $feed['file_size'] )."]</li>";
		$item->description .= "</ul>";

		$item->date         = ( int )$feed['last_modified'];
		$item->source       = 'http://'.$_SERVER['HTTP_HOST'].BIT_ROOT_URL;
		$item->author       = $gBitUser->getDisplayName( FALSE, $feed );

		$item->descriptionTruncSize = $gBitSystem->getConfig( 'rssfeed_truncate', 5000 );
		$item->descriptionHtmlSyndicated = FALSE;

		// pass the item on to the rss feed creator
		$rss->addItem( $item );
	}

	// finally we are ready to serve the data
	echo $rss->saveFeed( $rss_version_name, $cacheFile );
}
?>
