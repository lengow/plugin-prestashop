<?php

/*
 * Check MD5
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$base = dirname(dirname(__FILE__));
$fp = fopen(dirname(dirname(__FILE__)) . '/config/checkmd5.csv', 'wb+');

$listFolders = array(
    '/classes',
    '/controllers',
    '/mails',
    '/upgrade',
    '/views',
    '/webservice',
);

$filePaths = array(
    $base . '/es.php',
    $base . '/fr.php',
    $base . '/index.php',
    $base . '/it.php',
    $base . '/lengow.php',
    $base . '/loader.php',
    $base . '/config/index.php',
    $base . '/translations/en.csv',
    $base . '/translations/es.csv',
    $base . '/translations/fr.csv',
    $base . '/translations/index.php',
    $base . '/translations/it.csv',
);

foreach ($listFolders as $folder) {
    if (file_exists($base . $folder)) {
        $result = explorer($base . $folder);
        $filePaths = array_merge($filePaths, $result);
    }
}
foreach ($filePaths as $filePath) {
    if (file_exists($filePath)) {
        $checksum = array(str_replace($base, '', $filePath) => md5_file($filePath));
        writeCsv($fp, $checksum);
    }
}
fclose($fp);

function explorer($path)
{
    $paths = array();
    if (is_dir($path)) {
        $me = opendir($path);
        while ($child = readdir($me)) {
            if ($child !== '.' && $child !== '..' && $child !== 'checkmd5.csv') {
                $result = explorer($path . DIRECTORY_SEPARATOR . $child);
                $paths = array_merge($paths, $result);
            }
        }
    } else {
        $paths[] = $path;
    }
    return $paths;
}

function writeCsv($fp, $text, &$frontKey = array())
{
    if (is_array($text)) {
        foreach ($text as $k => $v) {
            $frontKey[] = $k;
            writeCsv($fp, $v, $frontKey);
            array_pop($frontKey);
        }
    } else {
        $line = join('.', $frontKey) . '|' . str_replace("\n", '<br />', $text) . PHP_EOL;
        fwrite($fp, $line);
    }
}
