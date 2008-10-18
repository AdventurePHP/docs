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

# print heading to quicknavi file
echo "<ol>" > $PROJECTPATH/quicknavi/$QUICKNAVI_FILE

# gather heading definitions in the text file
for line in $(grep -E "^<h[1-6]>" "$PROJECTPATH/content/$CONTENT_FILE" | sed -e "s/ /[space]/g")
do
   # display heading
   heading=$(echo $line | sed -e "s/\[space\]/ /g")
   echo "Heading: $heading"

   # get title of the link
   title=$(echo $line | cut -d ">" -f 2 | cut -d "<" -f 1 | sed -e "s/\[space\]/ /g")
   echo "Title: \"$title\""

   # gather link name
   link=$(echo $title | sed -e "s/\//-/g" -e "s/\./-/g" -e "s/ /-/g" -e "s/--/-/g" -e "s/---/-/g" -e "s/&Auml;/Ae/g" -e "s/&Ouml;/Oe/g" -e "s/&Uuml;/Ue/g" -e "s/&auml;/ae/g" -e "s/&ouml;/oe/g" -e "s/&uuml;/ue/g" -e "s/&//g" -e "s/--/-/g")
   echo "Link: \"$link\""

   # create anchor definiton
   anchor="<a name=\"$link\"></a>"
   echo "Anchor: \"$anchor\""

   # replace inline
   heading2=$(echo $heading | sed -e "s/&/%%%%%%/") # headings with a "&" are not replaced, yet!
   sed -e "s|$heading|$anchor$heading2|g" -i $PROJECTPATH/content/$CONTENT_FILE

   # build quicknavi file
   echo "  <li><a href=\"#$link\" title=\"$title\">$title</a></li>" >> $PROJECTPATH/quicknavi/$QUICKNAVI_FILE

   # print some new lines
   echo
   echo
done

# print footer to quicknavi file
echo -n "</ol>" >> $PROJECTPATH/quicknavi/$QUICKNAVI_FILE

# revert the %%%%%% replacement
sed -e "s/%%%%%%/\&/g" -i $PROJECTPATH/content/$CONTENT_FILE