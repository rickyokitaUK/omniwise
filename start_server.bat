@echo off
cd /d "%~dp0"

echo Checking dependencies...

if not exist "vendor" (
    echo Installing PHP dependencies...
    call composer install
)

if not exist "node_modules" (
    echo Installing Node dependencies...
    call npm install
)

echo Starting Omniwise Server...
echo The application will open in your browser shortly.

:: Start the server in a way that we can continue to the next command
:: Using start to open the browser in a separate window after a delay
start "" /B cmd /c "timeout /t 10 >nul & start http://localhost:8000"

:: Start the development server
call composer run dev

pause
