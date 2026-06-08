@echo off
title Stop Vrundavan Shield Agent
echo Stopping local Vrundavan Shield hardware agent...
taskkill /f /fi "windowtitle eq cmd.exe /c php -S 127.0.0.1:9988*" >nul 2>&1
taskkill /f /im php.exe >nul 2>&1
echo Success! Agent has been stopped.
timeout /t 3 >nul
