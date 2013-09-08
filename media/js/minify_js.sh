#!/bin/bash
####################################################################################################
# Script to minify the css files into one single shrinked css file to optimize performance.        #
####################################################################################################

BASEPATH=/cygdrive/c/Users/Christian/Entwicklung/Tools//yuicompressor-2.4.2
LIBPATH=$BASEPATH/lib
BUILDPATH=$BASEPATH/build
JSPATH=$(pwd)
TEMPJSFILE="apf-combined.js"
COMBINEDJSFILE="apf.js"
JQUERYJSFILE="jquery-1-9-1-min.js"

# build classpath
CLASSPATH=
for JAR in $(ls $LIBPATH)
do
   if [ ! -z "$CLASSPATH" ]
   then
      CLASSPATH="$CLASSPATH:$(cygpath -m $LIBPATH/$JAR)"
   else
      CLASSPATH=$(cygpath -m $LIBPATH/$JAR)
   fi
done

# create JAR path to avoid cygwin errors (see http://www.cygwin.com/ml/cygwin/2008-01/msg00095.html)
JARPATH=$(cygpath -m $BUILDPATH/yuicompressor-2.4.2.jar)

# create combined css
rm -f $JSPATH/$TEMPJSFILE
cat $JQUERYJSFILE $(ls $JSPATH/*.js | grep -v "$TEMPJSFILE" | grep -v "$COMBINEDJSFILE" | grep -v "$JQUERYJSFILE") > $JSPATH/$TEMPJSFILE

# create shrinked version
INFILE=$(cygpath -m $JSPATH/$TEMPJSFILE)
OUTFILE=$(cygpath -m $JSPATH/$COMBINEDJSFILE)
java -jar $JARPATH -o $OUTFILE $INFILE

rm -f $JSPATH/$TEMPJSFILE