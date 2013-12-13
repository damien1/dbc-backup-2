<?php
/*
Plugin Options for DBC Backup 2

*/

if ( ! defined( 'ABSPATH' ) ) exit;
if(!defined('WP_ADMIN') OR !current_user_can('manage_options')) wp_die(__('You do not have sufficient permissions to access this page.'));

//admin page functions
require_once ('inc/admin-functions.php');



/* ------------------------------------------------------------------------ *
 * This is the layout for the Admin Page
 * since v2
 * thanks to Frank Bueltge for his Admin Page template
 * http://bueltge.de/
 * ------------------------------------------------------------------------ */

 ?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"></div>
			<h2><?php _e('DBC Backup Options', 'dbcbackup'); ?></h2>
			<div class="metabox-holder has-right-sidebar">
				<?php

				//$screen = get_current_screen();
                //                    print_r($screen->id);
                //                        return $screen;
				settings_errors();?>

	<!-- SIDEBAR -->				
			<div class="inner-sidebar">
			<div class="postbox">
				<h3><span>Backup Settings Status</span></h3>
				<div class="inside">
					<?php echo implode('<br />', $dbc_msg); ?>
					</div>
				</div>
						<div class="postbox">
							<h3><span>Thanks from Damien</span></h3>
							<div class="inside">
							<?php echo 'Thank you for using version ' . DBCBACKUP2_VERSION; ?>. <a target="_blank" href="http://damien.co/?utm_source=WordPress&utm_medium=dbc-backup-installed-2.3&utm_campaign=WordPress-Plugin">Damien</a></p>
					<p>Please add yourself to <a target="_blank" href="http://wordpress.damien.co/wordpress-mailing-list/?utm_source=WordPress&utm_medium=dbc-backup-installed-2.3&utm_campaign=WordPress-Plugin">my mailing list</a> to be the first to hear WordPress tips and updates for this plugin.</p>
					<p>Let me and your friends know you installed this:</p>
				<a href="https://twitter.com/share" class="twitter-share-button" data-text="I just installed DBC Backup 2 for WordPress" data-url="http://damiens.ws/MLLV3H" data-counturl="http://wordpress.damien.co/dbc-backup-2" data-count="horizontal" data-via="damiensaunders">Tweet</a><script type="text/javascript" src="https://platform.twitter.com/widgets.js"></script>	
			
							</div>
						</div>
			
						<div class="postbox">
							<h3><span>Help & Support</span></h3>
							<div class="inside">
								<ul>
								<li><a target="_blank" href="http://wordpress.damien.co/dbc-backup-2/?utm_source=WordPress&utm_medium=dbc-backup-installed-2.3&utm_campaign=WordPress-Plugin">Help & FAQ's</a></li>
								<li><a target="_blank" href="http://wordpress.damien.co/?utm_source=WordPress&utm_medium=dbc-backup-installed-2.3&utm_campaign=WordPress-Plugin">More WordPress Tips & Ideas</a></li>
								</ul>
							</div>
						</div>
						<div class="postbox">
							<h3><span>Services & Plugins from Damien</span></h3>
							<div class="inside">
							<ul>
								<li><a target="_blank" href="http://wordpress.damien.co/isotope/?utm_source=WordPress&utm_medium=dbc-sitewide-installed&utm_campaign=WordPress-Plugin">Isotope</a> - does amazing visual things for your website.</li>
							<li><a target="_blank" href="http://whitetshirtdigital.com/shop/?utm_source=WordPress&utm_medium=dbc-backup-installed-2.3&utm_campaign=WordPress-Plugin">Learn more about digital marketing or WordPress</a> with Damien.</li>
							</ul>
							</div>
						</div>			
			
					</div> <!-- .inner-sidebar -->
		
		<!-- BODY COLUMN -->	
					<div id="post-body">
						<div id="post-body-content">
	<!-- 
		SETTINGS 
	    -->	
			<div class="postbox">
				<h3><span>Backup Schedule & Backup Now</span></h3>
				<div class="inside">
					<ul class="subsubsub">
		<li><form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"> 
			<?php wp_nonce_field('dbc_quickdo'); ?>
			<select name="quickdo" style="display:inline;">
				<option value="dbc_logerase"><?php _e('Erase Logs', 'dbcbackup'); ?></option>
				<option value="dbc_backupnow"><?php _e('Backup Now', 'dbcbackup'); ?></option>
			</select>
			<input style="display:inline;" type="submit" name="submit" class="button" value="<?php _e('Go', 'dbcbackup'); ?>" /> 
		</form></li>
	</ul>
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>"> 
	<?php wp_nonce_field('dbc_options'); ?>
	<table class="form-table">
	   <tr valign="top">
		   <th scope="row" nowrap="nowrap"><?php _e('Export Directory:', 'dbcbackup'); ?></th>
		   <td><input size="40" type="text"  name="export_dir" value="<?php echo esc_attr($cfg['export_dir']); ?>" /><br />
			<?php _e('Use ../wp-content/backup or a full path like e.g. /home/username/public_html/databack', 'dbcbackup'); ?></td>
		</tr>
		<tr valign="top">
		   <th scope="row" nowrap="nowrap"><?php _e('Compression:', 'dbcbackup'); ?></th>
		   <td>
			<?php
			$none_selected 	= ($cfg['compression'] == 'none') 	? 	'selected' : '';
			$gz_selected 	= ($cfg['compression'] == 'gz') 	? 	'selected' : '';
			$bz2_selected 	= ($cfg['compression'] == 'bz2') 	? 	'selected' : '';  
			?>
			<select name="compression" style="display:inline;">
				<option value="none" <?php echo $none_selected; ?>><?php _e('None', 'dbcbackup'); ?></option>
				<?php if(function_exists("gzopen")): ?> <option value="gz" <?php echo $gz_selected; ?>><?php _e('Gzip', 'dbcbackup'); ?></option> <?php endif; ?>
				<?php if(function_exists("bzopen")): ?> <option value="bz2" <?php echo $bz2_selected; ?>><?php _e('Bzip2', 'dbcbackup'); ?></option><?php endif; ?>
			</select>&nbsp;  
			<?php if(function_exists("gzopen")) : ?>
				<select name="gzip_lvl">
				<?php 	
				for($i = 1; $i <= 9; $i++) : 
				$selected = ($cfg['gzip_lvl'] == $i) ? 'selected' : '';
				?>
					<option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php _e('Level', 'dbcbackup'); ?> <?php echo $i; ?></option>
				<?php endfor; ?>
				</select><br />
				<?php _e('The level option applies only if you select Gzip', 'dbcbackup'); ?>
			<?php endif; ?>  
			</td>
		</tr>
		<tr valign="top">
			<th scope="row" nowrap="nowrap"><?php _e('Backup Schedule:', 'dbcbackup'); ?><br /><?php _e('Server Dates/Times', 'dbcbackup'); ?></th>
			<td><?php 
				list($hours, $minutes, $seconds) = explode('-', date('G-i-s', $cfg['schedule'])); 
				$times = array('hours', 'minutes', 'seconds');
				$periods = array(3600 => __('Hour(s)', 'dbcbackup'), 86400 => __('Day(s)', 'dbcbackup'), 604800 => __('Week(s)', 'dbcbackup'), 2592000 => __('Month(s)', 'dbcbackup'));
				$tmonth	=	$cfg['period'] / 2592000;
				$tweek	=	$cfg['period'] / 604800;
				$tday	=	$cfg['period'] / 86400;
				$thour	=	$cfg['period'] / 3600;
				
				if(is_int($tmonth) 		AND $tmonth > 0)	{	$speriod = 2592000;	$severy	= $tmonth;	}
				elseif(is_int($tweek) 	AND $tweek > 0)		{	$speriod = 604800;	$severy	= $tweek;	}
				elseif(is_int($tday) 	AND $tday > 0)		{	$speriod = 86400;	$severy	= $tday;	}
				elseif(is_int($thour)	AND $thour > 0)		{	$speriod = 3600;	$severy	= $thour;	}
				?><label><?php _e('Run Every', 'dbcbackup'); ?>:
				
				<select name="severy">
				<?php for ($i = 1; $i <= 12; $i++): $selected = ($severy == $i) ? 'selected' : ''; ?>
				<option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
				<?php endfor; ?>
				</select></label>&nbsp;
			   
				<select name="speriod"><?php
				foreach($periods as $period => $display):
				$selected = ($period == $speriod) ? 'selected' : ''; ?>
				<option value="<?php echo $period; ?>" <?php echo $selected; ?>><?php echo $display; ?></option>
				<?php endforeach; ?>
				</select> at this time</p><?php
				
				foreach($times AS $time):
					$max = $time == 'hours' ? 24 : 60; ?><label>
                    <?php
                    if($time == 'hours')  _e('Hours', 'dbcbackup');
					elseif($time == 'minutes')  _e('Minutes', 'dbcbackup');
					elseif($time == 'seconds')  _e('Seconds', 'dbcbackup');
					?>: <select name="<?php echo $time; ?>">
					<?php for ($i = 0; $i<$max; $i++): $selected = ($$time == $i) ? 'selected' : ''; ?>
					<option value="<?php echo $i; ?>" <?php echo $selected; ?>><?php echo $i; ?></option>
					<?php endfor; ?>
					</select></label>&nbsp;
					<?php endforeach;?></p>
				<?php _e('Active:', 'dbcbackup'); ?> <input type="checkbox" name="active" value="1" <?php echo ($cfg['active'] ? 'checked="checked"' : ''); ?> /></p>
				<?php if($next_scheduled = wp_next_scheduled('dbc_backup')):
						_e('Next Schedule is on: ', 'dbcbackup');  echo date('Y-m-d H:i:s', $next_scheduled); ?> | <?php
					endif;
				_e('Current Date:', 'dbcbackup'); ?> <?php echo date('Y-m-d H:i:s', time()); ?></td>
		</tr>
		<tr valign="top">
		   <th scope="row" nowrap="nowrap"><?php _e('Remove Backups:', 'dbcbackup'); ?></th>
		   <td>
           <label><?php _e('Older Than x', 'dbcbackup'); ?>:
           <select name="rotate">
           <?php for ($i = -1; $i <= 90; $i++): 
			switch($i)
			{
				case -1:	$display = __('Disabled', 'dbcbackup');											break;		
				case 0:		$display = __('All Old Backups', 'dbcbackup');									break;
				default:	$display = $i.' '.($i > 1 ? __('Days', 'dbcbackup') : __('Day', 'dbcbackup'));	break;
			}
		   ?>
            <option value="<?php echo $i; ?>" <?php echo ($cfg['rotate'] == $i ? 'selected' : ''); ?>><?php echo $display; ?></option>
			<?php endfor; ?>
           </select></label><br /><?php _e('Deletion of old backups occurs the next time a backup is run.', 'dbcbackup'); ?></td>
		</tr>
		 <tr>
			<td colspan="2" align="center">
				<input type="hidden" name="do" value="dbc_setup" />
				<input type="submit" name="submit" class="button-primary" value="<?php _e('Save Changes', 'dbcbackup'); ?>" /> 
			</td>
		</tr> 
	</table>
	</form>
				</div> <!-- .inside -->
			</div>
		<!-- DBC BACKUP LOG -->					
			<div class="postbox">
				<h3><span>Backup Log</span></h3>
				<div class="inside">
					<?php 
		/* 
		 * here we insert the log files if there are any 
		 */
					if(!empty($cfg['logs'])): ?>
					<table class="widefat">
					<thead>
					  <tr>
						<th scope="col">#</th>
						<th scope="col"><?php _e('Date', 'dbcbackup'); ?></th>
						<th scope="col"><?php _e('Status', 'dbcbackup'); ?></th>
						<th scope="col"><?php _e('Finished In', 'dbcbackup'); ?></th>
						<th scope="col"><?php _e('File', 'dbcbackup'); ?></th>
						<th scope="col"><?php _e('Filesize', 'dbcbackup'); ?></th>
						<th scope="col"><?php _e('Removed', 'dbcbackup'); ?></th>
					  </tr>
					</thead>
					<tbody>
					<?php 
					$i = 0;
					foreach($cfg['logs'] AS $log): ?>
					  <tr>
						<td><?php echo ++$i; ?></td>
						<td><?php echo date('Y-m-d H:i:s', $log['started']); ?></td>
						<td><?php echo $log['status']; ?></td>
						<td><?php echo round($log['took'], 3); ?> <?php _e('seconds', 'dbcbackup'); ?></td>
						<td><?php echo basename($log['file']); ?></td>
						<td><?php echo size_format($log['size'], 2); ?></td>
						<td><?php echo sprintf(__("%s old backups", 'dbcbackup'), intval($log['removed'])); ?></td>
					  </tr>
					  <?php endforeach; ?>
					</tbody>
					</table>
					<?php endif;?>
				</div> <!-- .inside -->
			</div>
			<!-- end of BACKUP LOG -->
						</div> <!-- #post-body-content -->
					</div> <!-- #post-body -->
			
				</div> <!-- .metabox-holder -->
				
			</div> <!-- .wrap -->
