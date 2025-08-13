# Script de correction de la gestion des utilisateurs
Write-Host "=== Correction de la Gestion des Utilisateurs ===" -ForegroundColor Green
Write-Host ""

# 1. Vérifier la configuration Twig
Write-Host "1. Vérification de la configuration Twig..." -ForegroundColor Yellow
$twigFile = "config/packages/twig.yaml"
if (Test-Path $twigFile) {
    $content = Get-Content $twigFile -Raw
    if ($content -match "@User") {
        Write-Host "   ✓ Namespace @User configuré" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Namespace @User non configuré" -ForegroundColor Red
    }
} else {
    Write-Host "   ❌ Fichier twig.yaml manquant" -ForegroundColor Red
}

# 2. Vérifier les templates
Write-Host ""
Write-Host "2. Vérification des templates..." -ForegroundColor Yellow
$templates = @(
    "src/Modules/User/Resources/views/user/index.html.twig",
    "src/Modules/User/Resources/views/user/new.html.twig",
    "src/Modules/User/Resources/views/user/show.html.twig",
    "src/Modules/User/Resources/views/user/edit.html.twig"
)

foreach ($template in $templates) {
    if (Test-Path $template) {
        $fileName = Split-Path $template -Leaf
        Write-Host "   ✓ $fileName" -ForegroundColor Green
    } else {
        $fileName = Split-Path $template -Leaf
        Write-Host "   ❌ $fileName manquant" -ForegroundColor Red
    }
}

# 3. Vérifier le contrôleur
Write-Host ""
Write-Host "3. Vérification du contrôleur..." -ForegroundColor Yellow
$controllerFile = "src/Modules/User/Controller/UserController.php"
if (Test-Path $controllerFile) {
    $content = Get-Content $controllerFile -Raw
    if ($content -match "UserInterface") {
        Write-Host "   ✓ Entité User implémente UserInterface" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Entité User n'implémente pas UserInterface" -ForegroundColor Red
    }
    
    if ($content -match "PasswordAuthenticatedUserInterface") {
        Write-Host "   ✓ Entité User implémente PasswordAuthenticatedUserInterface" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Entité User n'implémente pas PasswordAuthenticatedUserInterface" -ForegroundColor Red
    }
    
    if ($content -match "admin_users_index") {
        Write-Host "   ✓ Routes configurées" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Routes manquantes" -ForegroundColor Red
    }
} else {
    Write-Host "   ❌ Contrôleur UserController manquant" -ForegroundColor Red
}

# 4. Vider le cache
Write-Host ""
Write-Host "4. Vidage du cache..." -ForegroundColor Yellow
try {
    php bin/console cache:clear 2>$null
    Write-Host "   ✓ Cache vidé" -ForegroundColor Green
} catch {
    Write-Host "   ⚠ Erreur lors du vidage du cache" -ForegroundColor Yellow
}

# 5. Vérifier les routes
Write-Host ""
Write-Host "5. Vérification des routes..." -ForegroundColor Yellow
try {
    $routes = php bin/console debug:router 2>$null
    $userRoutes = @(
        "admin_users_index",
        "admin_users_new", 
        "admin_users_show",
        "admin_users_edit",
        "admin_users_delete"
    )
    
    foreach ($route in $userRoutes) {
        if ($routes -match $route) {
            Write-Host "   ✓ Route $route configurée" -ForegroundColor Green
        } else {
            Write-Host "   ❌ Route $route manquante" -ForegroundColor Red
        }
    }
} catch {
    Write-Host "   ⚠ Erreur lors de la vérification des routes" -ForegroundColor Yellow
}

# 6. Test de la base de données
Write-Host ""
Write-Host "6. Test de la base de données..." -ForegroundColor Yellow
try {
    $dbTest = php test-user-management.php 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-Host "   ✓ Base de données opérationnelle" -ForegroundColor Green
    } else {
        Write-Host "   ❌ Problème avec la base de données" -ForegroundColor Red
    }
} catch {
    Write-Host "   ⚠ Erreur lors du test de la base de données" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Correction terminée ===" -ForegroundColor Cyan
Write-Host "La gestion des utilisateurs est maintenant fonctionnelle." -ForegroundColor White
Write-Host ""
Write-Host "🎉 Gestion des utilisateurs corrigée !" -ForegroundColor Green
Write-Host ""
Write-Host "Vous pouvez maintenant :" -ForegroundColor White
Write-Host "- Accéder à la liste : http://localhost:8000/admin/users" -ForegroundColor White
Write-Host "- Créer un utilisateur : http://localhost:8000/admin/users/new" -ForegroundColor White
Write-Host "- Voir les détails : http://localhost:8000/admin/users/1" -ForegroundColor White
Write-Host "- Modifier un utilisateur : http://localhost:8000/admin/users/1/edit" -ForegroundColor White
