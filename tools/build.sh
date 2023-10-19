#!/bin/bash
# Build archive for PrestaShop module
# Step :
#     - Remove .DS_Store
#     - Remove .README.md
#     - Remove .idea
#     - Clean export folder
#     - Clean logs folder
#     - Clean translation folder
#     - Remove tools folder
#     - Remove .git Folder and .gitignore

remove_if_exist(){
    if [ -f $1 ]; then
      rm $1
    fi
}

remove_directory(){
    if [ -d "$1" ]; then
        rm -rf $1
    fi
}
remove_files(){
    DIRECTORY=$1
    FILE=$2
    if [ -f "${DIRECTORY}/${FILE}" ]
    then
        find $DIRECTORY -name $FILE -nowarn -exec rm -rf {} \;
        echo -e "- Delete ${FILE} : ${VERT}DONE${NORMAL}"
    fi
    if [ -d "${DIRECTORY}/${FILE}" ]
    then
        rm -Rf ${DIRECTORY}/${FILE}
    fi
}

remove_directories(){
    DIRECTORY=$1
    find $DIRECTORY -maxdepth 1 -mindepth 1 -type d -exec rm -rf {} \;
    echo -e "- Delete $FILE : ${VERT}DONE${NORMAL}"
}
# check parameters
if [ -z "$1" ]; then
	echo 'Version parameter is not set'
	echo
	exit 0
else
	VERSION="$1"
	ARCHIVE_NAME='lengow.prestashop.'$VERSION'.zip'
fi

# Variables
FOLDER_TMP="/tmp/lengow"
FOLDER_LOGS="/tmp/lengow/logs"
FOLDER_CONFIG="/tmp/lengow/config"
FOLDER_EXPORT="/tmp/lengow/export"
FOLDER_TOOLS="/tmp/lengow/tools"
FOLDER_TRANSLATION="/tmp/lengow/translations/yml"

VERT="\e[32m"
ROUGE="\e[31m"
NORMAL="\e[39m"
BLEU="\e[36m"


# process
echo
echo "#####################################################"
echo "##                                                 ##"
echo -e "##       "${BLEU}Lengow Magento${NORMAL}" - Build Module             ##"
echo "##                                                 ##"
echo "#####################################################"
echo
PWD=$(pwd)
FOLDER=$(dirname ${PWD})
echo ${FOLDER}
# remove TMP FOLDER
if [ -d "${FOLDER_TMP}" ]
then
    rm -Rf ${FOLDER_TMP}
fi
# create folder
if [ -d "${FOLDER_TMP}" ]
then
    rm -Rf ${FOLDER_TMP}
fi
mkdir ${FOLDER_TMP}

if [ ! -d "$FOLDER" ]; then
	echo -e "Folder doesn't exist : ${ROUGE}ERROR${NORMAL}"
	echo
	exit 0
fi
PHP=$(which php8.1)
echo ${PHP}

# generate translations
${PHP} translate.php
echo -e "- Generate translations : ${VERT}DONE${NORMAL}"
# create files checksum
${PHP} checkmd5.php
echo -e "- Create files checksum : ${VERT}DONE${NORMAL}"
# remove TMP FOLDER
remove_directory $FOLDER_TMP
# copy files
cp -rRp $FOLDER $FOLDER_TMP
# remove dod
remove_files $FOLDER_TMP "dod.md"
# remove Readme
remove_files $FOLDER_TMP "README.md"
# remove .gitignore
remove_files $FOLDER_TMP ".gitignore"
# remove php-cs-fixer-cache
remove_files $FOLDER_TMP ".php-cs-fixer.cache"
# remove .git
remove_files $FOLDER_TMP ".git"
# remove .DS_Store
remove_files $FOLDER_TMP ".DS_Store"
# remove .AdminLengowHome.gif
remove_files $FOLDER_TMP "AdminLengowHome.gif"
# remove .idea
remove_files $FOLDER_TMP ".idea"
# remove Jenkinsfile
remove_files $FOLDER_TMP "Jenkinsfile"
# clean Config Folder
remove_files $FOLDER_CONFIG "marketplaces.json"
# clean Log Folder
remove_files $FOLDER_LOGS "*.txt"
echo -e "- Clean logs folder : ${VERT}DONE${NORMAL}"
# clean export folder
remove_directories $FOLDER_EXPORT
echo -e "- Clean export folder : ${VERT}DONE${NORMAL}"
# clean tools folder
remove_directory $FOLDER_TOOLS
echo -e "- Remove Tools folder : ${VERT}DONE${NORMAL}"
# remove TMP FOLDER_TRANSLATION
remove_directory $FOLDER_TRANSLATION
echo -e "- Remove Translation yml folder : ${VERT}DONE${NORMAL}"
# remove config.xml
find $FOLDER_TMP -name "config.xml" -delete
echo -e "- Delete config.xml : ${VERT}DONE${NORMAL}"
# remove config_fr.xml
find $FOLDER_TMP -name "config_fr.xml" -delete
echo -e "- Delete config_fr.xml : ${VERT}DONE${NORMAL}"
# remove config_es.xml
find $FOLDER_TMP -name "config_es.xml" -delete
echo -e "- Delete config_es.xml : ${VERT}DONE${NORMAL}"
# remove config_it.xml
find $FOLDER_TMP -name "config_it.xml" -delete
echo -e "- Delete config_it.xml : ${VERT}DONE${NORMAL}"
# remove todo.txt
find $FOLDER_TMP -name "todo.txt" -delete
echo -e "- todo.txt : ${VERT}DONE${NORMAL}"
# make zip
cd /tmp
zip "-r" $ARCHIVE_NAME "lengow"
echo -e "- Build archive : ${VERT}DONE${NORMAL}"
if [ -d  "~/Bureau" ]
then
    mv $ARCHIVE_NAME ~/Bureau
else 
    mv $ARCHIVE_NAME ~/shared
fi
