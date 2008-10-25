<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_treasury/admin/upgrades/Attic/2.0.0.php,v 1.2 2008/10/25 09:37:34 squareing Exp $
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
