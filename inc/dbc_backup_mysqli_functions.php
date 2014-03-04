<?php

/**
 * Copyright © 2014, All Rights Reserved. Damien Saunders
 * Since v2.4
 * @param $table
 * @param $fp
 *
 * @return bool
 */
 function dbcbackup_structure($table, $fp)
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$is_safe_mode = ini_get('safe_mode') == '1' ? 1 : 0;
	if (!$is_safe_mode) set_time_limit(600);
	$res ='';
	$res .= "\n";
	$res .= "# Dump of table ".dbcbackup_backquote($table)."\n";
	$res .= "# ------------------------------------------------------- \n";
	$res .= "\n";

	if($sql = mysqli_query($link, "SHOW CREATE TABLE ".dbcbackup_backquote($table)))
	{
		$res .= "DROP TABLE IF EXISTS ".dbcbackup_backquote($table).";\n\n";
		$row = mysqli_fetch_array($sql, MYSQLI_BOTH);
		$create_table = $row[1];
		unset($row);
		$create_table = preg_replace('/^CREATE TABLE/', 'CREATE TABLE ', $create_table);
		//@todo delete the next line if this works
		//$create_table = preg_replace("/ENGINE\s?=/", "TYPE=", $create_table);
		$create_table .= ";\n";
		$res .= $create_table;
		mysqli_free_result($sql);
		$status = true;
	}
	else
	{
		$tmp = mysqli_error($link);
		$res .= "--".$tmp;
		$status = false;
	}

	dbcbackup_write($fp, $res);
	return ($status);
}

/* ------------------ */

/**
 * @param $table
 * @param $fp
 * @return bool
 */function dbcbackup_data($table, $fp)
{
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	$is_safe_mode = ini_get('safe_mode') == '1' ? 1 : 0;
	if (!$is_safe_mode) set_time_limit(600);
	$res ='';
	$res .= "\n";
	$res .= "# Dumping data for table ".dbcbackup_backquote($table)."\n";
	$res .= "# ----------------------------------------------------- \n";
	$res .= "\n";
	dbcbackup_write($fp, $res);

	if($sql = mysqli_query($link, "SELECT * FROM ".dbcbackup_backquote($table)))
	{
		list($numfields, $fields_meta) = dbcbackup_fields($sql);
		//$res = "LOCK TABLES ".dbcbackup_backquote($table)." WRITE;\n";
		$res = '';
		dbcbackup_write($fp, $res);
		$search       = array("\x00", "\x0a", "\x0d", "\x1a"); //\x08\\x09, not required | Taken from phpMyAdmin.
		$replace      = array('\0', '\n', '\r', '\Z');
		while ($row = mysqli_fetch_array($sql, MYSQLI_BOTH))
		{
			$res = "INSERT INTO ".dbcbackup_backquote($table)." VALUES (";
			$fieldcounter = -1;
			$firstfield = 1;
			while (++$fieldcounter < $numfields)
			{
				//tmp array and array dereferencing here
				$fields_tmp = array(1,2,3);

				if (!$firstfield){
					$res .= ', ';
				}else{
					$firstfield = 0;
				}
              	if (!isset($row[$fieldcounter]) || is_null($row[$fieldcounter])) {
                    $res     .= 'NULL';
                } elseif (isset($fields_meta[$fieldcounter]->numeric) && $fields_meta[$fieldcounter]->type != 'timestamp'
                        && ! $fields_meta[$fieldcounter]->blob) {
                    $res .= $row[$fieldcounter];
                } elseif (stristr($fields_tmp[1], 'BINARY')
                        && isset($GLOBALS['hexforbinary'])
                        && $fields_meta[$fieldcounter]->type != 'datetime'
                        && $fields_meta[$fieldcounter]->type != 'date'
                        && $fields_meta[$fieldcounter]->type != 'time'
                        && $fields_meta[$fieldcounter]->type != 'timestamp'
                       ) {
                    // empty blobs need to be different, but '0' is also empty :-(
                    if (empty($row[$fieldcounter]) && $row[$fieldcounter] != '0') {
                        $res .= '\'\'';
                    } else {
                        $res .= '0x' . bin2hex($row[$fieldcounter]);
                    }
                // something else -> treat as a string
                } else {
                    $res .= "'".str_replace($search, $replace, dbcbackup_addslashes($row["$fieldcounter"]))."'";
                } // end if
			}
			$res .= ");\n";
			dbcbackup_write($fp, $res);
		}
		//$res = "UNLOCK TABLES;\n";
		$res = '';
		mysqli_free_result($sql);
		$status = true;
	}
	else
	{
		$tmp = mysqli_error($link);
		$res .= "--".$tmp;
		$status = false;
	}
	dbcbackup_write($fp, $res);
	return $status;
}


/**
 * Add back ticks to tables and db-names in SQL queries.
 * @param $a_name
 * @return array|string
 */
function dbcbackup_backquote($a_name)
{
	if (!empty($a_name) && $a_name != '*')
	{
        if (is_array($a_name))
		{
			$result = array();
			reset($a_name);
			while(list($key, $val) = each($a_name))
			{
			  $result[$key] = '`' . $val . '`';
			}
			 return $result;
        }
		else
		{
			return '`' . $a_name . '`';
        }
    }
	else
	{
        return $a_name;
    }
}

/* ------------------ */

/**
 * This is the header of the SQL file
 * @return string
 */
function dbcbackup_header()
{
	//@todo tidy this up   with my plugin info
	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD);
	$header = "# ####################################################### \n";
	$header .= "#  \n";
	$header .= "# DBC Backup 2\n";
	$header .= "# Version 2.4 for Wordpress 3.6 \n";
	$header .= "# Plugin by Damien Saunders http://wordpress.damien.co \n";
	$header .= "# Generated: ".date('l dS \of F Y h:i A', time() + (get_option('gmt_offset') * 3600))." \n";
	$header .= "# MySQL Server: ".mysqli_get_host_info($link)."\n";
	$header .= "# MySQL Server version: ".mysqli_get_server_info($link)."\n";
	$header .= "# Database: ".dbcbackup_backquote(DB_NAME)."\n";
	$header .= "#  \n";
	$header .= "# ####################################################### \n";
	return $header;
}

/* ------------------ */

/**
 * @param $result
 * @return array
 */
function dbcbackup_fields($result) {
    $fields       = 	array();
    $num_fields   = 	mysqli_num_fields($result);
    for ($i = 0; $i < $num_fields; $i++)
	{
        $fields[] = mysqli_fetch_field($result);
    }
    return (array($num_fields, $fields));
}

/* ------------------ */

/**
 * @param string $a_string
 * @param bool   $is_like
 * @param bool   $crlf
 * @param bool   $php_code
 * @return mixed
 */
function dbcbackup_addslashes($a_string = '', $is_like = false, $crlf = false, $php_code = false)
{	//Taken from phpMyAdmin.
	if ($is_like) {
		$a_string = str_replace('\\', '\\\\\\\\', $a_string);
	} else {
		$a_string = str_replace('\\', '\\\\', $a_string);
	}
	if ($crlf) {
		$a_string = str_replace("\n", '\n', $a_string);
		$a_string = str_replace("\r", '\r', $a_string);
		$a_string = str_replace("\t", '\t', $a_string);
	}
	if ($php_code) {
		$a_string = str_replace('\'', '\\\'', $a_string);
	} else {
		$a_string = str_replace('\'', '\'\'', $a_string);
	}
	return $a_string;
}

/* ------------------ */

/**
 * PHP file write and compression settings
 * @param        $fp
 * @param string $mode
 * @return array
 */
function dbcbackup_open($fp, $mode='write')
{
	switch(DBC_COMPRESSION)
	{
		case 'bz2':
			if($mode=='write'){
				$fp = $fp.".sql.bz2";
				$file = @bzopen($fp, "w");
			}else{ $file = @bzopen($fp, "r"); }
		break;

		case 'gz':
			if($mode=='write'){
				$fp = $fp.".sql.gz";
				$file = @gzopen($fp, 'w'.DBC_GZIP_LVL);
			}else{ $file = @gzopen($fp, "r"); }
		break;

		default:
			if($mode=='write'){
				$fp = $fp.".sql";
				$file = @fopen($fp, "w");
			}else{ $file = @fopen($fp, "r"); }
		break;
	}
	return array($file, $fp);
}

/* ------------------ */

/**
 * @param $fp
 * @return string
 */
function dbcbackup_read($fp)
{
	switch(DBC_COMPRESSION)
	{
		case 'bz2':
			$data = bzread($fp, 4096);
		break;

		case 'gz':
			$data = gzread($fp, 4096);
		break;
		
		default:
			$data = fread($fp, 4096);
		break;
	}
	return ($data);
}

/* ------------------ */

/**
 * @param $fp
 * @param $code
 */function dbcbackup_write($fp, $code)
{	
	switch(DBC_COMPRESSION)
	{
		case 'bz2':
			bzwrite($fp, $code);
		break;

		case 'gz':
			gzwrite($fp, $code);
		break;

		default:
			fwrite($fp, $code);
		break;
	}
	return;
}

/* ------------------ */

/**
 * @param $fp
 */function dbcbackup_close($fp)
{
	switch(DBC_COMPRESSION)
	{
		case 'bz2':
			bzclose($fp);
		break;

		case 'gz':
			gzclose($fp);
		break;

		default:
			fclose($fp);
		break;
	}
	return;
}

/* ------------------ */

/**
 * @param $cfg
 * @param $timenow
 * @return int
 */function dbcbackup_rotate($cfg, $timenow)
{
	$removed = 0;
	if($cfg['rotate'] >= 0)
	{
		$compare = 86400 * $cfg['rotate'];
		if ($handle = opendir($cfg['export_dir'])) 
		{
			$dump_formats = array('gz', 'sql', 'bz2');
			while (false !== ($file = readdir($handle))) 
			{	
				$ext = substr(strrchr($file, '.'), 1);
				if ($file != "." && $file != ".." && in_array(strtolower($ext), $dump_formats)) 
				{ 	
					$name = explode('_', $file);
					list($month, $day, $yearhour, $minute, $seconds) = explode('.', $name[1]);
					list($year, $hour) = explode('-', $yearhour);
					$generated = mktime($hour,  $minute, $seconds, $month, $day, $year);

					if($timenow > $generated + $compare)
					{
						if(@unlink($cfg['export_dir'].'/'.$file))
						{
							$removed++;
						}
					}
				}
			}
		  closedir($handle);
		} 
	}
	return $removed;
}
