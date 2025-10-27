<?php

namespace App\Controllers;

abstract class BaseController {
    
    /**
     * Formate une réponse standardisée
     */
    protected function response($success, $message, $data = null) {
        return compact('success', 'message', 'data');
    }

    /**
     * Vérifie que la méthode HTTP est correcte
     */
    protected function requireMethod($method) {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            return $this->response(false, "Méthode $method requise.");
        }
        return true;
    }

    /**
     * Vérifie qu'une session est active
     */
    protected function requireSession() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return $this->response(false, 'Session non initialisée.');
        }
        return true;
    }

    /**
     * Récupère les données JSON de la requête
     */
    protected function input() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
}
