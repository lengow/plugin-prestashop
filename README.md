# Module Prestashop Lengow

## Installation du module ##

### Cloner le repository de Bitbucket dans votre espace de travail ###

    cd ~/Documents/modules_lengow/prestashop/
    git clone git@bitbucket.org:lengow-dev/prestashop-v3.git lengow
    chmod 777 -R ~/Documents/modules_lengow/prestashop/lengow

### Installation dans Prestashop ###

    cd ~/Documents/modules_lengow/prestashop/tools
    sh install.sh ~/Documents/docker_images/presta16

Le script va créer des liens symboliques vers les sources du module

## Traduction ##

Pour traduire le projet il faut modifier les fichier *.yml dans le répertoire : Documents/modules_lengow/prestashop/lengow/translations/yml/

### Installation de Yaml Parser ###

    sudo apt-get install php5-dev libyaml-dev
    sudo pecl install yaml

### Mise à jour des traductions ###

Une fois les traductions terminées, il suffit de lancer le script de mise à jour de traduction :

    cd ~/Documents/modules_lengow/prestashop/lengow/tools
    php translate.php

## Compiler le module ##

    cd ~/Documents/modules_lengow/prestashop/lengow/tools
    sh build.sh 3.0.0

Le 3.0.0 représente la version du module qu'il faudra modifier.
Le module est alors directement compilé et copier sur le bureau avec le bon nom de version.