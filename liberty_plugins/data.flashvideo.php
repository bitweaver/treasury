<?php
/**
 * @version  $Revision$
 * @package  liberty
 * @subpackage plugins_data
 */
// +----------------------------------------------------------------------+
// | Copyright (c) 2004, bitweaver.org
// +----------------------------------------------------------------------+
// | All Rights Reserved. See below for details and a complete list of authors.
// | Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
// |
// | For comments, please use phpdocu.sourceforge.net documentation standards!!!
// | -> see http://phpdocu.sourceforge.net/
// +----------------------------------------------------------------------+
// | Authors: drewslater <andrew@andrewslater.com>
// +----------------------------------------------------------------------+
// $Id$

/**
 * definitions
 */
global $gBitSystem;

define( 'PLUGIN_GUID_DATAFLASHVIDEO', 'dataflashvideo' );
global $gLibertySystem;
$pluginParams = array (
	'tag'           => 'flashvideo',
	'auto_activate' => FALSE,
	'requires_pair' => FALSE,
	'load_function' => 'data_flashvideo',
	'title'         => 'Flash Video',
	'help_page'     => 'TreasuryFlvPlugin',
	'description'   => tra( "Display flashvideo in content. This requires treasury to be active with the flashvideo plugin available and working. Please view the online help for details." ),
	'help_function' => 'data_flashvideo_help',
	'syntax'        => '{flashvideo id= size= align= }',
	'plugin_type'   => DATA_PLUGIN,
	'biticon'       => '{biticon ilocation=quicktag iname=video-x-generic iexplain="Flashvideo"}',
	'taginsert'     => '{flashvideo id= align= description=}',
);
$gLibertySystem->registerPlugin( PLUGIN_GUID_DATAFLASHVIDEO, $pluginParams );
$gLibertySystem->registerDataTag( $pluginParams['tag'], PLUGIN_GUID_DATAFLASHVIDEO );

function data_flashvideo_help() {
	$help =
		'<table class="data help">'
			.'<tr>'
				.'<th>' . tra( "Key" ) . '</th>'
				.'<th>' . tra( "Type" ) . '</th>'
				.'<th>' . tra( "Comments" ) . '</th>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>id</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(required)") . '</td>'
				.'<td>' . tra( "Id number of Flashvideo to display inline.") . '</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>size</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "You can change the display size of the video here. This will not influence the download size of the video itself. Possible values are:") . ' <strong>small, medium, large</strong></td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>view</td>'
				.'<td>' . tra( "key-words") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "If you are including a small version of the video, you can easily link to the large version of this film. Possible values are:") . ' <strong>small, medium, large, original</strong></td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>width</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Manually set the width of the video in pixels.").'</td>'
			.'</tr>'
			.'<tr class="odd">'
				.'<td>height</td>'
				.'<td>' . tra( "numeric") . '<br />' . tra("(optional)") . '</td>'
				.'<td>' . tra( "Manually set the height of the video in pixels. The hight will be calculated automatically if not set.").'</td>'
			.'</tr>'
			.'<tr class="even">'
				.'<td>'.tra( "styling" ).'</td>'
				.'<td>'.tra( "string").'<br />'.tra("(optional)").'</td>'
				.'<td>'.tra( "Multiple styling options available: padding, margin, background, border, text-align, color, font, font-size, font-weight, font-family, align. Please view CSS guidelines on what values these settings take.").'</td>'
			.'</tr>'
		.'</table>'
		. tra("Example: ") . "{flashvideo id='13' text-align='center'}";
	return $help;
}

function data_flashvideo( $pData, $pParams ) { // NOTE: The original plugin had several parameters that have been dropped
	return data_attachment( $pData, $pParams );
}
?>
