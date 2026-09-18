@echo off
title Vrundavan Shield Agent (Console Mode)
echo Starting Vrundavan Shield Agent in visible debug mode...
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0agent.ps1"
pause
