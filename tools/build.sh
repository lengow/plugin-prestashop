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

unameOut="$(uname -s)"
case "${unameOut}" in
    Linux*)     CURRENT_OS=Linux;;
    Darwin*)    CURRENT_OS=MacOS;;
    CYGWIN*)    CURRENT_OS=Cygwin;;
    MINGW*)     CURRENT_OS=MinGw;;
    MSYS_NT*)   CURRENT_OS=Git;;
    *)          CURRENT_OS="UNKNOWN:${unameOut}"
esac
echo "RUNNING ON: ${CURRENT_OS} ..."
sleep 3
source decrypt.sh
if [ !  -f "./vars.sh" ]; then
    echo 'Variables file not found. can not build module archive.'
    exit 0
fi

remove_if_exist(){
    if [ -f "$1" ]; then
      rm "$1"
    fi
}

remove_directory(){
    if [ -d "$1" ]; then
        rm -rf "$1"
    fi
}
remove_files(){
    DIRECTORY="$1"
    FILE="$2"
    if [ -f "${DIRECTORY}/${FILE}" ]; then
        find "$DIRECTORY" -name "$FILE" -exec rm -rf {} \;
        echo -e "- Delete ${FILE} : ${VERT}DONE${NORMAL}"
    fi
    if [ -d "${DIRECTORY}/${FILE}" ]; then
        rm -Rf "${DIRECTORY}/${FILE}"
    fi
}

remove_directories(){
    DIRECTORY="$1"
    find "$DIRECTORY" -maxdepth 1 -mindepth 1 -type d -exec rm -rf {} \;
    echo -e "- Delete $FILE : ${VERT}DONE${NORMAL}"
}

# check parameters
if [ -z "$1" ]; then
    echo 'Version parameter is not set'
    echo
    exit 0
else
    VERSION="$1"
    ARCHIVE_NAME="lengow.prestashop.${VERSION}.zip"
fi

# Check parameters
if [ -z "$2" ]; then
    echo 'Deploy environment is not set: preprod or prod'
    echo
    exit 0
fi

if [ ! -z "$2" ] && [ "$2" == "preprod" ]; then
    ARCHIVE_NAME="preprod__${ARCHIVE_NAME}"
fi
echo "ARCHIVE_NAME will be: ${ARCHIVE_NAME}"

# load vars
source vars.sh
# encrypt vars file
source encrypt.sh

# process
echo
echo "#####################################################"
echo "##                                                 ##"
echo -e "##       "${BLEU}Lengow Prestashop${NORMAL}" - Build Module          ##"
echo "##                                                 ##"
echo "#####################################################"
echo
PWD=$(pwd)
FOLDER=$(dirname "${PWD}")
echo "${FOLDER}"
sleep 3

# Change config for preprod
if [ ! -z "${DEPLOY_ENV}" ] && [ "${DEPLOY_ENV}" == "preprod" ]; then
    if [ "$CURRENT_OS" == "MacOS" ]; then
        sed -i '' 's/lengow.io/lengow.net/g' "${FOLDER}/classes/models/LengowConnector.php"
        sed -i '' 's/lengow.local/lengow.net/g' "${FOLDER}/classes/models/LengowConnector.php"
    else
        sed -i 's/lengow.io/lengow.net/g' "${FOLDER}/classes/models/LengowConnector.php"
        sed -i 's/lengow.local/lengow.net/g' "${FOLDER}/classes/models/LengowConnector.php"
    fi
fi

if [ ! -z "${DEPLOY_ENV}" ] && [ "${DEPLOY_ENV}" == "prod" ]; then
    if [ "$CURRENT_OS" == "MacOS" ]; then
        sed -i '' 's/lengow.net/lengow.io/g' "${FOLDER}/classes/models/LengowConnector.php"
        sed -i '' 's/lengow.local/lengow.io/g' "${FOLDER}/classes/models/LengowConnector.php"
    else
        sed -i 's/lengow.net/lengow.io/g' "${FOLDER}/classes/models/LengowConnector.php"
        sed -i 's/lengow.local/lengow.io/g' "${FOLDER}/classes/models/LengowConnector.php"
    fi
fi

# remove TMP FOLDER
if [ -d "${FOLDER_TMP}" ]; then
    rm -Rf "${FOLDER_TMP}"
fi
mkdir "${FOLDER_TMP}"

if [ ! -d "$FOLDER" ]; then
    echo -e "Folder doesn't exist : ${ROUGE}ERROR${NORMAL}"
    echo
    exit 0
fi

if [ "$CURRENT_OS" == "MacOS" ]; then
    PHP=$(realpath "$(which php)")
else
    PHP=$(which php8.1)
fi

echo "php binary is ${PHP}"

sleep 3
# generate translations
"${PHP}" translate.php
echo -e "- Generate translations : ${VERT}DONE${NORMAL}"
# create files checksum
"${PHP}" checkmd5.php
echo -e "- Create files checksum : ${VERT}DONE${NORMAL}"
# remove TMP FOLDER
remove_directory "$FOLDER_TMP"
# copy files
rsync -a --exclude='.DS_Store' "$FOLDER/" "$FOLDER_TMP/"
# remove dod
remove_files "$FOLDER_TMP" "dod.md"
# remove Readme
remove_files "$FOLDER_TMP" "README.md"
# remove .gitignore
remove_files "$FOLDER_TMP" ".gitignore"
# remove php-cs-fixer-cache
remove_files "$FOLDER_TMP" ".php-cs-fixer.cache"
remove_files "$FOLDER_TMP" ".php-cs-fixer.dist.php"
remove_files "$FOLDER_TMP" ".php_cs.cache"
# remove .git
remove_files "$FOLDER_TMP" ".git"
rm -Rf "$FOLDER_TMP/.github"
# remove .DS_Store
remove_files "$FOLDER_TMP" ".DS_Store"
# remove composer.json
remove_files "$FOLDER_TMP" "composer.json"
# remove .AdminLengowHome.gif
remove_files "$FOLDER_TMP" "AdminLengowHome.gif"
sleep 3
# remove .idea
remove_files "$FOLDER_TMP" ".idea"
# remove Jenkinsfile
remove_files "$FOLDER_TMP" "Jenkinsfile"
# clean Config Folder
remove_files "$FOLDER_CONFIG" "marketplaces.json"
# clean Log Folder
rm -Rf $FOLDER_TMP/logs/*.txt
echo -e "- Clean logs folder : ${VERT}DONE${NORMAL}"
# clean export folder
remove_directories "$FOLDER_EXPORT"
echo -e "- Clean export folder : ${VERT}DONE${NORMAL}"
# clean tools folder
remove_directory "$FOLDER_TOOLS"
echo -e "- Remove Tools folder : ${VERT}DONE${NORMAL}"
# remove TMP FOLDER_TRANSLATION
remove_directory "$FOLDER_TRANSLATION"
echo -e "- Remove Translation yml folder : ${VERT}DONE${NORMAL}"
# remove config.xml
find "$FOLDER_TMP" -name "config.xml" -delete
echo -e "- Delete config.xml : ${VERT}DONE${NORMAL}"
# remove config_fr.xml
find "$FOLDER_TMP" -name "config_fr.xml" -delete
echo -e "- Delete config_fr.xml : ${VERT}DONE${NORMAL}"
# remove config_es.xml
find "$FOLDER_TMP" -name "config_es.xml" -delete
echo -e "- Delete config_es.xml : ${VERT}DONE${NORMAL}"
# remove config_it.xml
find "$FOLDER_TMP" -name "config_it.xml" -delete
echo -e "- Delete config_it.xml : ${VERT}DONE${NORMAL}"
sleep 3
# remove todo.txt
find "$FOLDER_TMP" -name "todo.txt" -delete
echo -e "- todo.txt : ${VERT}DONE${NORMAL}"
# add module key
if [ ! -z "${DEPLOY_ENV}" ] && [ "${DEPLOY_ENV}" == "prod" ]; then
    if [ "$CURRENT_OS" == "MacOS" ]; then
        sed -i '' "s/__LENGOW_PRESTASHOP_PRODUCT_KEY__/${MODULE_KEY}/g" "${FOLDER_TMP}/lengow.php"
    else
        sed -i "s/__LENGOW_PRESTASHOP_PRODUCT_KEY__/${MODULE_KEY}/g" ${FOLDER_TMP}/lengow.php
    fi
    echo -e "- Add module key : ${VERT}DONE${NORMAL}"
fi

# make zip
cd /tmp
zip "-r" "$ARCHIVE_NAME" "lengow"
echo -e "- Build archive : ${VERT}DONE${NORMAL}"
if [ -d  "~/Bureau" ]; then
    mv "$ARCHIVE_NAME" ~/Bureau
else
    mv "$ARCHIVE_NAME" ~/shared
fi
sleep 3
echo "End of build."