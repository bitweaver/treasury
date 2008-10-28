<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_treasury/admin/upgrades/0.9.0.php,v 1.1 2008/10/28 21:15:34 squareing Exp $
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => TREASURY_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Since you've been using treasury, please visit the <a href='".TREASURY_PKG_URL."admin/database_to_libertymime.php'>treasury upgrade script</a> after you've completed the upgrade.",
	'post_upgrade' => "Since you've been using treasury, please visit the <a href='".TREASURY_PKG_URL."admin/database_to_libertymime.php'>treasury upgrade script</a> after you've completed the upgrade.",
);

$gBitInstaller->registerPackageUpgrade( $infoHash );

$gBitInstaller->registerPackageDependencies( $infoHash, array(
	'liberty' => array( 'min' => '2.1.0' ),
));
?>
