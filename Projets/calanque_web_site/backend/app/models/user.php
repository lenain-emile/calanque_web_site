<?php

namespace App\Models;
use App\Config\Database;
use PDO;

class User {
    private $connect;

    public function __construct() {
        $pdo = new Database();
        $this->connect = $pdo->connect();
    }

    // --- Helpers ---
    private function query($sql, $params = [], $single = false) {
        $stm = $this->connect->prepare($sql);
        $stm->execute($params);
        $result = $single ? $stm->fetch(PDO::FETCH_ASSOC) : $stm->fetchAll(PDO::FETCH_ASSOC);

        // On enlève le hash si trouvé
        if ($result) {
            if ($single) unset($result['password_hash']);
            else foreach ($result as &$row) unset($row['password_hash']);
        }
        return $result;
    }

    private function execute($sql, $params = []) {
        $stm = $this->connect->prepare($sql);
        return $stm->execute($params);
    }

    // --- CRUD ---
    public function create($firstName, $lastName, $email, $password, $role = 1) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash, role_id)
                VALUES (:first_name, :last_name, :email, :password_hash, :role)";
        return $this->execute($sql, [
            ':first_name' => $firstName,
            ':last_name'  => $lastName,
            ':email'      => $email,
            ':password_hash' => $hashedPassword,
            ':role'       => $role
        ]);
    }

    public function getById($id) {
        return $this->query("SELECT * FROM users WHERE id = :id", [':id' => $id], true);
    }

    public function getByEmail($email) {
        return $this->query("SELECT * FROM users WHERE email = :email", [':email' => $email], true);
    }

    public function getByEmailForAuth($email) {
        $stm = $this->connect->prepare("SELECT * FROM users WHERE email = :email");
        $stm->execute([':email' => $email]);
        return $stm->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        return $this->query("SELECT * FROM users");
    }

    public function update($id, $email = null, $password = null) {
        $fields = [];
        $params = [':id' => $id];
        if ($email) {
            $fields[] = "email = :email";
            $params[':email'] = $email;
        }
        if ($password) {
            $fields[] = "password_hash = :password";
            $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
        }
        if (!$fields) return false;
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id";
        return $this->execute($sql, $params);
    }

    public function delete($id) {
        return $this->execute("DELETE FROM users WHERE id = :id", [':id' => $id]);
    }
}
