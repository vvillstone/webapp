@echo off
echo ========================================
echo    Demarrage du serveur XAMPP
echo ========================================
echo.

echo 1. Demarrage d'Apache...
net start Apache2.4
if %errorlevel% neq 0 (
    echo Apache n'est pas demarre. Veuillez le demarrer manuellement dans XAMPP Control Panel.
) else (
    echo ✓ Apache demarre avec succes
)

echo.
echo 2. Demarrage de MySQL...
net start MySQL80
if %errorlevel% neq 0 (
    echo MySQL n'est pas demarre. Veuillez le demarrer manuellement dans XAMPP Control Panel.
) else (
    echo ✓ MySQL demarre avec succes
)

echo.
echo 3. Test de la configuration...
php test-xampp-config.php

echo.
echo ========================================
echo    URLs importantes:
echo ========================================
echo Application: http://localhost/
echo phpMyAdmin:  http://localhost/phpmyadmin/
echo XAMPP Panel: http://localhost/xampp/
echo.
echo Appuyez sur une touche pour continuer...
pause > nul

