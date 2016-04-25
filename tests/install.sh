#!/bin/bash

ROOT_DIR="/var/www/test/"
PRESTA_DIR_NAME="presta_1_6"
PRESTA_DIR="$ROOT_DIR/$PRESTA_DIR_NAME"
if [ ! -d "$ROOT_DIR" ]; then
    mkdir $ROOT_DIR
fi
cd $ROOT_DIR
git clone --depth=50 --branch=1.6.1.3 https://github.com/PrestaShop/PrestaShop.git $PRESTA_DIR_NAME
php $PRESTA_DIR/install-dev/index_cli.php  \
     --language=fr \
     --country=fr \
     --domain=prestashop.unit.test \
     --base_uri= \
     --db_name=presta_1_6_test \
     --db_user=root \
     --db_password=root \
     --db_create=1 \
     --name=prestashop.unit.test \
     --password=lengow44
ln -s /var/www/presta/prestashop-v3 $PRESTA_DIR/modules/
mv $PRESTA_DIR/modules/prestashop-v3 $PRESTA_DIR/modules/lengow

cd $PRESTA_DIR
curl -sS https://getcomposer.org/installer | php
php composer.phar require  guzzlehttp/guzzle:^6.1
php composer.phar require fzaninotto/faker


