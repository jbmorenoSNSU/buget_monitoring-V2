Set WshShell = CreateObject("WScript.Shell")
Set objWMIService = GetObject("winmgmts:\\.\root\cimv2")

' Check if WampServer is already running to avoid lag/double-launch
Set colProcessList = objWMIService.ExecQuery("Select * from Win32_Process Where Name = 'wampmanager.exe'")

If colProcessList.Count = 0 Then
    ' Start WampServer ONLY if not already running (Requires elevation)
    Set objShell = CreateObject("Shell.Application")
    objShell.ShellExecute "C:\wamp64\wampmanager.exe", "", "", "runas", 1
End If

' Get the folder containing this VBScript to run start_server.bat using its absolute path
Set fso = CreateObject("Scripting.FileSystemObject")
scriptDir = fso.GetParentFolderName(WScript.ScriptFullName)

' Run the batch file hidden (0)
WshShell.Run chr(34) & scriptDir & "\start_server.bat" & Chr(34), 0
Set WshShell = Nothing
