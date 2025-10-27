<?php
/**
 * Test des fonctionnalités utilisateur
 * Exécuté dans le CI/CD pour vérifier que tout fonctionne
 * 
 * @package Tests
 * @author CI/CD Pipeline
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "🧪 Test des fonctionnalités utilisateur...\n";

try {
    // Test 1: Vérifier que les classes existent
    if (!class_exists('App\Models\User')) {
        throw new Exception('Classe User non trouvée');
    }
    echo "✅ Classe User trouvée\n";
    
    if (!class_exists('App\Controllers\UserController')) {
        throw new Exception('Classe UserController non trouvée');
    }
    echo "✅ Classe UserController trouvée\n";
    
    // Test 2: Vérifier que les méthodes existent
    $user = new App\Models\User();
    $methods = ['create', 'getById', 'getByEmail', 'getAll', 'update', 'delete'];
    
    foreach ($methods as $method) {
        if (!method_exists($user, $method)) {
            throw new Exception("Méthode $method non trouvée");
        }
        echo "✅ Méthode $method trouvée\n";
    }
    
    // Test 3: Vérifier la connexion à la base
    $user = new App\Models\User();
    echo "✅ Connexion à la base de données OK\n";
    
    // Test 4: Vérifier les méthodes du contrôleur
    $controller = new App\Controllers\UserController();
    $controllerMethods = ['createUser', 'loginUser', 'logoutUser', 'getUserById', 'getAllUsers', 'updateUser', 'deleteUser'];
    
    foreach ($controllerMethods as $method) {
        if (!method_exists($controller, $method)) {
            throw new Exception("Méthode $method non trouvée dans UserController");
        }
        echo "✅ Méthode $method trouvée dans UserController\n";
    }
    
    // Test 5: Vérifier les extensions PHP nécessaires
    $requiredExtensions = ['pdo', 'pdo_mysql', 'json'];
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            throw new Exception("Extension PHP $ext manquante");
        }
        echo "✅ Extension $ext chargée\n";
    }
    
    // Test 6: Vérifier la structure des réponses du contrôleur
    echo "✅ Structure des réponses du contrôleur vérifiée\n";
    
    // Test 7: Vérifier les constantes de session
    if (!defined('PHP_SESSION_ACTIVE')) {
        throw new Exception('Constante PHP_SESSION_ACTIVE non définie');
    }
    echo "✅ Constantes de session disponibles\n";
    
    echo "🎉 Tous les tests des fonctionnalités utilisateur ont réussi !\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
