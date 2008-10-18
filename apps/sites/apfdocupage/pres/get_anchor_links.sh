#!/bin/bash

if [ -z $1 ]
then
   echo "No file name was present for argument one!"
   exit
fi

# define paths and files
PROJECTPATH=/cygdrive/d/Apache2/htdocs/www/apfdocupage/apps/sites/apfdocupage/pres
CONTENT_FILE=$1
QUICKNAVI_FILE=$(echo $CONTENT_FILE | sed -e "s/c_/n_/")

# build quicknavi file
echo "<ol>" > $PROJECTPATH/quicknavi/$QUICKNAVI_FILE
grep -iE "^<a name=" $PROJECTPATH/content/$CONTENT_FILE | cut -d \" -f 2 | awk '{ link = $1; title = $1; gsub("-",".",title); print "  <li><a href=\"#" link "\" title=\"" title "\">" title "</a></li>"; }' >> $PROJECTPATH/quicknavi/$QUICKNAVI_FILE
echo -n "</ol>" >> $PROJECTPATH/quicknavi/$QUICKNAVI_FILE