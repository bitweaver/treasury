<?php
require_once( '../../bit_setup_inc.php' );

$gBitSystem->verifyPermission( 'p_admin' );
ini_set( "max_execution_time", "1800" );

echo "
<pre>
   Update Treasury Database to work with LibertyMime
   =================================================
";

echo '
1. Database update:
   ----------------
   Our file handling system has undergone a majour overhaul and treasury needs 
   to be updated to work with the new setup. This update is only necessary if you 
   have been using bitweaver &lt; 2.1.0 and have been uploading files to treasury. 
   Even if you run this update more than once, there should be no damage to the 
   system.
   This is only necessary when you have just upgraded bitweaver from before 2.1.0.
   <a href="?fix_db=1">Update existing Treasury entries to work with the new and improved LibertyMime</a>.
   After running the above, please visit the <a href="'.LIBERTY_PKG_URL.'admin/plugins.php">liberty plugin page</a> and enable the
   appropriate mime plugins.
';

$sql = "
	SELECT lc.`title`, tri.`plugin_guid`, la.`attachment_id`
	FROM `".BIT_DB_PREFIX."treasury_item` tri
	INNER JOIN `".BIT_DB_PREFIX."liberty_content`     lc ON ( tri.`content_id` = lc.`content_id` )
	INNER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON ( tri.`content_id` = la.`content_id` )
	ORDER BY la.`attachment_id` ASC";

if( !empty( $_REQUEST['fix_db'] )) {
	// also make sure that all treasury plugins are off
	$gBitSystem->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."kernel_config` WHERE `config_name` LIKE ?", array( 'treasury_plugin_%' ));
	echo "\n\n   Unused treasury plugins have been disabled.\n";

	// also make sure the default mime plugin has been set active
	$gLibertySystem->scanAllPlugins( NULL, "mime\." );
	$gLibertySystem->setActivePlugin( 'mimedefault' );
	echo "   Required liberty file plugin has been enabled.\n";
die;
	$result = $gBitSystem->mDb->query( $sql );
	echo "<ul>";
	while( $aux = $result->fetchRow() ) {
		echo "<li>Updating: [ attachment_id: {$aux['attachment_id']}] - {$aux['title']}</li>";
		$gBitSystem->mDb->associateUpdate(
			BIT_DB_PREFIX."liberty_attachments",
			array( "attachment_plugin_guid" => str_replace( "_", "", $aux['plugin_guid'] )),
			array( "attachment_id" => $aux['attachment_id'] )
		);
	}
	echo "</ul>";
	echo "   All Treasury uploads have been updated.\n";
}

echo "<br /><br /><br />";
echo '
2. Content update:
   ---------------
   If you have been inserting content into wiki pages using {file} as suggested 
   by treasury, you can continue using this or you can move to using the global 
   {attachment} plugin. You can revisit this page at any time if you want to make 
   all your content use {attachment} instead of {file} or {flashvideo} once you 
   are sure that {attachment} does what you want it to do.
   <a href="?update_content=1">Update {file} or {flashvideo} with {attachment} where appropriate.</a>
   You might have to run this more than once. Keep on running this script until 
   no more updates appear. Depending on your system and the number of uploads 
   and content you have, this can take some time.
';

if( !empty( $_REQUEST['update_content'] )) {
	$atts = $gBitSystem->mDb->getAll( $sql );
	$query = "SELECT lc.`data`, lc.`title`, lc.`content_id` FROM `".BIT_DB_PREFIX."liberty_content` lc WHERE lc.`data` LIKE ? OR lc.`data` LIKE ?";
	$content = $gBitSystem->mDb->getAll( $query, array( "%{flashvideo%", "%{file%" ));

	$gBitSystem->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."kernel_config` WHERE `config_name` LIKE ?", array( 'liberty_plugin_%_dataflashvideo' ));
	$gBitSystem->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."kernel_config` WHERE `config_name` LIKE ?", array( 'liberty_plugin_%_datafile' ));
	echo "\n\n   The {flashvideo} and {file} plugins have been disabled.\n";

	echo '<ul>';
	foreach( $atts as $att ) {
		$attId = $att['attachment_id'];
		foreach( $content as $c ) {
			//vd($c['data']);
			//if( preg_match( "#\{flashvideo[^\}]*\bid *= *{$attId}\s[^\}]*\}#i", $c['data'] )) {
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
?>
