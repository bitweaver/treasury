<?php
require_once( '../../bit_setup_inc.php' );
$gBitSystem->verifyPermission( 'p_admin' );

echo "<pre>";
echo "     Treasury Primary Attachment IDs\n";
echo "     -------------------------\n";
$query = "
	SELECT tri.`content_id`, la.`attachment_id` AS `primary_attachment_id`, lf.`storage_path`
	FROM `".BIT_DB_PREFIX."liberty_attachments` la
		INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON( lf.`file_id` = la.`foreign_id` )
		INNER JOIN `".BIT_DB_PREFIX."liberty_attachments_map` lam ON( la.`attachment_id` = lam.`attachment_id` )
		INNER JOIN `".BIT_DB_PREFIX."treasury_item` tri ON( tri.`content_id` = lam.`content_id` )
	WHERE la.`attachment_plugin_guid` = ? ORDER BY la.`attachment_id`";
if( $ret = $gBitSystem->mDb->getAll( $query, array( 'treasury' ))) {
	foreach( $ret as $update ) {
		if( LibertyAttachable::storePrimaryAttachmentId( $update )) {
			echo ">>>> Updated Treasury file: [{$update['primary_attachment_id']}] {$update['storage_path']}\n";
			$gBitSystem->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."liberty_attachments_map` WHERE `content_id`=? AND `attachment_id` = ?", array( $update['content_id'], $update['primary_attachment_id'] ));
		}
	}
}

echo "     -------------------------\n\n\n";
echo "     Treasury File Storage GUID update\n";
echo "     -------------------------\n";
echo ">>>> Success\n";
$query = "UPDATE `".BIT_DB_PREFIX."liberty_attachments` SET `attachment_plugin_guid` = ? WHERE `attachment_plugin_guid` = ?";
$gBitSystem->mDb->query( $query, array( 'bitfile', 'treasury' ));

echo "     -------------------------\n\n\n";
echo "     Content that uses {attachment id=123} where the attachment_id is a treasury attachment_id\n";
echo "     -------------------------\n";
echo "</pre>";

$query = "SELECT lc.`primary_attachment_id` FROM `".BIT_DB_PREFIX."liberty_content` lc WHERE lc.`content_type_guid` = ?";
$attIds = $gBitSystem->mDb->getCol( $query, array( 'treasuryitem' ));
$query = "SELECT lc.`data`, lc.`title`, lc.`content_id` FROM `".BIT_DB_PREFIX."liberty_content` lc WHERE lc.`data` <> ''";
$content = $gBitSystem->mDb->getAll( $query );
echo '<ul>';
echo '<li><a href="?update_content=1">Try to replace all occurances of {attachment id=123} with {file id=123} where appropriate.</a></li>';
foreach( $attIds as $attId ) {
	foreach( $content as $c ) {
		if( preg_match( "!\{attachment[^\}]*id\s*=\s*{$attId}[^\d]*?\}!i", $c['data'] )) {
			if( !empty( $_GET['update_content'] )) {
				$data = preg_replace( "!\{attachment([^\}]+)id\s*=\s*{$attId}([^\d]*?)\}!i", "{file$1id={$attId}$2}", $c['data'] );
				$query = "UPDATE `".BIT_DB_PREFIX."liberty_content` SET `data` = ? WHERE `content_id` = ?";
				$gBitSystem->mDb->query( $query, array( $data, $c['content_id'] ));
				echo "<li>Updated: <a href=\"/index.php?content_id={$c['content_id']}\">{$c['title']}</a> <small>uses: {file id=$attId}</small></li>";
			} else {
				echo "<li><a href=\"/index.php?content_id={$c['content_id']}\">{$c['title']}</a> <small>uses: {attachment id=$attId}</small></li>";
			}
		}
	}
}
echo '</ul>';
?>
