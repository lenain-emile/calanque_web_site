<?php

namespace App\Models;
use App\Config\Database;
use PDO;

class Camping {
    private $connect;
    private $reservation;

    public function __construct() {
        $pdo = new Database();
        $this->connect = $pdo->connect();
        $this->reservation = new Reservation();
    }

    // --- Helpers ---
    private function query($sql, $params = [], $single = false) {
        $stm = $this->connect->prepare($sql);
        $stm->execute($params);
        return $single ? $stm->fetch(PDO::FETCH_ASSOC) : $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    private function execute($sql, $params = []) {
        $stm = $this->connect->prepare($sql);
        return $stm->execute($params);
    }

    // --- CRUD ---
    
    /**
     * Récupère tous les campings
     */
    public function getAll() {
        return $this->query("SELECT * FROM campings ORDER BY name");
    }

    /**
     * Récupère un camping par son ID
     */
    public function getById($id) {
        return $this->query("SELECT * FROM campings WHERE id = :id", [':id' => $id], true);
    }

    /**
     * Récupère les campings avec leur capacité disponible pour une période donnée
     */
    public function getWithAvailability($startDate = null, $endDate = null) {
        $campings = $this->getAll();
        
        // Ajouter les informations de disponibilité pour chaque camping
        foreach ($campings as &$camping) {
            if ($startDate && $endDate) {
                $capacityInfo = $this->reservation->getCampingCapacityInfo($camping['id'], $startDate, $endDate);
                $camping['reserved_capacity'] = $capacityInfo['reserved_capacity'] ?? 0;
                $camping['available_capacity'] = $capacityInfo['available_capacity'] ?? $camping['capacity'];
            } else {
                $camping['reserved_capacity'] = 0;
                $camping['available_capacity'] = $camping['capacity'];
            }
        }
        
        return $campings;
    }

    /**
     * Crée un nouveau camping
     */
    public function create($name, $capacity, $description = null) {
        $sql = "INSERT INTO campings (name, capacity, description) 
                VALUES (:name, :capacity, :description)";
        return $this->execute($sql, [
            ':name' => $name,
            ':capacity' => $capacity,
            ':description' => $description
        ]);
    }

    /**
     * Met à jour un camping
     */
    public function update($id, $name, $capacity, $description = null) {
        $sql = "UPDATE campings 
                SET name = :name, capacity = :capacity, description = :description 
                WHERE id = :id";
        return $this->execute($sql, [
            ':id' => $id,
            ':name' => $name,
            ':capacity' => $capacity,
            ':description' => $description
        ]);
    }

    /**
     * Supprime un camping
     */
    public function delete($id) {
        return $this->execute("DELETE FROM campings WHERE id = :id", [':id' => $id]);
    }

    /**
     * Vérifie si un camping a de la capacité disponible
     * Utilise la méthode du modèle Reservation pour éviter la duplication
     */
    public function checkAvailability($campingId, $startDate, $endDate, $numPeople) {
        return $this->reservation->checkCapacityAvailable($campingId, $startDate, $endDate, $numPeople);
    }
}
