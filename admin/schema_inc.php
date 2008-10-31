<?php
$tables = array(
	'treasury_gallery' => "
		content_id I4 NOTNULL,
		structure_id I4 NOTNULL,
		is_private C(1) DEFAULT 'n'
		CONSTRAINT '
			, CONSTRAINT `treasury_gallery_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )
			, CONSTRAINT `treasury_gallery_structure_ref` FOREIGN KEY (`structure_id`) REFERENCES `".BIT_DB_PREFIX."liberty_structures`( `structure_id` )'
	",

	'treasury_item' => "
		content_id I4 NOTNULL,
		plugin_guid C(16) NOTNULL
		CONSTRAINT '
			, CONSTRAINT `treasury_item_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content`( `content_id` )
	",

	'treasury_map' => "
		gallery_content_id I4 NOTNULL,
		item_content_id I4 NOTNULL,
		item_position I4
	",
);

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( TREASURY_PKG_NAME, $tableName, $tables[$tableName] );
}

$gBitInstaller->registerPackageInfo( TREASURY_PKG_NAME, array(
	'description' => "A flexible file manager.",
	'license' => '<a href="http://www.gnu.org/licenses/licenses.html#LGPL">LGPL</a>',
));

// Sequences
$sequences = array (
	'treasury_id_seq' => array( 'start' => 1 )
);
$gBitInstaller->registerSchemaSequences( TREASURY_PKG_NAME, $sequences );

// Indeces
$indices = array (
	'treasury_gallery_content_idx' => array( 'table' => 'treasury_gallery', 'cols' => 'content_id', 'opts' => array( 'UNIQUE' ) ),
	'treasury_gallery_structure_idx' => array( 'table' => 'treasury_gallery', 'cols' => 'structure_id', 'opts' => array( 'UNIQUE' ) ),
	'treasury_item_content_idx' => array( 'table' => 'treasury_item', 'cols' => 'content_id', 'opts' => array( 'UNIQUE' ) ),
);
$gBitInstaller->registerSchemaIndexes( TREASURY_PKG_NAME, $indices );

// Default Preferences
$gBitInstaller->registerPreferences( TREASURY_PKG_NAME, array(
	// default gallery listing
	array( TREASURY_PKG_NAME, 'treasury_gallery_list_thumb',     'avatar' ),
	array( TREASURY_PKG_NAME, 'treasury_gallery_view_thumb',     'small' ),
	array( TREASURY_PKG_NAME, 'treasury_gallery_list_title',     'y' ),
	array( TREASURY_PKG_NAME, 'treasury_gallery_list_desc',      'y' ),
	array( TREASURY_PKG_NAME, 'treasury_gallery_list_created',   'y' ),
	array( TREASURY_PKG_NAME, 'treasury_gallery_list_creator',   'y' ),
	array( TREASURY_PKG_NAME, 'treasury_gallery_list_hits',      'y' ),
	array( TREASURY_PKG_NAME, 'treasury_gallery_list_structure', 'y' ),
	// default item listing
	array( TREASURY_PKG_NAME, 'treasury_item_list_thumb',        'icon' ),
	array( TREASURY_PKG_NAME, 'treasury_item_list_size',         'y' ),
	array( TREASURY_PKG_NAME, 'treasury_item_list_date',         'y' ),
	array( TREASURY_PKG_NAME, 'treasury_item_list_hits',         'y' ),
	// default item view
	array( TREASURY_PKG_NAME, 'treasury_item_view_thumb',        'small' ),
	array( TREASURY_PKG_NAME, 'treasury_item_view_name',         'y' ),
	array( TREASURY_PKG_NAME, 'treasury_item_view_desc',         'y' ),
	array( TREASURY_PKG_NAME, 'treasury_item_view_size',         'y' ),
	array( TREASURY_PKG_NAME, 'treasury_item_view_date',         'y' ),
	array( TREASURY_PKG_NAME, 'treasury_item_view_creator',      'y' ),
	array( TREASURY_PKG_NAME, 'treasury_item_view_hits',         'y' ),
	array( TREASURY_PKG_NAME, 'treasury_menu_text',              'File Galleries' ),
	array( TREASURY_PKG_NAME, 'treasury_extended_upload_slots',  'y' ),
));

// Default UserPermissions
$gBitInstaller->registerUserPermissions( TREASURY_PKG_NAME, array(
	// gallery permissions
	array( 'p_treasury_view_gallery',   'Can view file galleries',                       'basic',      TREASURY_PKG_NAME ),
	array( 'p_treasury_create_gallery', 'Can create and remove file galleries',          'editors',    TREASURY_PKG_NAME ),
	array( 'p_treasury_update_gallery', 'Can edit existing file galleries',              'editors',    TREASURY_PKG_NAME ),
	// item permissions
	array( 'p_treasury_view_item',      'Can view a downloadable file',                  'basic',      TREASURY_PKG_NAME ),
	array( 'p_treasury_download_item',  'Can download files',                            'basic',      TREASURY_PKG_NAME ),
	array( 'p_treasury_upload_item',    'Can upload files into existing file galleries', 'registered', TREASURY_PKG_NAME ),
	array( 'p_treasury_import_item',    'Can import a file from an upload directory',    'editors',    TREASURY_PKG_NAME ),
	array( 'p_treasury_update_item',    'Can edit already uploaded files',               'editors',    TREASURY_PKG_NAME ),
	// admin permission
	array( 'p_treasury_admin',          'Can admin file galleries and the uploaded files', 'admin',    TREASURY_PKG_NAME ),
));

if( defined( 'RSS_PKG_NAME' )) {
	$gBitInstaller->registerPreferences( TREASURY_PKG_NAME, array(
		array( RSS_PKG_NAME, TREASURY_PKG_NAME.'_rss', 'y'),
	));
}

$gBitInstaller->registerContentObjects( WIKI_PKG_NAME, array( 
	'TreasuryItem'    => TREASURY_PKG_PATH.'TreasuryItem.php',
	'TreasuryGallery' => TREASURY_PKG_PATH.'TreasuryGallery.php',
));

// Package Requirements
$gBitInstaller->registerRequirements( TREASURY_PKG_NAME, array(
	'liberty' => array( 'min' => '2.1.0' ),
));
?>
