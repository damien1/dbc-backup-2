<?php
/*
Plugin Name: DBC Backup 2
Plugin URI: http://wordpress.damien.co/plugins?utm_source=WordPress&utm_medium=dbc-backup&utm_campaign=WordPress-Plugin&utm_keyword=source
Description: Safe & easy backup for your WordPress database. Just schedule and forget.
Version: @THISBUILD@
Author: Damien Saunders
Author URI: http://damien.co/?utm_source=WordPress&utm_medium=@PLUGINNAME@-@THISBUILD@&utm_campaign=WordPress-Plugin&utm_keyword=source
License: GPLv2 or later
*/

/**
 * You shouldn't be here.
 * since v2.4 changed to WPINC  from ABSPATH
 * this means WordPress has been called :)
 *
 */
if ( ! defined( 'WPINC' ) ) die;


/**
 * PUBLIC FACING FUNCTIONS
 * since v2.3
 *
 */

// PHP 5.0 introduced mysqli module
// but it was not enabled by default til PHP 5.3
if (version_compare(PHP_VERSION, '5.3.0', '<')) {
    // echo 'I am using Old PHP, my version: ' . PHP_VERSION . "\n";
    error_log("WordPress requires at least 5.2.4. Check that PHP mysqli connector is default enabled - since v5.3", 0);
    require_once ('inc/dbc_backup_mysql_functions.php');
    // backup files
    require_once ('inc/dbc_backup_mysql_backup_run.php');

}  elseif (version_compare(PHP_VERSION, '5.3.0') >= 0) {
    //echo 'I am at least PHP version 5.3.0, my version: ' . PHP_VERSION . "\n";
    require_once('inc/dbc_backup_mysqli_functions.php');
// backup files
   require_once ('inc/backup_run.php');
}

// backup files
//require_once ('inc/backup_run.php');

//functions for the admin page are called at the top of the options.php
//include ('inc/admin_functions.php');


/**
 * Globals
 */
$plugin = plugin_basename(__FILE__);
global $damien_dbc_option;
global $plugin;

// @todo implement this default location and make it work for 1st time users.
define ("DBCBACKUP2_LOCATION", "../wp-content/backup");


/**
 *
 *  ENVIRONMENT SETUP
 *
 */



// Define application environment
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ?
    getenv('APPLICATION_ENV') :
    'production'));
//echo getenv('APPLICATION_ENV');

if ( 'development'== APPLICATION_ENV) {
    define ("DBCBACKUP2_VERSION", "100");
    define ("PLUGIN_NAME", "@PLUGINNAME@");
    //echo APPLICATION_ENV;
} elseif ( 'production'== APPLICATION_ENV) {
    define ("DBCBACKUP2_VERSION", "@THISBUILD@");
    define ("PLUGIN_NAME", "@PLUGINNAME@");
}




/* ------------------------------------------------------------------------ *
 * If you delete the plugin - I'll feel sad
 * ------------------------------------------------------------------------ */

register_deactivation_hook(__FILE__, 'dbcbackup_deactiveate');


// note there is no plugin activation hook as the user has to first define the settings //



/**
 * Gets the stored presets from the database so we can use them in the Admin page
 * since v2.1
 * @return mixed
 */
function dbc_get_global_options(){
	$damien_dbc_option  = get_option('dbcbackup_options');
	return $dbc_option;
}


/**
 * Here we add the plugin default settings
 * Since v2.3 we set the default directory to wp-content/backup
 *
 */
function damien_dbc_set_default_options() {
	if (get_option('dbcbackup_options') === false){
		$new_options['compression'] = "none";
		$new_options['gzip_lvl'] = 0;
		$new_options['period'] = 86400;
		$new_options['schedule'] = time();
		$new_options['active'] = 0;
		$new_options['rotate'] =-1;
		$new_options['version'] = DBCBACKUP2_VERSION;
		$new_options['export_dir'] = "";
		add_option('dbcbackup_options', $new_options);
	}
}
add_action('admin_init', 'damien_dbc_set_default_options');


/**
 * i18n -- I need to look at the POT stuff for v2.2
 *
 */function dbcbackup_locale()
{
	load_plugin_textdomain('dbcbackup', 'wp-content/plugins/dbc-backup-2');
}

/**
 * Add Menu
 * since v0
 * v2.1 moved to Tools menu
 * @todo v2.3 added helptab - work in progress
 */
function dbcbackup_menu()
{
      global $dbc_backup_admin;
      $dbc_backup_admin = add_management_page('DBC Backup', 'DBC Backup', 'manage_options', 'dbc_backup_admin', 'damien_dbc_inc_my_page');
	  include dirname(__FILE__).'/inc/help_tab.php';
        	// Adds my_help_tab when my_admin_page loads
        	add_action('load-'.$dbc_backup_admin, 'damien_dbc_admin_add_help_tab');

}
add_action('admin_menu', 'dbcbackup_menu');


/**
 *
 */
function damien_dbc_inc_my_page()
{
	include dirname(__FILE__) . '/inc/dbcbackup-options.php';
}




/**
 * Add WP-Cron Job
 * since v0
 * @return array
 */
 function dbcbackup_interval() {
	$cfg = get_option('dbcbackup_options');
	$cfg['period'] = ($cfg['period'] == 0) ? 86400 : $cfg['period'];
	return array('dbc_backup' => array('interval' => $cfg['period'], 'display' => __('DBC Backup Interval', 'dbc_backup')));
}
add_filter('cron_schedules', 'dbcbackup_interval');


/**
 * Add settings link on Installed Plugin page
 * since v2.1
 * changes v2.3.25 updated the settings link
 * @param $links
 * @return mixed
 */
function dbc_backup_settings_link($links) {
  $settings_link = '<a href="tools.php?page=dbc_backup_admin">Settings</a>';
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'dbc_backup_settings_link' );



/* ------------------------------------------------------------------------ *
 * Boring stuff
 * ------------------------------------------------------------------------ */



/**
 * RSS feed
 */

function dbc_backup_rss_display()
{
$dbc_feed = 'http://damien.co/feed';

echo '<div class="rss-widget">';

wp_widget_rss_output( array(
	'url' => $dbc_feed,
	'title' => 'RSS Feed',
	'items' => 3,
	'show summary' => 1,
	'show_author' => 0,
	'show date' => 0,
	));
echo '</div>';
}

?>