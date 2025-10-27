<?php

namespace App\Models;
use App\Config\Database;
use PDO;

class Trail {
    private $connect;

    public function __construct() {
        $pdo = new Database();
        $this->connect = $pdo->connect();
    }

    /**
     * Récupère tous les sentiers
     */
    public function getAll() {
        $stmt = $this->connect->query("SELECT * FROM trails ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un sentier par son ID
     */
    public function getById($id) {
        $stmt = $this->connect->prepare("SELECT * FROM trails WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les ressources naturelles associées à un sentier
     */
    public function getNaturalResourcesByTrailId($trailId) {
        $sql = "SELECT nr.* 
                FROM natural_resources nr
                INNER JOIN trails_natural_resources tnr ON nr.id = tnr.resource_id
                WHERE tnr.trail_id = :trail_id
                ORDER BY nr.type, nr.name";
        
        $stmt = $this->connect->prepare($sql);
        $stmt->execute(['trail_id' => $trailId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouveau sentier
     */
    public function create($name, $description, $difficulty, $lengthKm, $pathLocations = null) {
        $sql = "INSERT INTO trails (name, description, difficulty, length_km, path_locations) 
                VALUES (:name, :description, :difficulty, :length_km, :path_locations)";
        
        $stmt = $this->connect->prepare($sql);
        return $stmt->execute([
            'name' => $name,
            'description' => $description,
            'difficulty' => $difficulty,
            'length_km' => $lengthKm,
            'path_locations' => $pathLocations
        ]);
    }

    /**
     * Met à jour un sentier
     */
    public function update($id, $name, $description, $difficulty, $lengthKm, $pathLocations = null) {
        $sql = "UPDATE trails 
                SET name = :name, description = :description, difficulty = :difficulty, 
                    length_km = :length_km, path_locations = :path_locations
                WHERE id = :id";
        
        $stmt = $this->connect->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'difficulty' => $difficulty,
            'length_km' => $lengthKm,
            'path_locations' => $pathLocations
        ]);
    }

    /**
     * Supprime un sentier
     */
    public function delete($id) {
        // Supprimer d'abord les associations dans trails_natural_resources
        $stmt = $this->connect->prepare("DELETE FROM trails_natural_resources WHERE trail_id = :id");
        $stmt->execute(['id' => $id]);
        
        // Puis supprimer le sentier
        $stmt = $this->connect->prepare("DELETE FROM trails WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Associe une ressource naturelle à un sentier
     */
    public function addNaturalResource($trailId, $resourceId) {
        $sql = "INSERT INTO trails_natural_resources (trail_id, resource_id) 
                VALUES (:trail_id, :resource_id)";
        
        $stmt = $this->connect->prepare($sql);
        return $stmt->execute([
            'trail_id' => $trailId,
            'resource_id' => $resourceId
        ]);
    }

    /**
     * Dissocie une ressource naturelle d'un sentier
     */
    public function removeNaturalResource($trailId, $resourceId) {
        $sql = "DELETE FROM trails_natural_resources 
                WHERE trail_id = :trail_id AND resource_id = :resource_id";
        
        $stmt = $this->connect->prepare($sql);
        return $stmt->execute([
            'trail_id' => $trailId,
            'resource_id' => $resourceId
        ]);
    }
}
