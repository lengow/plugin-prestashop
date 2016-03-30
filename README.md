# Module Prestashop Lengow

## Installation ##

### Installation de prestashop ###

1 - Aller sur le site de prestashop : https://www.prestashop.com/fr/telechargement

2 - Dans l'onglet release archive, choisir la version a télécharger (ex: la 1.6.1.4)

3 - Décompresser le projet dans /var/www/prestashop/prestashop-1-6

4 - Modification du fichier /etc/hosts

    echo "127.0.0.1 prestashop-1-6.local" >> /etc/hosts

5 - Création du fichier virtualhost d'apache

    sudo vim /etc/apache2/sites-enabled/prestashop-1-6.conf 
    <VirtualHost *:80>
    DocumentRoot /var/www/prestashop/prestashop-1-6/
    ServerName prestashop-1-6.local
    <Directory /var/www/prestashop/prestashop-1-6/>
        Options FollowSymLinks Indexes MultiViews
        AllowOverride All
    </Directory>
        ErrorLog /var/log/apache2/prestashop-1-6-error_log
        CustomLog /var/log/apache2/prestashop-1-6-access_log common
    </VirtualHost>
6 - Rédémarrer apache

    sudo service apache2 restart
    
7 - Creation de la base de données
    
    mysql -u root -p -e "CREATE DATABASE prestashop-1-6"; 
        
8 - Se connecter sur prestashop pour lancer l'installation
    
    http://prestashop-1-6.local     

### Récupération des sources ###

Cloner le repo dans votre espace de travail :

    cd /var/www/prestashop/
    git clone git@bitbucket.org:lengow-dev/prestashop-v3.git

### Installation dans Magento ###

Exécuter le script suivant :

    cd /var/www/prestashop/prestashop-v3/tools
    ./install.sh /var/www/prestashop/prestashop-1-6

Le script va créer des liens symboliques vers les sources du module

## Traduction ##

Pour traduire le projet il faut modifier les fichier *.yml dans le répertoire : /var/www/prestashop/prestashop-v3/translations/yml/

### Installation de Yaml Parser ###

    sudo apt-get install php5-dev libyaml-dev
    sudo pecl install yaml

### Mise à jour des traductions ###

Une fois les traductions terminées, il suffit de lancer le script de mise à jour de traduction :

    cd /var/www/prestashop/prestashop-v3/tools
    php translate.php