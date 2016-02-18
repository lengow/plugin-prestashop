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

$default_locale = 'en';
$listDefaultValues = array();

$directory =  dirname(dirname(__FILE__)).'/translations/yml/';
$listFiles = array_diff(scandir($directory), array('..', '.'));

foreach ($listFiles as $list) {
    $ymlFile = yaml_parse_file($directory.$list);
    $locale =  basename($directory.$list, '.yml');

    $fp = fopen(dirname(dirname(__FILE__)).'/translations/'.$locale.'.csv', 'w+');
    foreach ($ymlFile as $language => $categories) {
        foreach ($categories as $category => $values) {
            foreach ($values as $key => $value) {
                $line = $category . '.' . $key . '|' . $value . PHP_EOL;
                fwrite($fp, $line);
            }
        }
    }
    fclose($fp);
}
