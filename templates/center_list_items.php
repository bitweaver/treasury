<?php
global $gQueryUser, $moduleParams;

require_once( TREASURY_PKG_PATH.'TreasuryItem.php' );
$ti = new TreasuryItem();

$listHash = $_REQUEST;

if( !empty( $moduleParams['module_rows'] )) {
	$listHash['max_records'] = $moduleParams['module_rows'];
}
if( empty( $listHash['sort_mode'] )) {
	$listHash['sort_mode'] = 'random';
}

/* Get a list of user items */
if( !empty( $gQueryUser ) && $gQueryUser->mUserId ) {
	$listHash['user_id'] = $gQueryUser->mUserId;
}

$centerItemList = $ti->getList( $listHash );
$gBitSmarty->assign( 'centerItemList', $centerItemList );
$gBitSmarty->assign( 'treasury_center_params', $moduleParams['module_params'] );
?>
