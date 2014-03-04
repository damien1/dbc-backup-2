<?php
/**
 * Created by IntelliJ IDEA.
 * User: damien
 * Date: 23/02/2014
 * Time: 14:23
 * Copyright ©2014 All Rights Reserved Damien Saunders
 * Since v2.4
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;

    wp_clear_scheduled_hook('dbc_backup');
    delete_option('dbcbackup_options');
}
?>
