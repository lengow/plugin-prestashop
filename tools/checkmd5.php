<?php

/*
 * Check MD5 
 */

error_reporting(E_ALL);
ini_set("display_errors", 1);

$list_folders = array(
    '/backward_compatibility/',
    '/classes/controllers/',
    '/classes/models/',
    '/controllers/admin/',
    '/toolbox/',
    '/toolbox/views/',
    '/upgrade/',
    '/views/css/',
    '/views/fonts/',
    '/views/img/',
    '/views/img/flag/',
    '/views/js/',
    '/views/js/lengow/',
    '/views/templates/admin/',
    '/views/templates/admin/lengow_feed/',
    '/views/templates/admin/lengow_feed/helpers/view/',
    '/views/templates/admin/lengow_help/',
    '/views/templates/admin/lengow_help/helpers/view/',
    '/views/templates/admin/lengow_home/',
    '/views/templates/admin/lengow_home/helpers/view/',
    '/views/templates/admin/lengow_main_setting/',
    '/views/templates/admin/lengow_main_setting/helpers/view/',
    '/views/templates/admin/lengow_order/',
    '/views/templates/admin/lengow_order/helpers/view/',
    '/views/templates/admin/lengow_order_setting/',
    '/views/templates/admin/lengow_order_setting/helpers/view/',
    '/views/templates/admin/order/',
    '/views/templates/front/',
    '/views/templates/mails/de/',
    '/views/templates/mails/en/',
    '/views/templates/mails/es/',
    '/views/templates/mails/fr/',
    '/views/templates/mails/de/',
    '/views/templates/mails/it/',
    '/views/templates/mails/nl/',
    '/views/templates/mails/pt/',
    '/views/templates/mails/sv/',
    '/webservice/',
);

$list_files = array(
    '/lengow.php',
    '/loader.php',
    '/AdminLengowFeed14.php',
    '/AdminLengowHelp14.php',
    '/AdminLengowHome14.php',
    '/AdminLengowMainSetting14.php',
    '/AdminLengowOrder14.php',
    '/AdminLengowOrderSetting14.php',
);

$fp = fopen(dirname(dirname(__FILE__)).'/toolbox/checkmd5.csv', 'w+');

foreach ($list_folders as $folder) {
    if (file_exists(dirname(dirname(__FILE__)).$folder)) {
        $folder_files = array_diff(
            scandir(dirname(dirname(__FILE__)).$folder),
            array('..', '.', 'index.php', 'checkmd5.csv')
        );
        foreach ($folder_files as $file) {
            $file_path = $folder.$file;
            if (file_exists(dirname(dirname(__FILE__)).$file_path)) {
                $checksum = array($file_path => md5_file(dirname(dirname(__FILE__)).$file_path));
                writeCsv($fp, $checksum);
            }
        }
    }
}
foreach ($list_files as $file) {
    if (file_exists(dirname(dirname(__FILE__)).$file)) {
        $checksum = array($file => md5_file(dirname(dirname(__FILE__)).$file));
        writeCsv($fp, $checksum);
    }
}

fclose($fp);

function writeCsv($fp, $text, &$frontKey = array())
{
    if (is_array($text)) {
        foreach ($text as $k => $v) {
            $frontKey[]= $k;
            writeCsv($fp, $v, $frontKey);
            array_pop($frontKey);
        }
    } else {
        $line = join('.', $frontKey).'|'.str_replace("\n", '<br />', $text).PHP_EOL;
        fwrite($fp, $line);
    }
}
