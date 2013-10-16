<?php
/*
Plugin Name: DBC Backup 2
Plugin URI: http://wordpress.damien.co/plugins?utm_source=WordPress&utm_medium=dbc-backup&utm_campaign=WordPress-Plugin&utm_keyword=source
Description: Safe & easy backup for your WordPress database. Just schedule and forget.
Version: 2.3a
Author: Damien Saunders
Author URI: http://damien.co/?utm_source=WordPress&utm_medium=dbc-backup&utm_campaign=WordPress-Plugin&utm_keyword=source
License: GPLv2 or later
*/

/**
 * You shouldn't be here.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Globals
 */
define ("DBCBACKUP2_VERSION", "2.3a");
define ("PLUGIN_NAME", "dbc-backup-2");
$plugin = plugin_basename(__FILE__);
global $damien_dbc_option;
global $plugin;


/**
 * Gets the stored presets from the database
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
		$new_options['export_dir'] = "../wp-content/backup";
		add_option('dbcbackup_options', $new_options);
	}
}
add_action('admin_init', 'damien_dbc_set_default_options');




/**
 *  Uninstall function
 */
function dbcbackup_uninstall()
{
	wp_clear_scheduled_hook('dbc_backup');	
	delete_option('dbcbackup_options');
}
register_deactivation_hook(__FILE__, 'dbcbackup_uninstall');

add_action('dbc_backup', 'dbcbackup_run');


/**
 * @param string $mode
 *
 * @return array|bool
 */function dbcbackup_run($mode = 'auto')
{
	if(defined('DBC_BACKUP_RETURN')) return;
	$cfg = get_option('dbcbackup_options'); 
	if(!$cfg['active'] AND $mode == 'auto') return;
	if(empty($cfg['export_dir'])) return;
	if($mode == 'auto')	dbcbackup_locale();
	
	require_once ('inc/functions.php');
	define('DBC_COMPRESSION', $cfg['compression']);
	define('DBC_GZIP_LVL', $cfg['gzip_lvl']);
	define('DBC_BACKUP_RETURN', true);
	
	$timenow 			= 	time();
	$mtime 				= 	explode(' ', microtime());
	$time_start 		= 	$mtime[1] + $mtime[0];
	$key 				= 	substr(md5(md5(DB_NAME.'|'.microtime())), 0, 6);
	$date 				= 	date('m.d.y-H.i.s', $timenow);
	list($file, $fp) 	=	dbcbackup_open($cfg['export_dir'].'/Backup_'.$date.'_'.$key);
	
	if($file)
	{
		$removed = dbcbackup_rotate($cfg, $timenow);
		@set_time_limit(0);
		$sql = mysql_query("SHOW TABLE STATUS FROM ".DB_NAME);
		dbcbackup_write($file, dbcbackup_header());
		while ($row = mysql_fetch_array($sql))
		{	
			dbcbackup_structure($row['Name'], $file);
			dbcbackup_data($row['Name'], $file);
		}
		dbcbackup_close($file);
		$result = __('Successful', 'dbcbackup');
	}
	else
	{
		$result = sprintf(__("Failed To Open: %s.", 'dbcbackup'), $fp);
	}
	$mtime 			= 	explode(' ', microtime());
	$time_end 		= 	$mtime[1] + $mtime[0];
	$time_total 	= 	$time_end - $time_start;
	$cfg['logs'][] 	= 	array ('file' => $fp, 'size' => @filesize($fp), 'started' => $timenow, 'took' => $time_total, 'status'	=> $result, 'removed' => $removed);					
	update_option('dbcbackup_options', $cfg);
	return ($mode == 'auto' ? true : $cfg['logs']);
}
/*
 * i18n -- I need to look at the POT stuff for v2.2
 */
/**
 *
 */function dbcbackup_locale()
{
	load_plugin_textdomain('dbcbackup', 'wp-content/plugins/dbc-backup-2');
}

/*
 * 2.1 Add Menu - moved to Tools
 */
add_action('admin_menu', 'dbcbackup_menu');
/**
 *
 */function dbcbackup_menu()
{
	if(function_exists('add_management_page')) 
	{
		add_management_page('DBC Backup', 'DBC Backup', 'manage_options', dirname(__FILE__).'/dbcbackup-options.php');
	}
}

/*
 * Add WP-Cron Job
 */
add_filter('cron_schedules', 'dbcbackup_interval');
/**
 * @return array
 */function dbcbackup_interval() {
	$cfg = get_option('dbcbackup_options');
	$cfg['period'] = ($cfg['period'] == 0) ? 86400 : $cfg['period'];
	return array('dbc_backup' => array('interval' => $cfg['period'], 'display' => __('DBC Backup Interval', 'dbc_backup')));
}

/*
 * 2.1 Add settings link on Installed Plugin page
 */
/**
 * @param $links
 * @return mixed
 */function dbc_backup_settings_link($links) {
  $settings_link = '<a href="tools.php?page=dbc-backup-2/dbcbackup-options.php">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'dbc_backup_settings_link' );


/*
 * RSS feed
 */
/**
 *
 */function dbc_backup_rss_display()
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