<?php

// @todo fix up the translations
// dbcbackup_locale();



/* ------------------------------------------------------------------------ *
 * This is really important shit that was in the plugin admin page
 * since version 2.3 moved to this
 * @todo move all the _POST URL stuff to a separate file
 * ------------------------------------------------------------------------ */

//variables we will use
$temp='';
$dbc_cnt='';   // takes the $_POST variables
$clear='';
$schedule='';



  // first we get the options from the dbc
$cfg = get_option('dbcbackup_options');
  // then we check what the _POST value is
  // store the _POST variables so we can use them

if (isset($_POST['quickdo']))
{
    $dbc_cnt = ($_POST['quickdo']);
    // uncomment the next line to print_r the value
    // echo "_post [quickdo] ";
    //print_r($dbc_cnt);
}

elseif (isset($_POST['do']))
{
    $dbc_cnt = ($_POST['do']);
    // echo "_post [do] ";
    //   print_r($dbc_cnt2);

}

else {
    //echo "nothing is set";
    //unset($dbc_cnt);
    }

if ($dbc_cnt == 'dbc_logerase')
{

  //if the $dbc_cnt / _POST value is logerase we check admin referer and then delete the logs from db.

	check_admin_referer('dbc_quickdo');
	$cfg['logs'] = array();
	update_option('dbcbackup_options', $cfg);
}

elseif   ($dbc_cnt == 'dbc_backupnow')
    //($_POST['quickdo'] == 'dbc_backupnow')
    {
        //if $dbc_cnt is quickdo then we do a backupnow
        check_admin_referer('dbc_quickdo');
        $cfg['logs'] = dbcbackup_run('backupnow');
    }
//elseif ($_POST['do'] == 'dbc_setup')

 elseif ($dbc_cnt == 'dbc_setup')
    {
        // echo "test";
        //print_r($dbc_cnt);
	//we check the admin referrer
        // and setup the $temp values that we need

	check_admin_referer('dbc_options');
	$temp['export_dir']		=	rtrim(stripslashes_deep(trim($_POST['export_dir'])), '/');
	$temp['compression']	=	stripslashes_deep(trim($_POST['compression']));
	$temp['gzip_lvl']		=	intval($_POST['gzip_lvl']);
	$temp['period']			=	intval($_POST['severy']) * intval($_POST['speriod']);
	$temp['active']			=	(bool)$_POST['active'];
	$temp['rotate']			=	intval($_POST['rotate']);
	$temp['logs']			=	$cfg['logs'];

	$timenow 				= 	time();
	$year 					= 	date('Y', $timenow);
	$month  				= 	date('n', $timenow);
	$day   					= 	date('j', $timenow);
	$hours   				= 	intval($_POST['hours']);
	$minutes 				= 	intval($_POST['minutes']);
	$seconds 				= 	intval($_POST['seconds']);
	$temp['schedule'] 		= 	mktime($hours, $minutes, $seconds, $month, $day, $year);
	update_option('dbcbackup_options', $temp);

    // now we check and compare existing settings -- if the plugin has been installed and used ...

	if($cfg['active'] AND !$temp['active']) $clear = true;
	if(!$cfg['active'] AND $temp['active']) $schedule = true;
	if($cfg['active'] AND $temp['active'] AND (array($hours, $minutes, $seconds) != explode('-', date('G-i-s', $cfg['schedule'])) OR $temp['period'] != $cfg['period']) )
	{
		$clear = true;
		$schedule = true;
	}
	if($clear) 		wp_clear_scheduled_hook('dbc_backup');
	if($schedule) 	wp_schedule_event($temp['schedule'], 'dbc_backup', 'dbc_backup');
	    // so finally if you are using the plugin for the first time ... $cfg = $temp
        $cfg = $temp;
        // if it saves ok ... we display the message that the options were saved
        ?>

        <div id="message" class="updated fade"><p><?php _e('Options saved.') ?></p></div><?php
}


//
// here we go make directories
//
$is_safe_mode = ini_get('safe_mode') == '1' ? 1 : 0;
if(!empty($cfg['export_dir']))
{
	if(!is_dir($cfg['export_dir']) AND !$is_safe_mode)
	{
		@mkdir($cfg['export_dir'], 0777, true);
		@chmod($cfg['export_dir'], 0777);

		/* ------------------------------------------------------------------------ *
		 * This is really important shit that shouldnt be in the admin page
		 * since version 2.3 moved to this file
		 * @todo move all the custom error messages to somewhere else
		 * ------------------------------------------------------------------------ */


         // @TODO change this if clause as it doesnt work for first time users.
		if(is_dir($cfg['export_dir']))
		{
			$dbc_msg[] = sprintf(__("Backup Folder <strong>%s</strong> was created.", 'dbcbackup'), $cfg['export_dir']);
		}
		else
		{
			$dbc_msg[] = $is_safe_mode ? __('PHP Safe Mode Is On', 'dbcbackup') : sprintf(__("Folder <strong>%s</strong> wasn't created, check permissions.", 'dbcbackup'), $cfg['export_dir']);
		}
	}
	else
	{
		$dbc_msg[] = sprintf(__("Backup Folder <strong>%s</strong> exists.", 'dbcbackup'), $cfg['export_dir']);
	}

	if(is_dir($cfg['export_dir']))
	{
		$condoms = array('.htaccess', 'index.html');
		foreach($condoms as $condom)
		{
			if(!file_exists($cfg['export_dir'].'/'.$condom))
			{
				if($file = @fopen($cfg['export_dir'].'/'.$condom, 'w'))
				{
					$cofipr =  ($condom == 'index.html')? '' : "Order allow,deny\ndeny from all";
					fwrite($file, $cofipr);
					fclose($file);
					$dbc_msg[] =  sprintf(__("File <strong>%s</strong> was created.", 'dbcbackup'), $condom);
				}
				else
				{
					$dbc_msg[] = sprintf(__("File <strong>%s</strong> wasn't created, check permissions.", 'dbcbackup'), $condom);
				}
			}
			else
			{
				$dbc_msg[] = sprintf(__("<strong>%s</strong> protection exists.", 'dbcbackup'), $condom);
			}
		}
	}
}
else
{
	$dbc_msg[] = __('Specify the folder where the backups will be stored', 'dbcbackup');
}






