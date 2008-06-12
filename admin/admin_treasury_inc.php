<?php
// $Header: /cvsroot/bitweaver/_bit_treasury/admin/admin_treasury_inc.php,v 1.17 2008/06/12 07:14:40 squareing Exp $

$treasurySettings = array(
	'treasury_menu_text' => array(
		'label' => 'Menu Text',
		'note' => 'The text you want to appear in the top menu.',
		'type' => 'text',
	),
	'treasury_force_download' => array(
		'label' => 'Force Download',
		'note' => "If you set this, the mime type during download will be set to application/force-download forcing the browser to download the content regardless of it's original file type.",
		'type' => 'checkbox',
	),
	'treasury_use_cron' => array(
		'label' => 'Use Cron',
		'note' => "Use cron to monitor for lengthy or taxing jobs that need to be taken care of. If this is not selected, treasury will deal with all files as they are uploaded. This can take a long time and might upset the user or cause your server to choke. Before you activate this, make sure you have added the required cron job(s) to your cron file.",
		'type' => 'checkbox',
	),
);
if( !$gBitSystem->isPackageActive( 'gigaupload' ) ) {
	$treasurySettings["treasury_extended_upload_slots"] = array(
		'label' => 'Extended Upload Slots',
		'note' => 'When you enable this, users can enter the title and description of the file when uploading them.',
		'type' => 'checkbox'
	);
};
$gBitSmarty->assign( 'treasurySettings', $treasurySettings );

$galleryListing = array(
	'treasury_gallery_list_desc' => array(
		'label' => 'Display Description',
		'note' => 'Show the description of the gallery below the title.',
		'type' => 'checkbox',
	),
	'treasury_gallery_list_structure' => array(
		'label' => 'Display Sub Galleries',
		'note' => 'Show all subgalleries in the list.',
		'type' => 'checkbox',
	),
	'treasury_gallery_list_created' => array(
		'label' => 'Display Creation Time',
		'note' => 'Display the gallery creation time.',
		'type' => 'checkbox',
	),
	'treasury_gallery_list_creator' => array(
		'label' => 'Creator',
		'note' => 'Display the creator of the gallery.',
		'type' => 'checkbox',
	),
	'treasury_gallery_list_item_count' => array(
		'label' => 'Display Item Count',
		'note' => 'Display the number of files within this gallery.',
		'type' => 'checkbox',
	),
	'treasury_gallery_list_hits' => array(
		'label' => 'Display hits',
		'note' => 'Display the number of times the gallery has been loaded.',
		'type' => 'checkbox',
	),
);
$gBitSmarty->assign( 'galleryListing', $galleryListing );

$itemListing = array(
	'treasury_item_list_thumb_custom' => array(
		'label' => 'Allow thumbsize override',
		'note' => 'Allow gallery creator to specify their preferred icon size.',
		'type' => 'checkbox',
	),
	'treasury_item_list_name' => array(
		'label' => 'Filename',
		'note' => 'Display the actual filename of the file.',
		'type' => 'checkbox',
	),
	'treasury_item_list_desc' => array(
		'label' => 'Description',
		'note' => 'Display the file description.',
		'type' => 'checkbox',
	),
	'treasury_item_list_size' => array(
		'label' => 'File Size',
		'note' => 'Display the file size.',
		'type' => 'checkbox',
	),
	'treasury_item_list_date' => array(
		'label' => 'Upload Date',
		'note' => 'Display the date the file was uploaded.',
		'type' => 'checkbox',
	),
	'treasury_item_list_creator' => array(
		'label' => 'Uploader',
		'note' => 'Display the name of the person who uploaded the file.',
		'type' => 'checkbox',
	),
	'treasury_item_list_hits' => array(
		'label' => 'Downloads',
		'note' => 'Display the number of times the file has been downloaded.',
		'type' => 'checkbox',
	),
	'treasury_item_list_attid' => array(
		'label' => 'Attachment ID',
		'note' => 'Display the syntax used to include the file in a wiki page.',
		'type' => 'checkbox',
	),
);
$gBitSmarty->assign( 'itemListing', $itemListing );

$itemViewing = array(
	'treasury_item_view_name' => array(
		'label' => 'Filename',
		'note' => 'Display the actual filename of the file.',
		'type' => 'checkbox',
	),
	'treasury_item_view_desc' => array(
		'label' => 'Description',
		'note' => 'Display the file description.',
		'type' => 'checkbox',
	),
	'treasury_item_view_size' => array(
		'label' => 'File Size',
		'note' => 'Display the file size.',
		'type' => 'checkbox',
	),
	'treasury_item_view_date' => array(
		'label' => 'Upload Date',
		'note' => 'Display the date the file was uploaded.',
		'type' => 'checkbox',
	),
	'treasury_item_view_creator' => array(
		'label' => 'Uploader',
		'note' => 'Display the name of the person who uploaded the file.',
		'type' => 'checkbox',
	),
	'treasury_item_view_hits' => array(
		'label' => 'Downloads',
		'note' => 'Display the number of times the file has been downloaded.',
		'type' => 'checkbox',
	),
	'treasury_item_view_attid' => array(
		'label' => 'Attachment ID',
		'note' => 'Display the syntax used to include the file in a wiki page.',
		'type' => 'checkbox',
	),
);
$gBitSmarty->assign( 'itemViewing', $itemViewing );
$gBitSmarty->assign( 'imageSizes', get_image_size_options() );

if( !empty( $_REQUEST['treasury_settings'] ) ) {
	$treasuries = array_merge( $treasurySettings, $galleryListing, $itemListing, $itemViewing );
	foreach( $treasuries as $item => $data ) {
		if( $data['type'] == 'checkbox' ) {
			simple_set_toggle( $item, TREASURY_PKG_NAME );
		} elseif( $data['type'] == 'numeric' ) {
			simple_set_int( $item, TREASURY_PKG_NAME );
		} else {
			$gBitSystem->storeConfig( $item, ( !empty( $_REQUEST[$item] ) ? $_REQUEST[$item] : NULL ), TREASURY_PKG_NAME );
		}
	}
	$gBitSystem->storeConfig( 'treasury_gallery_list_thumb', ( !empty( $_REQUEST['treasury_gallery_list_thumb'] ) ? $_REQUEST['treasury_gallery_list_thumb'] : NULL ), TREASURY_PKG_NAME );
	$gBitSystem->storeConfig( 'treasury_gallery_view_thumb', ( !empty( $_REQUEST['treasury_gallery_view_thumb'] ) ? $_REQUEST['treasury_gallery_view_thumb'] : NULL ), TREASURY_PKG_NAME );
	$gBitSystem->storeConfig( 'treasury_item_list_thumb', ( !empty( $_REQUEST['treasury_item_list_thumb'] ) ? $_REQUEST['treasury_item_list_thumb'] : NULL ), TREASURY_PKG_NAME );
	$gBitSystem->storeConfig( 'treasury_item_view_thumb', ( !empty( $_REQUEST['treasury_item_view_thumb'] ) ? $_REQUEST['treasury_item_view_thumb'] : NULL ), TREASURY_PKG_NAME );
}
?>
