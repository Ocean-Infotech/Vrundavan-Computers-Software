@echo off
title Vrundavan Shield Agent Installer
echo ===================================================
echo Vrundavan Shield - Device Binding Agent Installer
echo ===================================================
echo.
echo Installing local hardware agent to your PC...

set "INSTALL_DIR=%APPDATA%\VrundavanAgent"
if not exist "%INSTALL_DIR%" (
    mkdir "%INSTALL_DIR%" >nul 2>&1
)

:: Copy all unzipped agent files recursively to the user's AppData directory
xcopy /E /I /Y /Q "%~dp0*.*" "%INSTALL_DIR%\" >nul 2>&1

:: Create the automatic startup launcher in the Windows Startup folder
set "STARTUP_VBS=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\vrundavan_shield_agent.vbs"

(
    echo Set WshShell = CreateObject^("WScript.Shell"^)
    echo WshShell.CurrentDirectory = "%INSTALL_DIR%"
    echo WshShell.Run "wscript.exe """ ^& "%INSTALL_DIR%\run_silent.vbs" ^& """", 0, False
) > "%STARTUP_VBS%"

echo [SUCCESS] Agent installed and configured for Windows Auto-Start!
echo Even if the PC restarts unlimited times, the agent will stay online in the background.
echo.
echo Launching the agent in the background for this session...
wscript "%INSTALL_DIR%\run_silent.vbs"

echo.
echo [DONE] You can close this terminal now.
echo.
pause
