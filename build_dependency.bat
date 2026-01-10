@echo off
setlocal
cd /d "%~dp0"

echo ===========================================
echo       Omniwise Environment Builder
echo ===========================================

:: 1. Setup Environment File
if not exist .env (
    echo [.env] Creating from .env.example...
    copy .env.example .env
    echo [.env] Generating Application Key...
    call php artisan key:generate
) else (
    echo [.env] File already exists. Skipping creation.
)

:: 2. Install Dependencies
echo.
echo [Dependencies] Checking Composer...
if not exist vendor (
    call composer install
) else (
    echo [Dependencies] Vendor folder exists. Skipping install.
)

echo [Dependencies] Checking Node Modules...
if not exist node_modules (
    call npm install
) else (
    echo [Dependencies] node_modules folder exists. Skipping install.
)

:: 3. Database Setup
echo.
echo [Database] Initializing...

:: Try to create the database if it doesn't exist.
:: Assumes XAMPP default path relative to this project (../../mysql/bin/mysql.exe)
:: and default root user with no password.
if exist "..\..\mysql\bin\mysql.exe" (
    echo [Database] Creating 'omniwise' database if missing...
    "..\..\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS omniwise;"
) else (
    echo [WARNING] Could not find MySQL executable at ..\..\mysql\bin\mysql.exe
    echo           Please ensure the 'omniwise' database exists manually.
)

echo [Database] Running Migrations and Seeds...
:: Use --force to run in production mode if needed, though this is likely dev.
call php artisan migrate:fresh --seed

:: 4. Configure XAMPP Auto-Start
echo.
echo [Auto-Start] Configuring XAMPP to start on login...

set "startup_folder=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup"
set "shortcut_name=Start_XAMPP.lnk"
set "shortcut_path=%startup_folder%\%shortcut_name%"

:: Target: c:\xampp\xampp_start.exe (Assumed standard path)
:: We resolve the relative path to absolute for the shortcut
pushd ..\..\
set "xampp_root=%CD%"
popd
set "target_exe=%xampp_root%\xampp_start.exe"

if exist "%target_exe%" (
    echo [Auto-Start] Creating shortcut to %target_exe% in Startup folder...
    
    powershell -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut('%shortcut_path%'); $s.TargetPath = '%target_exe%'; $s.Save()"
    
    echo [Auto-Start] Shortcut created successfully.
) else (
    echo [WARNING] Could not find %target_exe%. Auto-start configuration skipped.
)

echo.
echo ===========================================
echo       Build Complete!
echo ===========================================
echo.
pause
