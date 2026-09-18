Set WshShell = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")
currentDir = fso.GetParentFolderName(WScript.ScriptFullName)
WshShell.CurrentDirectory = currentDir

' Stop any old processes on port 9988 first
WshShell.Run "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -Command ""Get-Process powershell, php -ErrorAction SilentlyContinue | Where-Object { (Get-NetTCPConnection -LocalPort 9988 -ErrorAction SilentlyContinue).OwningProcess -contains $_.Id } | Stop-Process -Force -ErrorAction SilentlyContinue""", 0, True

' Run the Native PowerShell Agent in hidden mode
WshShell.Run "powershell.exe -NoProfile -ExecutionPolicy Bypass -WindowStyle Hidden -File """ & currentDir & "\agent.ps1""", 0, False
