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
FOLDER_TMP="/tmp/presta"
FOLDER_LOGS="/tmp/presta/logs"
FOLDER_EXPORT="/tmp/presta/export"


FOLDER_MODULE="lengow"

FOLDER_OVERRIDE="/lengow/override"
FOLDER_INSTALL="/lengow/install"
FOLDER_GIT="/lengow/.git"
FOLDER_IDEA="/lengow/.idea"
FILE_MARKETPLACES="/marketplaces.xml"
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
# Remove .gitignore
remove_files $FOLDER_TMP ".gitignore"
# Remove .git
remove_files $FOLDER_TMP ".git"
# Remove .DS_Store
remove_files $FOLDER_TMP ".DS_Store"
# Clean Log Folder
remove_files $FOLDER_LOGS "*.txt"

exit
# Clean export folder
remove_files $FOLDER_EXPORT "*"



echo "- Clean export folder : ""$VERT""DONE""$NORMAL"""
# Remove config_fr.xml
find $FOLDER -name "config_fr.xml" -delete
echo "- Delete config_fr.xml : ""$VERT""DONE""$NORMAL"""
# Clean logs folder
find $FOLDER$FOLDER_LOGS -name '*.txt' -delete
echo "- Clean logs folder : ""$VERT""DONE""$NORMAL"""
#move override files to install folder
if ! find $FOLDER$FOLDER_INSTALL
	then mkdir $FOLDER$FOLDER_INSTALL
	echo "- Creating Install folder : ""$VERT""DONE""$NORMAL"""
fi
if find $FOLDER$FOLDER_OVERRIDE
	then mv $FOLDER$FOLDER_OVERRIDE"/"* $FOLDER$FOLDER_INSTALL
		rmdir $FOLDER$FOLDER_OVERRIDE
		echo "- Removing override folder : ""$VERT""DONE""$NORMAL"""
fi
echo "- Moved override files to Install folder : ""$VERT""DONE""$NORMAL"""
# Make zip
cd $FOLDER
zip '-r' $ARCHIVE_NAME $FOLDER_MODULE
echo "- Build archive : ""$VERT""DONE""$NORMAL"""
echo