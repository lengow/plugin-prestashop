<?php


$currentDirectory = str_replace('modules/lengow/webservice/', '', dirname($_SERVER['SCRIPT_FILENAME']) . "/");

$sep = DIRECTORY_SEPARATOR;
require_once $currentDirectory . 'config' . $sep . 'config.inc.php';
require_once $currentDirectory . 'init.php';
require_once $currentDirectory . 'modules/lengow/lengow.php';

require_once $currentDirectory.'/vendor/fzaninotto/faker/src/autoload.php';


$faker = Faker\Factory::create();



$categoryCollection = array();
$result = Db::getInstance()->executeS(
    'SELECT * FROM ' . _DB_PREFIX_ . 'category WHERE active = 1 AND is_root_category = 0'
);
foreach ($result as $categ) {
    $categoryCollection[] = $categ["id_category"];
}
$totalCategory = count($categoryCollection)-1;
/*
for ($i = 1; $i <= 100; $i++) {

    $categoryId = $categoryCollection[rand(0, $totalCategory)];
    $qty = $faker->numberBetween(0, 20);

    $product = new Product();
    $product->reference = $faker->numerify('SKU'.str_pad($product->id, 4, "0", STR_PAD_LEFT));
    $product->ean13 = $faker->ean13();
    $product->name = array(1 => $faker->sentence(3));
    $product->link_rewrite = array(1 => $faker->slug());
    $product->id_category = $categoryId;
    $product->id_category_default = $categoryId;
    $product->redirect_type = '404';
    $product->price = $faker->randomFloat(2, 1, 300);
    $product->minimal_quantity = 1;
    $product->show_price = 1;
    $product->add();
    $product->addToCategories(array($categoryId));

    StockAvailable::updateQuantity($product->id, 0, $qty);

}*/

$result = Db::getInstance()->executeS('SELECT id_product FROM ' . _DB_PREFIX_ . 'product WHERE id_product > 7');
foreach ($result as $product) {
    $categoryId = $categoryCollection[rand(0, $totalCategory)];
    $qty = $faker->numberBetween(0, 20);

    $product = new Product($product['id_product']);
    $product->ean13 = $faker->ean13();
    $product->name = array(1 => $faker->sentence(3));
    $product->link_rewrite = array(1 => $faker->slug());
    $product->id_category = $categoryId;
    $product->id_category_default = $categoryId;
    $product->redirect_type = '404';
    $product->price = $faker->randomFloat(2, 1, 300);
    $product->minimal_quantity = 1;
    $product->show_price = 1;
    $product->reference = $faker->numerify('SKU'.str_pad($product->id, 4, "0", STR_PAD_LEFT));
    $product->save();
}
