#!/bin/bash
####################################################################################################
# Script to minify the css files into one single shrinked css file to optimize performance.        #
####################################################################################################

BASEPATH=/cygdrive/c/Users/Christian/Entwicklung/Tools/yuicompressor-2.4.2
LIBPATH=$BASEPATH/lib
BUILDPATH=$BASEPATH/build
CSSPATH=$(pwd)
TEMPCSSFILE="apf-combined.css"
COMBINEDCSSFILE="apf.css"
IE6CSSFILE="lte-ie7.css"
IE7CSSFILE="ie7.css"
PRINTCSSFILE="print.css"

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
rm -f $CSSPATH/$TEMPCSSFILE
cat $(ls $CSSPATH/*.css | grep -v "$TEMPCSSFILE" | grep -v "$COMBINEDCSSFILE" | grep -v "$IE6CSSFILE" | grep -v "$IE7CSSFILE" | grep -v "$PRINTCSSFILE") > $CSSPATH/$TEMPCSSFILE

# create shrinked version
INFILE=$(cygpath -m $CSSPATH/$TEMPCSSFILE)
OUTFILE=$(cygpath -m $CSSPATH/$COMBINEDCSSFILE)
java -jar $JARPATH -o $OUTFILE $INFILE

rm -f $CSSPATH/$TEMPCSSFILE