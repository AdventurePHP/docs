@echo off
rem ####################################################################################################################
rem # Automatic creation of symlinks to the repository clone of the APF code base locally clone'd from                 #
rem # https://github.com/AdventurePHP/code.                                                                            #
rem #                                                                                                                  #
rem # Pass local path of clone to parameter %1.                                                                        #
rem ####################################################################################################################

IF [%1] EQU [] GOTO error

mklink /J core %1\core
mklink /J tools %1\tools
mklink /J modules %1\modules
mklink /J extensions %1\extensions
mklink /J tests %1\tests
mklink /J migration %1\migration

goto success

rem ####################################################################################################################
:error
echo ERROR: Please provide local repository path containing a clone of https://github.com/AdventurePHP/code!
GOTO end

rem ####################################################################################################################
:success
echo SUCCESS: Links created!

rem ####################################################################################################################
:end
