<?php
/**
 * @version      $Header: /cvsroot/bitweaver/_bit_treasury/Attic/TreasurySystem.php,v 1.11 2007/07/21 09:31:40 squareing Exp $
 *
 * @author       xing  <xing@synapse.plus.com>
 * @version      $Revision: 1.11 $
 * created      Monday Jul 03, 2006   11:06:47 CEST
 * @package      treasury
 * @copyright    2003-2006 bitweaver
 * @license      LGPL {@link http://www.gnu.org/licenses/lgpl.html}
 **/

/**
 * Setup
 */ 
require_once( LIBERTY_PKG_PATH.'LibertySystem.php' );
define( 'TREASURY_DEFAULT_MIME_HANDLER', 'mime_default' );
define( 'TREASURY_MIME', 'mime_handler' );

/**
 *   TreasurySystem 
 * 
 * @package treasury
 * @uses LibertySystem
 */
class TreasurySystem extends LibertySystem {
	// Contains plugin information
	var $mPlugins;

	/**
	 * Initiate class
	 * 
	 * @access public
	 * @return void
	 */
	function TreasurySystem() {
		// Set some treasury specific options that we can extend LibertySystem
		$this->mSystem     = TREASURY_PKG_NAME;
		$this->mPluginPath = TREASURY_PKG_PATH."plugins/";

		LibertySystem::LibertySystem( FALSE );
	}

	/**
	 * Will return the plugin that is responsible for the given mime type
	 *
	 * @param string $pFileHash['mimetype'] (required if no tmp_name) Mime type of file that needs to be dealt with
	 * @param string $pFileHash['tmp_name'] (required if no mimetype) Full path to file that needs to be dealt with
	 * @access public
	 * @return handler plugin guid
	 * TODO: Currently this will return the first found handler - might want to have a sort order?
	 **/
	function lookupMimeHandler( &$pFileHash ) {
		global $gBitSystem;

		if( empty( $this->mPlugins ) ) {
			$this->scanAllPlugins( NULL, "mime\." );
		}

		$pFileHash['type'] = $gBitSystem->lookupMimeType( $pFileHash['name'] );

		foreach( $this->mPlugins as $handler => $plugin ) {
			if( $plugin['is_active'] && !empty( $plugin['mimetypes'] ) && is_array( $plugin['mimetypes'] ) ) {
				foreach( $plugin['mimetypes'] as $pattern ) {
					if( preg_match( $pattern, $pFileHash['type'] ) ) {
						return $handler;
					}
				}
			}
		}

		return TREASURY_DEFAULT_MIME_HANDLER;
	}

	/**
	 * Get the function of the plugin responsible for dealing with a given upload
	 * 
	 * @param string $pGuid GUID of plugin used
	 * @param string $pFunctionName Function type we want to use
	 * @access public
	 * @return function name
	 */
	function getPluginFunction( $pGuid, $pFunctionName ) {
		$ret = parent::getPluginFunction( $pGuid, $pFunctionName );
		if( empty( $ret ) ) {
			$ret = parent::getPluginFunction( TREASURY_DEFAULT_MIME_HANDLER, $pFunctionName );
		}

		return $ret;
	}
}
?>
