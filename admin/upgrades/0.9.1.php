<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_treasury/admin/upgrades/0.9.1.php,v 1.1 2008/12/22 13:43:15 squareing Exp $
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => TREASURY_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Update liberty attachments data with data stored in treasury item table and then remove obsolete treasury item table.",
);
$gBitInstaller->registerPackageUpgrade( $infoHash, array(

array( 'PHP' => '
	global $gBitSystem;
	$sql = "
		SELECT tri.`plugin_guid`, la.`attachment_id`
		FROM `".BIT_DB_PREFIX."treasury_item` tri
		INNER JOIN `".BIT_DB_PREFIX."liberty_content`     lc ON ( tri.`content_id` = lc.`content_id` )
		INNER JOIN `".BIT_DB_PREFIX."liberty_attachments` la ON ( tri.`content_id` = la.`content_id` )
		WHERE la.attachment_plugin_guid NOT LIKE ?";
	if( $result = $gBitSystem->mDb->query( $sql, array( "mime%" ))) {
		while( $aux = $result->fetchRow() ) {
			$gBitSystem->mDb->associateUpdate(
				BIT_DB_PREFIX."liberty_attachments",
				array( "attachment_plugin_guid" => str_replace( "_", "", $aux["plugin_guid"] )),
				array( "attachment_id" => $aux["attachment_id"] )
			);
		}
	}
'),

array( 'DATADICT' => array(
	array( 'DROPTABLE' => array(
		'treasury_item',
	)),
)),

));
?>
