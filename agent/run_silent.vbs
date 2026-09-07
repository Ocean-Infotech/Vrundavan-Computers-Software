Set WshShell = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")
currentDir = fso.GetParentFolderName(WScript.ScriptFullName)
WshShell.CurrentDirectory = currentDir

' Use local portable PHP executable if bundled, otherwise fallback to system path or XAMPP
Dim phpPath
If fso.FileExists(currentDir & "\php\php.exe") Then
    phpPath = """" & currentDir & "\php\php.exe"""
ElseIf fso.FileExists("D:\xampp\php\php.exe") Then
    phpPath = "D:\xampp\php\php.exe"
ElseIf fso.FileExists("C:\xampp\php\php.exe") Then
    phpPath = "C:\xampp\php\php.exe"
Else
    phpPath = "php"
End If

WshShell.Run "cmd.exe /c " & phpPath & " -S 127.0.0.1:9988 -t """ & currentDir & """ """ & currentDir & "\router.php" & """", 0, False
