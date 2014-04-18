<?php
/**
 * Created by IntelliJ IDEA.
 * User: damien
 * Date: 02/03/2014
 * Time: 14:06
 * Copyright © 2014, All Rights Reserved. Damien Saunders
 * Since v2.4
 */

  // some variables
 $dbc_date = '';
 $dbc_status = '';
 $dbc_time = '';
 $dbc_file_name = '';
 $dbc_backup_file = '';
 $dbc_server_path = ($_SERVER["DOCUMENT_ROOT"]);
 $dbc_downloader_path = '/inc/dbc_backup_downloader.php?download_file='; // path to downloader
 global $dbc_plugin_path;
 global $dbc_plugin_url;

if(!empty($cfg['logs'])):
   ?>

    <table class="widefat">
        <thead>
        <tr>
            <th scope="col">#</th>
            <th scope="col"><?php _e('Date', 'dbcbackup'); ?></th>
            <th scope="col"><?php _e('Status', 'dbcbackup'); ?></th>
            <th scope="col"><?php _e('Finished In', 'dbcbackup'); ?></th>
            <th scope="col"><?php _e('File', 'dbcbackup'); ?></th>
            <th scope="col"><?php _e('Filesize', 'dbcbackup'); ?></th>
            <th scope="col"><?php _e('Backups Removed', 'dbcbackup'); ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        $i = 0;
        foreach($cfg['logs'] AS $log):
            $dbc_base_name =  basename($log['file']);
            $dbc_file_name = ($cfg['export_dir']).'/'.$dbc_base_name;
            $dbc_backup_file = str_replace("..", "", $dbc_file_name);   // check if the file path is relative

            $dbc_date= date('Y-m-d H:i:s', $log['started']);
            $dbc_status = $log['status'];
            $dbc_time =  round($log['took'], 3) .' seconds';
            $dbc_size =  size_format($log['size'], 2);
            $dbc_delete = intval($log['removed']);
            $ret = '<tr name="' . $dbc_base_name .'">';
            $ret .= '<td>' . ++$i . '</td>';
            $ret .= '<td>' . $dbc_date . '</td>';
            $ret .= '<td>' . $dbc_status . '</td>';
            $ret .= '<td>' . $dbc_time . '</td>';
            $ret .= '<td>';
            if (!file_exists($dbc_server_path . $dbc_backup_file )){
            $ret .= $dbc_base_name;
            }

            elseif (file_exists($dbc_server_path . $dbc_backup_file )){
            $ret .= '<a target="_blank" href="';
            $ret .= $dbc_plugin_url;
            $ret .= $dbc_downloader_path;
            $ret .= $dbc_file_name;
            $ret .= '">dl</a>';
            }
            $ret .= '</td>';
            $ret .= '<td>' . $dbc_size . '</td>';
            $ret .= '<td>' . $dbc_delete . '</td>';
            $ret .= '</tr>';
            echo $ret;

            endforeach; ?>
        </tbody>
    </table>
<?php endif;