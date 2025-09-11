<?php
namespace App\Config;

// Enregistre l'autoloader
spl_autoload_register(function ($class) {
    // Convertit le namespace en chemin de fichier
    $prefix = 'App\\';
    $base_dir = ROOT_PATH . '/app/';

    // Vérifie si la classe utilise le préfixe du namespace
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Récupère le chemin relatif de la classe
    $relative_class = substr($class, $len);

    // Remplace les namespace separators par des directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Si le fichier existe, on l'inclut
    if (file_exists($file)) {
        require $file;
    }
});
