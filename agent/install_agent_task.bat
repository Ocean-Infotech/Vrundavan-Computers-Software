@echo off
title Configure Vrundavan Shield Agent Task Scheduler
echo ---------------------------------------------------
echo Configuring Windows Task Scheduler for Local Agent...
echo ---------------------------------------------------
echo.

set "INSTALL_DIR=%APPDATA%\VrundavanAgent"
if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%"
xcopy /E /I /Y /Q "%~dp0*.*" "%INSTALL_DIR%\" >nul 2>&1

:: Define XML configuration path
set "xml_path=%temp%\vrundavan_agent_task.xml"

(
echo ^<?xml version="1.0" encoding="UTF-16"?^>
echo ^<Task version="1.2" xmlns="http://schemas.microsoft.com/windows/2004/02/mit/task"^>
echo   ^<RegistrationInfo^>
echo     ^<Description^>Vrundavan Shield Hardware Agent^</Description^>
echo   ^</RegistrationInfo^>
echo   ^<Triggers^>
echo     ^<LogonTrigger^>
echo       ^<Enabled^>true^</Enabled^>
echo     ^</LogonTrigger^>
echo   ^</Triggers^>
echo   ^<Settings^>
echo     ^<MultipleInstancesPolicy^>IgnoreNew^</MultipleInstancesPolicy^>
echo     ^<DisallowStartIfOnBatteries^>false^</DisallowStartIfOnBatteries^>
echo     ^<StopIfGoingOnBatteries^>false^</StopIfGoingOnBatteries^>
echo     ^<AllowHardTerminate^>true^</AllowHardTerminate^>
echo     ^<StartWhenAvailable^>true^</StartWhenAvailable^>
echo     ^<RunOnlyIfNetworkAvailable^>false^</RunOnlyIfNetworkAvailable^>
echo     ^<IdleSettings^>
echo       ^<StopOnIdleEnd^>false^</StopOnIdleEnd^>
echo       ^<RestartOnIdle^>false^</RestartOnIdle^>
echo     ^</IdleSettings^>
echo     ^<Enabled^>true^</Enabled^>
echo     ^<Hidden^>true^</Hidden^>
echo     ^<RunOnlyIfIdle^>false^</RunOnlyIfIdle^>
echo     ^<WakeToRun^>false^</WakeToRun^>
echo     ^<ExecutionTimeLimit^>PT0S^</ExecutionTimeLimit^>
echo     ^<Priority^>7^</Priority^>
echo   ^</Settings^>
echo   ^<Principals^>
echo     ^<Principal id="Author"^>
echo       ^<LogonType^>InteractiveToken^</LogonType^>
echo       ^<RunLevel^>HighestAvailable^</RunLevel^>
echo     ^</Principal^>
echo   ^</Principals^>
echo   ^<Actions Context="Author"^>
echo     ^<Exec^>
echo       ^<Command^>wscript.exe^</Command^>
echo       ^<Arguments^>"%INSTALL_DIR%\run_silent.vbs"^</Arguments^>
echo       ^<WorkingDirectory^>%INSTALL_DIR%^</WorkingDirectory^>
echo     ^</Exec^>
echo   ^</Actions^>
echo ^</Task^>
) > "%xml_path%"

:: Register the task with Windows Task Scheduler
schtasks /create /xml "%xml_path%" /tn "VrundavanShieldAgent" /f >nul 2>&1

if %errorlevel% equ 0 (
    echo [SUCCESS] Windows Task Scheduler successfully configured!
    echo The hardware agent is now permanently set to run hidden in the background on logon.
    echo Even if the PC restarts unlimited times, the agent will stay online forever!
    echo.
    echo Starting the agent task right now...
    schtasks /run /tn "VrundavanShieldAgent" >nul 2>&1
    echo Done!
) else (
    echo [ERROR] Permission Denied.
    echo.
    echo PLEASE RIGHT-CLICK "%~nx0" AND SELECT "RUN AS ADMINISTRATOR".
)

:: Clean up temporary XML
del "%xml_path%" >nul 2>&1
echo.
pause
