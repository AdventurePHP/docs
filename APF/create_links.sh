#!/bin/bash

####################################################################################################################
# Automatic creation of symlinks to the repository clone of the APF code base locally clone'd from                 #
# https://github.com/AdventurePHP/code.                                                                            #
#                                                                                                                  #
# Pass local path of clone to parameter $1.                                                                        #
####################################################################################################################

if [ -z "$1" ]
then
   echo "ERROR: Please provide local repository path containing a clone of https://github.com/AdventurePHP/code!"
   exit 1
fi

ln -s $1/core core
ln -s $1/tools tools
ln -s $1/modules modules
ln -s $1/extensions extensions
ln -s $1/tests tests
ln -s $1/migration migration

echo "SUCCESS: Links created!"
exit 0

