@echo off
title Stop Vrundavan Shield Agent
echo Stopping Vrundavan Shield Agent...
powershell -NoProfile -ExecutionPolicy Bypass -Command "Get-Process powershell, php -ErrorAction SilentlyContinue | Where-Object { (Get-NetTCPConnection -LocalPort 9988 -ErrorAction SilentlyContinue).OwningProcess -contains $_.Id } | Stop-Process -Force -ErrorAction SilentlyContinue"
echo Agent stopped successfully.
timeout /t 3
