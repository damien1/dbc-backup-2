<?php

// Copyright © 2014, All Rights Reserved. Damien Saunders

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
$dbc_export_dir='';



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

elseif   ('dbc_backupnow' == $dbc_cnt )
    //($_POST['quickdo'] == 'dbc_backupnow')
    {
        //if $dbc_cnt is quickdo then we do a backupnow
        check_admin_referer('dbc_quickdo');
        $cfg['logs'] = dbcbackup_run('backupnow');
        return dbc_check_condoms();
        //$dbc_msg[] ='done for now';
        //return $dbc_msg;
    }

 elseif ('dbc_setup' == $dbc_cnt)
    {

    //print_r($dbc_cnt);
	//we check the admin referrer
        // and setup the $temp values that we need
     $dbc_export_dir =   ($cfg['export_dir']);


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

        if(empty ($dbc_export_dir) && empty ($temp['export_dir'])  )
        {
            $ret = "<div class='error' name='new-user-error'><p>New User? No backup location set. Enter something like ../wp-content/backup below and then click Save Settings (1).</p></div>";

            echo $ret;
        }

        elseif(!empty ($dbc_export_dir) && empty ($temp['export_dir'])  )
        {
            $ret = "<div class='error' name='silly-user-error'><p>Hey -- Why would you try to do clear your backup folder (2).</p></div>";

            echo $ret;
        }





        else {
    // save the options to the database
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

        <div class="updated"><p><?php _e('Options saved.') ?></p></div><?php

//
// here we go make directories
// since v2.4 we dont overwrite the backup  folder if it already exists
//
     $is_safe_mode = ini_get('safe_mode') == '1' ? 1 : 0;
     if(!empty($dbc_export_dir))
     {
         if(!is_dir($dbc_export_dir) AND !$is_safe_mode AND !file_exists($dbc_export_dir))
         {
             @mkdir($dbc_export_dir, 0777, true);
             @chmod($dbc_export_dir, 0777);



             // @TODO change the error messages as half of them are now obsolete
             if(is_dir($dbc_export_dir))
             {
                 $dbc_msg[] = sprintf(__("Backup Folder <strong>%s</strong> was created.", 'dbcbackup'), $dbc_export_dir);
             }
             else
             {
                 $dbc_msg[] = $is_safe_mode ? __('PHP Safe Mode Is On', 'dbcbackup') : sprintf(__("Folder <strong>%s</strong> wasn't created, check permissions.", 'dbcbackup'), $dbc_export_dir);
             }
         }
         else
         {
             $dbc_msg[] = sprintf(__("Backup Folder <strong>%s</strong> exists.", 'dbcbackup'), $dbc_export_dir);
         }

         if(is_dir($dbc_export_dir))
         {
             $condoms = array('.htaccess', 'index.html');
             foreach($condoms as $condom)
             {
                 if(!file_exists($dbc_export_dir.'/'.$condom))
                 {
                     if($file = @fopen($dbc_export_dir.'/'.$condom, 'w'))
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
   elseif(empty ($temp['export_dir']))
     {
         $ret = "<div class='update-nag'><p>New User - No backup location set. Enter something like ../wp-content/backup below and then click Save Settings (2).</p></div>";

         echo $ret;
     }
        }
}

/**
 * dbc_check_condoms()
 * Looks to see if you're protected
 * The joke about condoms was in the 1st developers code.
 * Since v2.4
 * @return string
 */
function dbc_check_condoms(){
    $cfg = get_option('dbcbackup_options');
    $dbc_export_dir =   ($cfg['export_dir']);
    if(!is_dir($dbc_export_dir))
    {
    $dbc_msg = sprintf(__("Backup Folder <strong>%s</strong> not found.", 'dbcbackup'), $dbc_export_dir);
    }
    else
    {
        $dbc_msg = sprintf(__("Backup Folder <strong>%s</strong> exists.", 'dbcbackup'), $dbc_export_dir);
    }

    // done the directory move on
    if(is_dir($dbc_export_dir))
    {
        $condoms = array('.htaccess', 'index.html');
        foreach($condoms as $condom)
        {
            if(!file_exists($dbc_export_dir.'/'.$condom))
            {
                $dbc_msg .= sprintf(__("<br><strong>%s</strong> not found.", 'dbcbackup'), $condom);
                $ret = sprintf("<div class='error' name='error-not-found'><p>You're not protected -- <strong>%s</strong> was deleted. Click save settings to create a new one (4).</p></div>", $condom);
            }
            else
            {
                $dbc_msg .= sprintf(__("<br><strong>%s</strong> protection exists.", 'dbcbackup'), $condom);
            }
        }
    }



    echo $ret;
    return $dbc_msg;

}