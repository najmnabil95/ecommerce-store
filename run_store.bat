@echo off
title Elite E-Commerce Store (Laravel & MariaDB)
cd /d "%~dp0\laravel"
set PATH=C:\tools\php;C:\xampp\mysql\bin;%PATH%

echo ============================================================
echo   Elite E-Commerce Store - Powered by Laravel & MariaDB
echo ============================================================

:: Check if MariaDB/MySQL is running on port 3306
netstat -ano | findstr :3306 >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo [INFO] Starting MariaDB service from XAMPP...
    if exist "C:\xampp\mysql\bin\mysqld.exe" (
        start "" /B "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
        timeout /t 3 /nobreak >nul
    ) else (
        echo [WARNING] C:\xampp\mysql\bin\mysqld.exe not found. Please ensure MariaDB is started.
    )
) else (
    echo [OK] MariaDB is active on port 3306.
)

echo Starting Laravel Server at http://127.0.0.1:8000 ...
start http://127.0.0.1:8000
php artisan serve --host=127.0.0.1 --port=8000
pause

