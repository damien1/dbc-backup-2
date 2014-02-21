<?php

/**
* @param string $mode
*
* @return array|bool
*/

function dbcbackup_run($mode = 'auto')
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);
if(defined('DBC_BACKUP_RETURN')) return;
$cfg = get_option('dbcbackup_options');
if(!$cfg['active'] AND $mode == 'auto') return;
if(empty($cfg['export_dir'])) return;
if($mode == 'auto')	dbcbackup_locale();

//    @TODO FIX THIS LINE
	require_once('dbc_backup_mysqli_functions.php');

//require_once ('inc/dbc_backup_mysqli_functions.php');
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
$sql = mysqli_query($link, "SHOW TABLE STATUS FROM ".DB_NAME);
dbcbackup_write($file, dbcbackup_header());
while ($row = mysqli_fetch_array($sql, MYSQLI_BOTH))
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

add_action('dbc_backup', 'dbcbackup_run');