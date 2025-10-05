@echo off
echo ================================================
echo Vehicle Management System - Database Setup
echo ================================================
echo.
echo ERROR: PostgreSQL command-line tools (psql) not found!
echo.
echo ================================================
echo ALTERNATIVE SETUP METHOD (RECOMMENDED)
echo ================================================
echo.
echo Since psql is not installed, please use the web-based setup:
echo.
echo 1. Open your web browser
echo 2. Go to: https://www.app.adzdiag.co.uk/setup_vehicle_database.php
echo 3. Log in with admin credentials
echo 4. The setup will run automatically
echo.
echo ================================================
echo MANUAL SETUP METHOD (ALTERNATIVE)
echo ================================================
echo.
echo If you prefer, you can manually run the SQL:
echo.
echo 1. Open pgAdmin or your PostgreSQL client
echo 2. Connect to database 'mydb' at 217.154.59.146
echo 3. Open and execute the file: vehicle_schema.sql
echo.
echo ================================================
echo.
echo Press any key to open setup_vehicle_database.php in browser...
pause > nul

echo.
echo Opening web browser...
start https://www.app.adzdiag.co.uk/setup_vehicle_database.php

echo.
echo Browser opened! Follow the instructions on the web page.
echo.
pause
