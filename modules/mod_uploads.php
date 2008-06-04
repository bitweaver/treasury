<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_treasury/modules/mod_uploads.php,v 1.4 2008/06/04 12:48:42 squareing Exp $
 * @package fisheye
 * @subpackage modules
 */

global $gQueryUserId, $gContent;

/**
 * required setup
 */
require_once( TREASURY_PKG_PATH.'TreasuryItem.php' );
// this has the stuff we need from the form
extract( $moduleParams );

$item = new TreasuryItem();
$display = TRUE;
$listHash = &$module_params;
$listHash['gallery_content_id'] = !empty( $module_params['content_id'] ) ? $module_params['content_id'] : NULL;
$listHash['max_records'] = $module_rows;

if( $gQueryUserId ) {
	$listHash['user_id'] = $gQueryUserId;
} elseif( !empty( $_REQUEST['user_id'] ) ) {
	$listHash['user_id'] = $_REQUEST['user_id'];
} elseif( !empty( $module_params['recent_users'] ) ) {
	$listHash['recent_users'] = TRUE;
}

// this is needed to avoid wrong sort_modes entered resulting in db errors
$sort_options = array( 'hits', 'created' );
if( !empty( $module_params['sort_mode'] ) && in_array( $module_params['sort_mode'], $sort_options ) ) {
	$sort_mode = $module_params['sort_mode'].'_desc';
} else {
	$sort_mode = 'random';
}
$listHash['sort_mode'] = $sort_mode;

$items = $item->getList( $listHash );

// vd( $moduleParams );
if( empty( $moduleParams['title'] ) && $items ) {
	$moduleTitle = '';
	if( !empty( $module_params['sort_mode'] ) ) {
		if( $module_params['sort_mode'] == 'random' ) {
			$moduleTitle = 'Random';
		} elseif( $module_params['sort_mode'] == 'created' ) {
			$moduleTitle = 'Recent';
		} elseif( $module_params['sort_mode'] == 'hits' ) {
			$moduleTitle = 'Popular';
		}
	} else {
		$moduleTitle = 'Random';
	}

	$moduleTitle .= ' Files';
	$moduleTitle = tra( $moduleTitle );

	if( !empty( $listHash['user_id'] ) ) {
		$moduleTitle .= ' '.tra( 'by' ).' '.BitUser::getDisplayName( TRUE, current( $files ) );
	} elseif( !empty( $listHash['recent_users'] ) ) {
		$moduleTitle .= ' '.tra( 'by' ).' <a href="'.USERS_PKG_URL.'">'.tra( 'New Users' ).'</a>';
	}

	$listHash['sort_mode'] = $sort_mode;
	$gBitSmarty->assign( 'moduleTitle', $moduleTitle );
}

$gBitSmarty->assign( 'modItems', $items );
$gBitSmarty->assign( 'module_params', $module_params );
$gBitSmarty->assign( 'maxlen', isset( $module_params["maxlen"] ) ? $module_params["maxlen"] : 0 );
$gBitSmarty->assign( 'maxlendesc', isset( $module_params["maxlendesc"] ) ? $module_params["maxlendesc"] : 0 );
?>
