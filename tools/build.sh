#!/bin/bash
# Build archive for Prestashop module
# Step :
#     - Remove .DS_Store
#     - Remove .README.md
#     - Remove .idea
#     - Clean export folder
#     - Clean logs folder
#     - Clean translation folder
#     - Remove tools folder
#     - Remove tests folder
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
    find $DIRECTORY -name $FILE -nowarn -exec rm -rf {} \;
    echo "- Delete $FILE : ""$VERT""DONE""$NORMAL"""
}

remove_directories(){
    DIRECTORY=$1
    find $DIRECTORY -maxdepth 1 -mindepth 1 -type d -exec rm -rf {} \;
    echo "- Delete $FILE : ""$VERT""DONE""$NORMAL"""
}
# Check parameters
if [ -z "$1" ]; then
	echo 'Version parameter is not set'
	echo
	exit 0
else
	VERSION="$1"
	ARCHIVE_NAME='lengow.'$VERSION'.zip'
fi

# Variables
FOLDER_TMP="/tmp/lengow"
FOLDER_LOGS="/tmp/lengow/logs"
FOLDER_EXPORT="/tmp/lengow/export"
FOLDER_TEST="/tmp/lengow/tests"
FOLDER_TOOLS="/tmp/lengow/tools"
FOLDER_TRANSLATION="/tmp/lengow/translations/yml"

VERT="\\033[1;32m"
ROUGE="\\033[1;31m"
NORMAL="\\033[0;39m"
BLEU="\\033[1;36m"

# Process
echo
echo "#####################################################"
echo "##                                                 ##"
echo "##       ""$BLEU""Lengow Prestashop""$NORMAL"" - Build Module          ##"
echo "##                                                 ##"
echo "#####################################################"
echo
FOLDER="$(dirname "$(pwd)")"
echo $FOLDER
if [ ! -d "$FOLDER" ]; then
	echo "Folder doesn't exist : ""$ROUGE""ERROR""$NORMAL"""
	echo
	exit 0
fi

# Generate translations
php translate.php
echo "- Generate translations : ""$VERT""DONE""$NORMAL"""
# Create files checksum
php checkmd5.php
echo "- Create files checksum : ""$VERT""DONE""$NORMAL"""
#remove TMP FOLDER
remove_directory $FOLDER_TMP
#copy files
cp -rRp $FOLDER $FOLDER_TMP
# Remove dod
remove_files $FOLDER_TMP "dod.md"
# Remove Readme
remove_files $FOLDER_TMP "README.md"
# Remove .gitignore
remove_files $FOLDER_TMP ".gitignore"
# Remove .git
remove_files $FOLDER_TMP ".git"
# Remove .DS_Store
remove_files $FOLDER_TMP ".DS_Store"
# Remove .AdminLengowHome.gif
remove_files $FOLDER_TMP "AdminLengowHome.gif"
# Remove .idea
remove_files $FOLDER_TMP ".idea"
# Clean Log Folder
remove_files $FOLDER_LOGS "*.txt"
echo "- Clean logs folder : ""$VERT""DONE""$NORMAL"""
# Clean export folder
remove_directories $FOLDER_EXPORT
echo "- Clean export folder : ""$VERT""DONE""$NORMAL"""
# Clean tools folder
remove_directory $FOLDER_TOOLS
echo "- Remove Tools folder : ""$VERT""DONE""$NORMAL"""
# Remove Test folder
remove_directory $FOLDER_TEST
echo "- Remove Test folder : ""$VERT""DONE""$NORMAL"""
#remove TMP FOLDER_TRANSLATION
remove_directory $FOLDER_TRANSLATION
echo "- Remove Translation yml folder : ""$VERT""DONE""$NORMAL"""
# Remove config.xml
find $FOLDER_TMP -name "config.xml" -delete
echo "- Delete config.xml : ""$VERT""DONE""$NORMAL"""
# Remove config_fr.xml
find $FOLDER_TMP -name "config_fr.xml" -delete
echo "- Delete config_fr.xml : ""$VERT""DONE""$NORMAL"""
# Remove config_es.xml
find $FOLDER_TMP -name "config_es.xml" -delete
echo "- Delete config_es.xml : ""$VERT""DONE""$NORMAL"""
# Remove config_it.xml
find $FOLDER_TMP -name "config_it.xml" -delete
echo "- Delete config_it.xml : ""$VERT""DONE""$NORMAL"""
# Remove todo.txt
find $FOLDER_TMP -name "todo.txt" -delete
echo "- todo.txt : ""$VERT""DONE""$NORMAL"""
# Make zip
cd /tmp
zip "-r" $ARCHIVE_NAME "lengow"
echo "- Build archive : ""$VERT""DONE""$NORMAL"""
mv $ARCHIVE_NAME ~/Bureau