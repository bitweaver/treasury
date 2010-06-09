<?php
/**
 * @version $Header$
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => TREASURY_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Since you've been using treasury, please visit the <a href='".TREASURY_PKG_URL."admin/database_to_libertymime.php'>treasury upgrade script</a> after you've completed the upgrade.",
	'post_upgrade' => "Since you've been using treasury, please visit the <a href='".TREASURY_PKG_URL."admin/database_to_libertymime.php'>treasury upgrade script</a> after you've completed the upgrade.",
);
$gBitInstaller->registerPackageUpgrade( $infoHash );
?>
