#!/bin/bash
# Build archive for Prestashop module
# Step :
#     - Remove .DS_Store
#     - Clean export folder
#     - Clean logs folder
#     - Remove .gitFolder and .gitignore
#     - Update marketplaces.xml


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
    find $DIRECTORY -name $FILE -nowarn -exec rm -f {} \;
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
FOLDER_TEST="/tmp/lengow/test"


FOLDER_MODULE="lengow"

FOLDER_OVERRIDE="/lengow/override"
FOLDER_INSTALL="/lengow/install"
FOLDER_GIT="/lengow/.git"
FOLDER_IDEA="/lengow/.idea"

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

#remove TMP FOLDER
remove_directory $FOLDER_TMP
#copy files
cp -rRp $FOLDER $FOLDER_TMP
# Remove Readme
remove_files $FOLDER_TMP "README.md"
# Remove .gitignore
remove_files $FOLDER_TMP ".gitignore"
# Remove .git
remove_files $FOLDER_TMP ".git"
# Remove .DS_Store
remove_files $FOLDER_TMP ".DS_Store"
# Clean Log Folder
remove_files $FOLDER_LOGS "*.txt"
echo "- Clean logs folder : ""$VERT""DONE""$NORMAL"""
#remove Idea Folder
remove_directory $FOLDER_IDEA
echo "- Remove Idea folder : ""$VERT""DONE""$NORMAL"""
# Clean export folder
remove_directories $FOLDER_EXPORT
echo "- Clean export folder : ""$VERT""DONE""$NORMAL"""
# Remove Test folder
remove_directory $FOLDER_TEST
echo "- Remove Test folder : ""$VERT""DONE""$NORMAL"""
# Remove config_fr.xml
find $FOLDER_TMP -name "config_fr.xml" -delete
echo "- Delete config_fr.xml : ""$VERT""DONE""$NORMAL"""
# Remove todo.txt
find $FOLDER_TMP -name "todo.txt" -delete
echo "- todo.txt : ""$VERT""DONE""$NORMAL"""
# Make zip
cd /tmp
zip "-r" $ARCHIVE_NAME "lengow"
echo "- Build archive : ""$VERT""DONE""$NORMAL"""
mv $ARCHIVE_NAME ~/Bureau