<?php

/*
 * New Translation system base on YAML files
 * We need to edit yml file for each languages
 * /translations/yml/en.yml
 * /translations/yml/fr.yml
 * /translations/yml/es.yml
 * /translations/yml/it.yml
 *
 * Execute this script to generate files
 *
 * Installation de YAML PARSER
 *
 * sudo apt-get install php5-dev libyaml-dev
 * sudo pecl install yaml
 */
error_reporting(E_ALL);
ini_set("display_errors", 1);

$listDefaultValues = [];

$directory = dirname(dirname(__FILE__)) . '/translations/yml/';
$listFiles = array_diff(scandir($directory), ['..', '.', 'index.php']);
$listFiles = array_diff($listFiles, ['en.yml']);
array_unshift($listFiles, "en.yml");

foreach ($listFiles as $list) {
    $ymlFile = yaml_parse_file($directory . $list);
    $locale = basename($directory . $list, '.yml');

    if ($list == 'log.yml') {
        $fp = fopen(dirname(dirname(__FILE__)) . '/translations/en.csv', 'a+');
    } else {
        $fp = fopen(dirname(dirname(__FILE__)) . '/translations/' . $locale . '.csv', 'w+');
    }

    foreach ($ymlFile as $language => $categories) {
        writeCsv($fp, $categories);
    }
    fclose($fp);
}

function writeCsv($fp, $text, &$frontKey = [])
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
