@echo off
echo Stopping Budget Monitor Server...
taskkill /f /im php.exe > nul 2>&1
taskkill /f /im node.exe > nul 2>&1
wmic process where "name='cmd.exe' and commandline like '%%start_server.bat%%'" call terminate > nul 2>&1
echo Done.
pause
