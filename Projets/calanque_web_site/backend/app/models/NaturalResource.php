<?php

namespace App\Models;
use App\Config\Database;
use PDO;

class NaturalResource {
    private $connect;

    public function __construct() {
        $pdo = new Database();
        $this->connect = $pdo->connect();
    }

    /**
     * Récupère toutes les ressources naturelles
     */
    public function getAll() {
        $stmt = $this->connect->query("SELECT * FROM natural_resources ORDER BY type, name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une ressource par son ID
     */
    public function getById($id) {
        $stmt = $this->connect->prepare("SELECT * FROM natural_resources WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les ressources par type
     */
    public function getByType($type) {
        $stmt = $this->connect->prepare("SELECT * FROM natural_resources WHERE type = :type ORDER BY name ASC");
        $stmt->execute(['type' => $type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée une nouvelle ressource naturelle
     */
    public function create($name, $type, $description = null) {
        $sql = "INSERT INTO natural_resources (name, type, description) 
                VALUES (:name, :type, :description)";
        
        $stmt = $this->connect->prepare($sql);
        return $stmt->execute([
            'name' => $name,
            'type' => $type,
            'description' => $description
        ]);
    }

    /**
     * Met à jour une ressource naturelle
     */
    public function update($id, $name, $type, $description = null) {
        $sql = "UPDATE natural_resources 
                SET name = :name, type = :type, description = :description 
                WHERE id = :id";
        
        $stmt = $this->connect->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'type' => $type,
            'description' => $description
        ]);
    }

    /**
     * Supprime une ressource naturelle
     */
    public function delete($id) {
        // Supprimer d'abord les associations dans trails_natural_resources
        $stmt = $this->connect->prepare("DELETE FROM trails_natural_resources WHERE resource_id = :id");
        $stmt->execute(['id' => $id]);
        
        // Puis supprimer la ressource
        $stmt = $this->connect->prepare("DELETE FROM natural_resources WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
