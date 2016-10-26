# Installation Prestashop #

## Installation du module ##

### Cloner le repository de Bitbucket dans votre espace de travail ###

    cd ~/Documents/modules_lengow/prestashop/
    git clone git@bitbucket.org:lengow-dev/prestashop-v3.git lengow
    chmod 777 -R ~/Documents/modules_lengow/prestashop/lengow

### Installation dans Prestashop ###

    cd ~/Documents/modules_lengow/prestashop/lengow/tools
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

## Mise à jour du fichier d'intégrité des données ##

    cd ~/Documents/modules_lengow/prestashop/lengow/tools
    php checkmd5.php

Le fichier checkmd5.csv sera directement créé dans le dossier /toolbox

## Compiler le module ##

    cd ~/Documents/modules_lengow/prestashop/lengow/tools
    sh build.sh x.x.x

Le x.x.x représente la version du module qu'il faudra modifier.
Le module est alors directement compilé et copier sur le bureau avec le bon nom de version.

## Versionning GIT ##

1 - Prendre un ticket sur JIRA et cliquer sur Créer une branche dans le bloc développement à droite

2 - Sélectionner en "Repository" lengow-dev/prestashop-v3, pour "Branch from" prendre dev et laisser le nom du ticket pour "Branch name"

3 - Créer la nouvelle branche

4 - Exécuter le script suivant pour changer de branche 

    cd ~/Documents/modules_lengow/prestashop/lengow/
    git fetch
    git checkout "Branch name"

5 - Faire le développement spécifique

6 - Lorsque que le développement est terminé, faire un push sur la branche du ticket

    git add .
    git commit -m 'My ticket is finished'
    git pull origin "Branch name"
    git push origin "Branch name"

7 - Dans Bitbucket, dans l'onglet Pull Requests créer une pull request

8 - Sélectionner la branche du tiket et l'envoyer sur la branche de dev de lengow-dev/prestashop-v3

9 - Bien nommer la pull request et mettre toutes les informations nécessaires à la vérification

10 - Mettre tous les Reviewers nécessaires à la vérification et créer la pull request

11 - Lorsque la pull request est validée, elle sera mergée sur la branche de dev