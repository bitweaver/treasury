<?php
require_once( '../../bit_setup_inc.php' );

$gBitSystem->verifyPermission( 'p_admin' );
ini_set( "max_execution_time", "1800" );

echo "
<pre>
   Update Treasury Database to work with LibertyMime
   =================================================
";

$reset = "";
if( !empty( $_GET )) {
	$reset = "<a href=\"".TREASURY_PKG_URL."admin/database_to_libertymime.php\">Reset page</a>";
}

echo '
1. Database update: '.$reset.'
   ----------------
   Our file handling system has undergone a majour overhaul and treasury needs 
   to be updated to work with the new setup. This update is only necessary if you 
   have been using bitweaver &lt; 2.1.0 and have been uploading files to treasury. 
   Even if you run this update more than once, there should be no damage to the 
   system.
   This is only necessary when you have just upgraded bitweaver from before 2.1.0.
   <a href="'.TREASURY_PKG_URL.'admin/database_to_libertymime.php?fix_db=1">Disable Treasury plugins</a>.
   After running the above, please visit the <a href="'.LIBERTY_PKG_URL.'admin/plugins.php">liberty plugin page</a> and enable the
   appropriate mime plugins.
';

if( !empty( $_GET['fix_db'] )) {
	// also make sure that all treasury plugins are off
	$gBitSystem->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."kernel_config` WHERE `config_name` LIKE ?", array( 'treasury_plugin_%' ));
	echo "\n\n   Unused treasury plugins have been disabled.\n";

	// also make sure the default mime plugin has been set active
	$gLibertySystem->scanAllPlugins( NULL, "mime\." );
	$gLibertySystem->setActivePlugin( 'mimedefault' );
	echo "   Required liberty file plugin has been enabled.\n";
}

echo "<br /><br /><br />";
echo '
2. Content update: '.$reset.'
   ---------------
   If you have been inserting content into wiki pages using {file} as suggested 
   by treasury, you can continue using this or you can move to using the global 
   {attachment} plugin. You can revisit this page at any time if you want to make 
   all your content use {attachment} instead of {file} or {flashvideo} once you 
   are sure that {attachment} does what you want it to do.
   <a href="'.TREASURY_PKG_URL.'admin/database_to_libertymime.php?update_content=1">Update {file} or {flashvideo} with {attachment} where appropriate.</a>
   You might have to run this more than once. Keep on running this script until 
   no more updates appear. Depending on your system and the number of uploads 
   and content you have, this can take some time.
';

if( !empty( $_GET['update_content'] )) {
$sql = "
	SELECT la.`attachment_id`
	FROM `".BIT_DB_PREFIX."liberty_content` lc
	INNER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON ( lc.`content_id` = la.`content_id` )
	WHERE lc.content_type_guid = ? ORDER BY la.`attachment_id` ASC";

	$atts = $gBitSystem->mDb->getAll( $sql, array( 'treasuryitem' ));
	$query = "SELECT lc.`data`, lc.`title`, lc.`content_id` FROM `".BIT_DB_PREFIX."liberty_content` lc WHERE lc.`data` LIKE ? OR lc.`data` LIKE ?";
	$content = $gBitSystem->mDb->getAll( $query, array( "%{flashvideo%", "%{file%" ));

	$gBitSystem->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."kernel_config` WHERE `config_name` LIKE ?", array( 'liberty_plugin_%_dataflashvideo' ));
	$gBitSystem->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."kernel_config` WHERE `config_name` LIKE ?", array( 'liberty_plugin_%_datafile' ));
	echo "\n\n   The {flashvideo} and {file} plugins have been disabled.\n";

	echo '<ul>';
	foreach( $atts as $att ) {
		$attId = $att['attachment_id'];
		foreach( $content as $c ) {
			$pattern = "#\{(file|flashvideo)([^\}]+)\bid *= *{$attId}\b([^\}]*)\}#i";
			if( preg_match( $pattern, $c['data'] )) {
				$data = preg_replace( $pattern, "{attachment$2id={$attId}$3}", $c['data'] );
				$query = "UPDATE `".BIT_DB_PREFIX."liberty_content` SET `data` = ? WHERE `content_id` = ?";
				$gBitSystem->mDb->query( $query, array( $data, $c['content_id'] ));
				echo "<li>Updated: <a href=\"/index.php?content_id={$c['content_id']}\">{$c['title']}</a> [ now it uses: {attachment id=$attId} ]</li>";
			}
		}
	}
	echo '</ul>';
	echo "   All content has been updated";
}
echo "</pre>";

// some stuff that has to happen regardless of what the user wants
if( empty( $_GET )) {
	if( !$gBitSystem->isFeatureActive( "treasury_item_list_thumb_custom" )) {
		if( $galleryContentIds = $gBitSystem->mDb->getCol( "SELECT `content_id` FROM `".BIT_DB_PREFIX."treasury_gallery`" ) ) {
			foreach( $galleryContentIds as $gid ) {
				$query    = "DELETE FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id` = ? AND `pref_name` = ? ";
				$bindvars = array( $gid, 'item_list_thumb_size' );
				$result   = $gBitSystem->mDb->query( $query, $bindvars );
			}
		}
	}
}
?>
