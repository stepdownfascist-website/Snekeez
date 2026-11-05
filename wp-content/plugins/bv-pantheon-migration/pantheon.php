<?php
/*
Plugin Name: Pantheon Migration
Plugin URI: https://pantheon.io
Description: The easiest way to migrate your site to Pantheon
Author: Pantheon
Author URI: https://pantheon.io
Version: 6.72
Network: True
License: GPLv2 or later
License URI: [http://www.gnu.org/licenses/gpl-2.0.html](http://www.gnu.org/licenses/gpl-2.0.html)
 */

/*  Copyright 2017  Pantheon Migrate  (email : support@blogvault.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* Global response array */

if (!defined('ABSPATH')) exit;
##OLDWPR##

require_once dirname( __FILE__ ) . '/wp_settings.php';
require_once dirname( __FILE__ ) . '/wp_site_info.php';
require_once dirname( __FILE__ ) . '/wp_db.php';
require_once dirname( __FILE__ ) . '/wp_api.php';
require_once dirname( __FILE__ ) . '/wp_actions.php';
require_once dirname( __FILE__ ) . '/info.php';
require_once dirname( __FILE__ ) . '/account.php';
require_once dirname( __FILE__ ) . '/helper.php';
require_once dirname( __FILE__ ) . '/wp_file_system.php';
##WP_2FA_REQUIRE_FILE##
##WP_LOGIN_WHITELABEL_REQUIRE_FILE##
##WPCACHEMODULE##


$bvsettings = new PTNWPSettings();
$bvsiteinfo = new PTNWPSiteInfo();
$bvdb = new PTNWPDb();


$bvapi = new PTNWPAPI($bvsettings);
$bvinfo = new PTNInfo($bvsettings);
$wp_action = new PTNWPAction($bvsettings, $bvsiteinfo, $bvapi);

register_uninstall_hook(__FILE__, array('PTNWPAction', 'uninstall'));
register_activation_hook(__FILE__, array($wp_action, 'activate'));
register_deactivation_hook(__FILE__, array($wp_action, 'deactivate'));


add_action('wp_footer', array($wp_action, 'footerHandler'), 100);
add_action('ptn_clear_bv_services_config', array($wp_action, 'clear_bv_services_config'));

##SOADDUNINSTALLACTION##

##DISABLE_OTHER_OPTIMIZATION_PLUGINS##

##WPCLIMODULE##

if (is_admin()) {
	require_once dirname( __FILE__ ) . '/wp_admin.php';
	$wpadmin = new PTNWPAdmin($bvsettings, $bvsiteinfo);
	add_action('admin_init', array($wpadmin, 'initHandler'));
	add_filter('all_plugins', array($wpadmin, 'initWhitelabel'));
	add_filter('plugin_row_meta', array($wpadmin, 'hidePluginDetails'), 10, 2);
	##HEALTH_INFO_HOOK##
	if ($bvsiteinfo->isMultisite()) {
		add_action('network_admin_menu', array($wpadmin, 'menu'));
	} else {
		add_action('admin_menu', array($wpadmin, 'menu'));
	}
	add_filter('plugin_action_links', array($wpadmin, 'settingsLink'), 10, 2);
	add_action('admin_head', array($wpadmin, 'removeAdminNotices'), 3);

	##MG_AJAX_ACTIONS##
	##POPUP_ON_DEACTIVATION##
	##ACTIVATEWARNING##
	add_action('admin_enqueue_scripts', array($wpadmin, 'ptnsecAdminMenu'));
}

if ((array_key_exists('bvreqmerge', $_POST)) || (array_key_exists('bvreqmerge', $_GET))) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
	$_REQUEST = array_merge($_GET, $_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
}

##REMOVE_BV_PRELOAD_MODULE##
##PHP_ERROR_MONITORING_MODULE##
if ($bvinfo->hasValidDBVersion()) {
	##ACTLOGMODULE##
	##MAINTENANCEMODULE##
}

if (PTNHelper::getRawParam('REQUEST', 'bvplugname') == "pantheon") {
	require_once dirname( __FILE__ ) . '/callback/base.php';
	require_once dirname( __FILE__ ) . '/callback/response.php';
	require_once dirname( __FILE__ ) . '/callback/request.php';
	require_once dirname( __FILE__ ) . '/recover.php';

	$pubkey = PTNHelper::getRawParam('REQUEST', 'pubkey');
	$pubkey = isset($pubkey) ? PTNAccount::sanitizeKey($pubkey) : '';
	$rcvracc = PTNHelper::getRawParam('REQUEST', 'rcvracc');

	if (isset($rcvracc)) {
		$bvctag = PTNHelper::getRawParam('REQUEST', 'bvctag');
		$bvctag = isset($bvctag) ? PTNAccount::sanitizeKey($bvctag) : null;
		$account = PTNRecover::find($bvsettings, $pubkey, $bvctag);
	} else {
		$account = PTNAccount::find($bvsettings, $pubkey);
	}

	$request = new PTNCallbackRequest($account, $_REQUEST, $bvsettings); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$response = new PTNCallbackResponse($request->bvb64cksize);

	if ($request->authenticate() === 1) {
		$bv_frm_tstng = PTNHelper::getRawParam('REQUEST', 'bv_frm_tstng');
		if (isset($bv_frm_tstng)) {
			##FORM_TESTING##
		} else {
			##BVBASEPATH##

			require_once dirname( __FILE__ ) . '/callback/handler.php';

			$params = $request->processParams($_REQUEST); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ($params === false) {
				$response->terminate($request->corruptedParamsResp());
			}
			$request->params = $params;
			$callback_handler = new PTNCallbackHandler($bvdb, $bvsettings, $bvsiteinfo, $request, $account, $response);
			if ($request->is_aftershutdown) {
				$callback_handler->deferExecutionUntilShutdown();
			} else if ($request->is_afterload) {
				add_action('wp_loaded', array($callback_handler, 'execute'));
			} else if ($request->is_admin_ajax) {
				add_action('wp_ajax_bvadm', array($callback_handler, 'bvAdmExecuteWithUser'));
				add_action('wp_ajax_nopriv_bvadm', array($callback_handler, 'bvAdmExecuteWithoutUser'));
			} else {
				$callback_handler->execute();
			}
		}
	} else {
		$response->terminate($request->authFailedResp());
	}
} else {
	if ($bvinfo->hasValidDBVersion()) {
		##PROTECTMODULE##
		##DYNSYNCMODULE##
	}
	
	##HIDEPLUGINUPDATEMODULE##
	##THIRDPARTYCACHINGMODULE##
}

##WP2FAMODULE##
##WP_LOGIN_WHITELABEL_MODULE##
##CLEAR_WP_2FA_CONFIG_ACTION##
##PLUGIN_LOADED_MODULE##
