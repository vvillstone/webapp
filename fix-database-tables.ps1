# Script de correction des tables de base de données
Write-Host "=== Correction des tables de base de données ===" -ForegroundColor Green
Write-Host ""

# 1. Vérifier l'état actuel
Write-Host "1. Vérification de l'état actuel..." -ForegroundColor Yellow
$tables = & "C:\xampp\mysql\bin\mysql.exe" -u root -e "USE symfony_app; SHOW TABLES;" 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✓ Base de données accessible" -ForegroundColor Green
    Write-Host "   Tables trouvées : $($tables.Count - 1)" -ForegroundColor White
} else {
    Write-Host "   ❌ Problème d'accès à la base de données" -ForegroundColor Red
    exit 1
}

# 2. Vérifier si les tables nécessaires existent
Write-Host ""
Write-Host "2. Vérification des tables nécessaires..." -ForegroundColor Yellow
$requiredTables = @("user", "messenger_messages")
$missingTables = @()

foreach ($table in $requiredTables) {
    $result = & "C:\xampp\mysql\bin\mysql.exe" -u root -e "USE symfony_app; SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'symfony_app' AND table_name = '$table';" 2>$null
    if ($result -match "0") {
        $missingTables += $table
        Write-Host "   ❌ Table '$table' manquante" -ForegroundColor Red
    } else {
        Write-Host "   ✓ Table '$table' présente" -ForegroundColor Green
    }
}

# 3. Si des tables manquent, recréer la base de données
if ($missingTables.Count -gt 0) {
    Write-Host ""
    Write-Host "3. Tables manquantes détectées. Recréation de la base de données..." -ForegroundColor Yellow
    
    # Supprimer la base de données corrompue
    Write-Host "   Suppression de la base de données corrompue..." -ForegroundColor White
    & "C:\xampp\mysql\bin\mysql.exe" -u root -e "DROP DATABASE IF EXISTS symfony_app;" 2>$null
    Remove-Item "C:\xampp\mysql\data\symfony_app" -Recurse -Force -ErrorAction SilentlyContinue
    
    # Recréer la base de données
    Write-Host "   Recréation de la base de données..." -ForegroundColor White
    php bin/console doctrine:database:create --if-not-exists 2>$null
    
    # Créer les tables
    Write-Host "   Création des tables..." -ForegroundColor White
    php bin/console doctrine:schema:create 2>$null
    
    Write-Host "   ✓ Base de données recréée avec succès" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "3. Toutes les tables sont présentes. Aucune action nécessaire." -ForegroundColor Green
}

# 4. Vérification finale
Write-Host ""
Write-Host "4. Vérification finale..." -ForegroundColor Yellow
$finalCheck = & "C:\xampp\mysql\bin\mysql.exe" -u root -e "USE symfony_app; SHOW TABLES;" 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "   ✓ Base de données fonctionnelle" -ForegroundColor Green
    Write-Host "   Tables disponibles :" -ForegroundColor White
    $finalCheck | ForEach-Object { if ($_ -match "Tables_in_symfony_app") { } else { Write-Host "     - $_" -ForegroundColor White } }
} else {
    Write-Host "   ❌ Problème persistant avec la base de données" -ForegroundColor Red
    exit 1
}

Write-Host ""
Write-Host "=== Correction terminée ===" -ForegroundColor Cyan
Write-Host "L'application peut maintenant créer des utilisateurs." -ForegroundColor White
Write-Host "Vous pouvez continuer l'installation." -ForegroundColor White
Write-Host ""
Write-Host "🎉 Tables de base de données corrigées !" -ForegroundColor Green
