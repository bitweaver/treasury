<?php
global $gQueryUser, $module_rows, $module_params;

require_once( TREASURY_PKG_PATH.'TreasuryItem.php' );
$gTi = new TreasuryItem();

$listHash = $_REQUEST;

if( !empty( $module_rows )) {
	$listHash['max_records'] = $module_rows;
}
if( empty( $listHash['sort_mode'] )) {
	$listHash['sort_mode'] = 'random';
}

/* Get a list of user items */
if( !empty( $gQueryUser ) && $gQueryUser->mUserId ) {
	$listHash['user_id'] = $gQueryUser->mUserId;
}

$centerItemList = $gTi->getList( $listHash );
$gBitSmarty->assign( 'centerItemList', $centerItemList );
$gBitSmarty->assign( 'treasury_center_params', $module_params );
?>
