@echo off

rem *************************************************************
rem ** Lime CLI for Windows based systems (based on symfony.bat)
rem *************************************************************

rem This script will do the following:
rem - check for PHP_COMMAND env, if found, use it.
rem   - if not found detect php, if found use it, otherwise err and terminate

if "%OS%"=="Windows_NT" @setlocal

rem Adjust the following path to point to your Lime directory!
set SCRIPT_DIR=C:\Program Files\PHP\Lime2

if "%PHP_COMMAND%" == "" goto no_phpcommand

:init
%PHP_COMMAND% "%SCRIPT_DIR%\lime" %*
goto cleanup

:no_phpcommand
rem echo ------------------------------------------------------------------------
rem echo WARNING: Set environment var PHP_COMMAND to the location of your php.exe
rem echo          executable (e.g. C:\PHP\php.exe).  (assuming php.exe on PATH)
rem echo ------------------------------------------------------------------------
set PHP_COMMAND=php.exe
goto init

:cleanup
if "%OS%"=="Windows_NT" @endlocal
rem pause