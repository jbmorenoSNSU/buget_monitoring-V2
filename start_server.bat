@echo off
:: Navigate to the directory where this script is located
cd /d %~dp0

:: 0. Robust Early Exit: Only skip the wait if BOTH the Database and the App are confirmed ready.
:: This prevents errors if the app server is running but the database is offline.
php -r "try { new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''); exit(0); } catch (Exception $e) { exit(1); }" > nul 2>&1
if %errorlevel% equ 0 (
    netstat -ano | find "LISTENING" | find ":8000" > nul
    if %errorlevel% equ 0 (
        start chrome --app=http://localhost:8000
        exit
    )
)

:: 1. Wait for WampServer MySQL (Port 3306) to be ready using an actual connection attempt
echo Waiting for WampServer (Database) to be ready...
:CHECK_MYSQL
php -r "try { new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''); exit(0); } catch (Exception $e) { exit(1); }" > nul 2>&1
if %errorlevel% neq 0 (
    timeout /t 2 /nobreak > nul
    goto CHECK_MYSQL
)
echo Database is ready!

:: 2. Check if the Artisan server is already running, if not start it
netstat -ano | find "LISTENING" | find ":8000" > nul
if %errorlevel% neq 0 (
    echo Starting Artisan server...
    start /b php artisan serve --port=8000
)

:: 3. Wait for the Artisan server (Port 8000) itself to be fully active
echo Waiting for App Server to be ready...
:CHECK_APP
netstat -ano | find "LISTENING" | find ":8000" > nul
if %errorlevel% neq 0 (
    timeout /t 1 /nobreak > nul
    goto CHECK_APP
)
echo App Server is ready!

:: 4. Finally open the browser in "App Mode"
start chrome --app=http://localhost:8000
exit
