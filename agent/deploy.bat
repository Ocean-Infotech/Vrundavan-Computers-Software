@echo off
:: =========================================================================
:: Vrundavan Shield Agent - Automated Silent Windows Installer & Daemon Setup
:: Designed for bulk deployment (up to 1000+ PCs) via Active Directory (GPO)
:: or manual network administrator execution.
:: =========================================================================

setlocal enabledelayedexpansion

:: 1. Define Deployment Configuration
:: UPDATE THESE VARIABLES WITH YOUR ACTUAL SERVER PATHS AND BACKEND URL
set "SERVER_SHARE_PATH=\\192.168.1.14\Deployments\VrundavanAgent"
set "LOCAL_INSTALL_DIR=%ProgramData%\VrundavanAgent"
set "SERVICE_NAME=VrundavanShieldAgent"

echo [+] Initiating silent hardware agent deployment...

:: 2. Create local directory silently
if not exist "%LOCAL_INSTALL_DIR%" (
    mkdir "%LOCAL_INSTALL_DIR%" >nul 2>&1
)

:: 3. Test if Server Share is reachable
dir "%SERVER_SHARE_PATH%" >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [!] Server deployment share is unreachable. 
    echo [!] Ensure "%SERVER_SHARE_PATH%" is shared and accessible over LAN.
    exit /b 1
)

:: 4. Copy agent binaries silently (including php subfolder recursively if present)
echo [+] Syncing security binaries from local server...
xcopy /E /I /Y /Q "%SERVER_SHARE_PATH%\*.*" "%LOCAL_INSTALL_DIR%\" >nul 2>&1

:: Determine PHP Executable Path
if exist "%LOCAL_INSTALL_DIR%\php\php.exe" (
    set "PHP_EXE_PATH=%LOCAL_INSTALL_DIR%\php\php.exe"
    echo [+] Detected and configured bundled portable PHP...
) else if exist "C:\xampp\php\php.exe" (
    set "PHP_EXE_PATH=C:\xampp\php\php.exe"
    echo [+] Using system XAMPP PHP...
) else (
    set "PHP_EXE_PATH=php"
    echo [+] Using system PATH PHP...
)

:: 5. Create a VBScript helper to execute PHP server completely hidden (No console window)
echo [+] Installing silent background startup runner...
set "VBS_RUNNER=%LOCAL_INSTALL_DIR%\run_silent.vbs"
(
    echo Set WshShell = CreateObject^("WScript.Shell"^)
    echo WshShell.Run """%PHP_EXE_PATH%"" -S 127.0.0.1:9988 -t """ & "%LOCAL_INSTALL_DIR%" & """ """ & "%LOCAL_INSTALL_DIR%\router.php" & """", 0, False
) > "%VBS_RUNNER%"

:: 6. Setup automatic silent startup in Windows Registry for all users
echo [+] Configuring automatic system startup...
reg add "HKLM\Software\Microsoft\Windows\CurrentVersion\Run" /v "%SERVICE_NAME%" /t REG_SZ /d "wscript.exe \"%VBS_RUNNER%\"" /f >nul 2>&1

if %ERRORLEVEL% neq 0 (
    :: Fallback to Current User registry if not running as Administrator
    reg add "HKCU\Software\Microsoft\Windows\CurrentVersion\Run" /v "%SERVICE_NAME%" /t REG_SZ /d "wscript.exe \"%VBS_RUNNER%\"" /f >nul 2>&1
)

:: 7. Execute the silent agent immediately in the background
echo [+] Triggering silent service background startup...
wscript.exe "%VBS_RUNNER%" >nul 2>&1

echo [SUCCESS] Vrundavan Shield local agent is now installed and running silently in the background!
echo [SUCCESS] No CMD windows will be shown, and the agent will auto-start with Windows.
endlocal
exit /b 0
