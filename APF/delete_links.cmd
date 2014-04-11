@echo off
rem ####################################################################################################################
rem # Automatic deletion of symlinks to the repository clone of the APF code base locally clone'd from                 #
rem # https://github.com/AdventurePHP/code.                                                                            #
rem ####################################################################################################################

rmdir core
rmdir tools
rmdir modules
rmdir extensions
rmdir tests
rmdir migration

echo SUCCESS: Links deleted!
