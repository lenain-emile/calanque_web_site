<?php

namespace App\Controllers;
use App\Models\User;
use Exception;

class UserController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    private function response($success, $message, $data = null) {
        return compact('success', 'message', 'data');
    }

    private function requireMethod($method) {
        if ($_SERVER['REQUEST_METHOD'] !== $method) {
            return $this->response(false, "Méthode $method requise.");
        }
        return true;
    }

    private function requireSession() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return $this->response(false, 'Session non initialisée.');
        }
        return true;
    }

    private function input() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    // --- CRUD ---
    public function createUser() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;

        $data = $this->input();
        $first = $data['firstName'] ?? null;
        $last  = $data['lastName'] ?? null;
        $email = $data['email'] ?? null;
        $pass  = $data['password'] ?? null;
        $role  = $data['role'] ?? 1;

        if (!$first || !$last || !$email || !$pass) {
            return $this->response(false, 'Tous les champs sont obligatoires.');
        }

        if ($this->user->getByEmail($email)) {
            return $this->response(false, 'Cet email existe déjà.');
        }

        try {
            $ok = $this->user->create($first, $last, $email, $pass, $role);
            return $this->response($ok, $ok ? 'Utilisateur créé.' : 'Erreur création.');
        } catch (Exception $e) {
            return $this->response(false, 'Erreur base de données: ' . $e->getMessage());
        }
    }

    public function loginUser() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;

        $data = $this->input();
        $email = $data['email'] ?? null;
        $pass  = $data['password'] ?? null;

        if (!$email || !$pass) return $this->response(false, 'Champs requis.');

        $user = $this->user->getByEmailForAuth($email);
        if (!$user || !password_verify($pass, $user['password_hash'])) {
            return $this->response(false, 'Identifiants incorrects.');
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        unset($user['password_hash']);

        return $this->response(true, 'Connexion réussie.', $user);
    }

    public function logoutUser() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;
        session_unset();
        session_destroy();
        return $this->response(true, 'Déconnexion réussie.');
    }

    public function getUserById($id) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        $user = $this->user->getById($id);
        return $user ? $this->response(true, 'Utilisateur trouvé.', $user)
                     : $this->response(false, 'Utilisateur introuvable.');
    }

    public function getAllUsers() {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        return $this->response(true, 'Liste récupérée.', $this->user->getAll());
    }

    public function updateUser($id) {
        if (($check = $this->requireMethod('PUT')) !== true) return $check;
        $data = $this->input();
        $ok = $this->user->update($id, $data['email'] ?? null, $data['password'] ?? null);
        return $this->response($ok, $ok ? 'Mise à jour réussie.' : 'Erreur mise à jour.');
    }

    public function deleteUser($id) {
        if (($check = $this->requireMethod('DELETE')) !== true) return $check;
        $ok = $this->user->delete($id);
        return $this->response($ok, $ok ? 'Suppression réussie.' : 'Erreur suppression.');
    }
}
