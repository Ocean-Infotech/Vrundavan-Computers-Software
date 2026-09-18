@echo off
title Vrundavan Shield Agent Installer
echo ===================================================
echo   Vrundavan Shield - Device Security Agent Setup
echo ===================================================
echo.
echo [1/3] Stopping any previous agent instances...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-Process powershell, php -ErrorAction SilentlyContinue | Where-Object { (Get-NetTCPConnection -LocalPort 9988 -ErrorAction SilentlyContinue).OwningProcess -contains $_.Id } | Stop-Process -Force -ErrorAction SilentlyContinue"

echo [2/3] Installing Shield Agent to AppData...
set "INSTALL_DIR=%APPDATA%\VrundavanAgent"
if not exist "%INSTALL_DIR%" (
    mkdir "%INSTALL_DIR%" >nul 2>&1
)

xcopy /E /I /Y /Q "%~dp0*.*" "%INSTALL_DIR%\" >nul 2>&1

echo [3/3] Setting up automatic Windows Startup...
set "STARTUP_VBS=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\vrundavan_shield_agent.vbs"

(
    echo Set WshShell = CreateObject^("WScript.Shell"^)
    echo WshShell.Run "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File """ ^& "%INSTALL_DIR%\agent.ps1""" , 0, False
) > "%STARTUP_VBS%"

echo.
echo Starting Vrundavan Shield Agent in the background...
wscript "%STARTUP_VBS%"

echo.
echo ===================================================
echo [SUCCESS] Vrundavan Shield Agent is now ACTIVE!
echo Auto-start is enabled. The agent will run in the
echo background whenever this computer turns on.
echo ===================================================
echo.
echo You can now refresh the CRM Login page in your browser.
echo.
pause
