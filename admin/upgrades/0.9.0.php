<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_treasury/admin/upgrades/0.9.0.php,v 1.2 2008/10/30 22:02:20 squareing Exp $
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
