# Script de réinitialisation complète de l'application Symfony
Write-Host "=== Réinitialisation complète de l'application ===" -ForegroundColor Green
Write-Host ""

# 1. Supprimer la base de données
Write-Host "1. Suppression de la base de données..." -ForegroundColor Yellow
C:\xampp\mysql\bin\mysql.exe -u root -e "DROP DATABASE IF EXISTS symfony_app;" 2>$null
Remove-Item "C:\xampp\mysql\data\symfony_app" -Recurse -Force -ErrorAction SilentlyContinue
Write-Host "✓ Base de données supprimée" -ForegroundColor Green

# 2. Supprimer et recréer le fichier de configuration
Write-Host ""
Write-Host "2. Réinitialisation du fichier .env..." -ForegroundColor Yellow
Remove-Item ".env" -Force -ErrorAction SilentlyContinue
Copy-Item "env.example" ".env"
Write-Host "✓ Fichier .env réinitialisé" -ForegroundColor Green

# 3. Supprimer le fichier de verrouillage d'installation
Write-Host ""
Write-Host "3. Suppression du verrouillage d'installation..." -ForegroundColor Yellow
Remove-Item "var\install.lock" -Force -ErrorAction SilentlyContinue
Write-Host "✓ Verrouillage d'installation supprimé" -ForegroundColor Green

# 4. Vider le cache
Write-Host ""
Write-Host "4. Vidage du cache..." -ForegroundColor Yellow
Remove-Item "var\cache\*" -Recurse -Force -ErrorAction SilentlyContinue
Write-Host "✓ Cache vidé" -ForegroundColor Green

# 5. Vider les logs
Write-Host ""
Write-Host "5. Vidage des logs..." -ForegroundColor Yellow
Remove-Item "var\logs\*" -Recurse -Force -ErrorAction SilentlyContinue
Write-Host "✓ Logs vidés" -ForegroundColor Green

# 6. Supprimer les sessions
Write-Host ""
Write-Host "6. Suppression des sessions..." -ForegroundColor Yellow
Remove-Item "var\sessions\*" -Recurse -Force -ErrorAction SilentlyContinue
Write-Host "✓ Sessions supprimées" -ForegroundColor Green

# 7. Supprimer les clés JWT
Write-Host ""
Write-Host "7. Suppression des clés JWT..." -ForegroundColor Yellow
Remove-Item "config\jwt\*" -Recurse -Force -ErrorAction SilentlyContinue
Write-Host "✓ Clés JWT supprimées" -ForegroundColor Green

# 8. Supprimer les uploads
Write-Host ""
Write-Host "8. Suppression des fichiers uploadés..." -ForegroundColor Yellow
Remove-Item "public\uploads\*" -Recurse -Force -ErrorAction SilentlyContinue
Write-Host "✓ Fichiers uploadés supprimés" -ForegroundColor Green

Write-Host ""
Write-Host "=== Réinitialisation terminée ===" -ForegroundColor Cyan
Write-Host "L'application est maintenant en mode installation" -ForegroundColor White
Write-Host ""
Write-Host "Pour accéder à l'installation :" -ForegroundColor Yellow
Write-Host "1. Démarrez le serveur : php -S localhost:8000 -t public" -ForegroundColor White
Write-Host "2. Accédez à : http://localhost:8000/" -ForegroundColor White
Write-Host "3. Vous serez redirigé vers l'assistant d'installation" -ForegroundColor White
Write-Host ""
Write-Host "🎉 Application réinitialisée avec succès !" -ForegroundColor Green
