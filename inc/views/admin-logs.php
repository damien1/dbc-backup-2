<?php
/**
 * Created by IntelliJ IDEA.
 * User: damien
 * Date: 02/03/2014
 * Time: 14:06
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
        foreach($cfg['logs'] AS $log):
            $dbc_file_name = ($cfg['export_dir']).'/'.basename($log['file']);?>
            <tr>
                <td><?php echo ++$i; ?></td>
                <td><?php echo date('Y-m-d H:i:s', $log['started']); ?></td>
                <td><?php echo $log['status']; ?></td>
                <td><?php echo round($log['took'], 3); ?> <?php _e('seconds', 'dbcbackup'); ?></td>
                <td><?php echo basename($log['file']); ?> <a target="_blank" href="http://dev.damien.home/wp-content/plugins/dbc-backup-2/inc/dbc_backup_downloader.php?download_file=<?php echo $dbc_file_name;?>">dl</a>  </td>
                <td><?php echo size_format($log['size'], 2); ?></td>
                <td><?php echo sprintf(__("%s old backups", 'dbcbackup'), intval($log['removed'])); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif;