@echo off
:: Navigate to the directory where this script is located
cd /d %~dp0

:START_SEQUENCE

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

:: 2.1 Run database migrations silently
echo Checking for database updates...
php artisan migrate --force > nul 2>&1

:: 2.5 Check if Queue Worker is running, if not start it
where wmic >nul 2>nul
if %errorlevel% equ 0 (
    wmic process where "commandline like '%%queue:work%%' and name='php.exe'" get processid ^| findstr [0-9] > nul
    if errorlevel 1 (
        echo Starting Queue Worker...
        start /b php artisan queue:work
    )
) else (
    echo wmic not found, Starting Queue Worker...
    start /b php artisan queue:work
)

:: 2.6 Run Recurring Transactions Generator
echo Running Recurring Transactions...
start /b php artisan recurring:generate

:: 2.7 Check if Vite Dev Server is running, if not start it
netstat -ano | find "LISTENING" | find ":5173" > nul
if %errorlevel% neq 0 (
    echo Starting Vite Dev Server...
    start /b npm run dev
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

:: 3.5 Wait for Vite Dev Server (Port 5173) to be fully active
echo Waiting for Vite Dev Server to be ready...
:CHECK_VITE
netstat -ano | find "LISTENING" | find ":5173" > nul
if %errorlevel% neq 0 (
    timeout /t 1 /nobreak > nul
    goto CHECK_VITE
)
echo Vite Dev Server is ready!

:: 4. Finally open the browser in "App Mode"
start chrome --app=http://localhost:8000

:: 5. Keep the hidden console alive so background processes don't terminate
:: Use stop_server.bat to kill the processes
pause > nul
