#!/bin/bash

####################################################################################################################
# Automatic deletion of symlinks to the repository clone of the APF code base locally clone'd from                 #
# https://github.com/AdventurePHP/code.                                                                            #
####################################################################################################################

rm core
rm tools
rm modules
rm extensions
rm tests
rm migration

echo "SUCCESS: Links deleted!"
exit 0

