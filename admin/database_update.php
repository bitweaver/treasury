<?php
require_once( '../../bit_setup_inc.php' );
$gBitSystem->verifyPermission( 'p_admin' );

echo "<pre>";
echo "     Treasury database cleanup\n";
echo "     -------------------------\n\n\n";
$query = "
	SELECT tri.`content_id`, la.`attachment_id` AS `primary_attachment_id`, lf.`storage_path`
	FROM `".BIT_DB_PREFIX."liberty_attachments` la
		INNER JOIN `".BIT_DB_PREFIX."liberty_files` lf ON( lf.`file_id` = la.`foreign_id` )
		INNER JOIN `".BIT_DB_PREFIX."liberty_attachments_map` lam ON( la.`attachment_id` = lam.`attachment_id` )
		INNER JOIN `".BIT_DB_PREFIX."treasury_item` tri ON( tri.`content_id` = lam.`content_id` )
	WHERE la.`attachment_plugin_guid` = ? ORDER BY la.`attachment_id`";
if( $ret = $gBitSystem->mDb->getAll( $query, array( PLUGIN_GUID_TREASURY_FILE ))) {
	foreach( $ret as $update ) {
		if( LibertyAttachable::storePrimaryAttachmentId( $update )) {
			echo ">>>> Updated Treasury file: [{$update['primary_attachment_id']}] {$update['storage_path']}\n";
			$gBitSystem->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."liberty_attachments_map` WHERE `content_id`=? AND `attachment_id` = ?", array( $update['content_id'], $update['primary_attachment_id'] ));
		}
	}
}
echo "</pre>";
?>
