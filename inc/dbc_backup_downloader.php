<?php
/**
 * Created by IntelliJ IDEA.
 * User: damien
 * Date: 03/03/2014
 * Time: 22:58
 * Copyright © 2014, All Rights Reserved. Damien Saunders
 * Since v2.4
 *
 */

if (isset($_GET['download_file'])){

    $dbc_backup_rawfile = $_GET['download_file'];

    $dbc_backup_file = str_replace("..", "", $dbc_backup_rawfile);   // check if the file path is relative

    $dbc_server_path = ($_SERVER["DOCUMENT_ROOT"]);
    $filename = $dbc_server_path . $dbc_backup_file;
    $size= filesize($filename);

    if ( file_exists($filename) ){
        header('Pragma: public');
        header('Expires: 0');
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false); // required for certain browsers
        header('Content-Disposition: attachment; filename="'. basename($filename) . '";');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($filename));

        readfile($filename);
        exit;
    }
    else {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>Error 404: File Not Found: <br /><em>$file</em></h1>";
    }

}




?>
