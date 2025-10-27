<?php

namespace App\Models;
use App\Config\Database;
use PDO;

class Reservation {
    private $connect;

    public function __construct() {
        $pdo = new Database();
        $this->connect = $pdo->connect();
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

    // Expose l'ID de la dernière insertion (utile aux contrôleurs)
    public function getLastInsertId() {
        return $this->connect->lastInsertId();
    }

    // --- Méthodes de gestion des capacités ---
    
    /**
     * Vérifie si un camping a de la capacité disponible pour une période donnée
     */
    public function checkCapacityAvailable($campingId, $startDate, $endDate, $numPeople, $excludeReservationId = null) {
        // Récupère la capacité totale du camping
        $camping = $this->query("SELECT capacity FROM campings WHERE id = :id", [':id' => $campingId], true);
        if (!$camping) {
            return ['available' => false, 'message' => 'Camping introuvable'];
        }
        
        $totalCapacity = $camping['capacity'];
        
        // Calcule la capacité déjà réservée pour cette période
        $sql = "SELECT COALESCE(SUM(num_people), 0) as reserved_capacity 
                FROM reservations 
                WHERE camping_id = :camping_id 
                AND status IN ('PENDING', 'CONFIRMED', 'ACTIVE')
                AND (
                    (start_date <= :start_date AND end_date >= :start_date) OR
                    (start_date <= :end_date AND end_date >= :end_date) OR
                    (start_date >= :start_date AND end_date <= :end_date)
                )";
        
        $params = [
            ':camping_id' => $campingId,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ];
        
        // Exclut une réservation spécifique (pour les modifications)
        if ($excludeReservationId) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeReservationId;
        }
        
        $result = $this->query($sql, $params, true);
        $reservedCapacity = $result['reserved_capacity'];
        
        $availableCapacity = $totalCapacity - $reservedCapacity;
        
        return [
            'available' => $availableCapacity >= $numPeople,
            'total_capacity' => $totalCapacity,
            'reserved_capacity' => $reservedCapacity,
            'available_capacity' => $availableCapacity,
            'requested_people' => $numPeople,
            'message' => $availableCapacity >= $numPeople ? 
                "Capacité disponible" : 
                "Capacité insuffisante. Disponible: $availableCapacity, Demandé: $numPeople"
        ];
    }
    
    /**
     * Récupère les informations de capacité d'un camping
     */
    public function getCampingCapacityInfo($campingId, $startDate = null, $endDate = null) {
        $camping = $this->query("SELECT * FROM campings WHERE id = :id", [':id' => $campingId], true);
        if (!$camping) {
            return null;
        }
        
        $info = [
            'camping' => $camping,
            'total_capacity' => $camping['capacity']
        ];
        
        // Si des dates sont fournies, calcule la capacité disponible
        if ($startDate && $endDate) {
            $capacityCheck = $this->checkCapacityAvailable($campingId, $startDate, $endDate, 0);
            $info['available_capacity'] = $capacityCheck['available_capacity'];
            $info['reserved_capacity'] = $capacityCheck['reserved_capacity'];
        }
        
        return $info;
    }

    // --- CRUD ---
    public function create($userId, $campingId, $startDate, $endDate, $numPeople, $reservationName = null) {
        // Vérifie d'abord la capacité disponible
        $capacityCheck = $this->checkCapacityAvailable($campingId, $startDate, $endDate, $numPeople);
        if (!$capacityCheck['available']) {
            throw new \Exception($capacityCheck['message']);
        }
        
        $sql = "INSERT INTO reservations 
                (user_id, camping_id, start_date, end_date, num_people, status, reservation_name, created_at)
                VALUES (:user_id, :camping_id, :start_date, :end_date, :num_people, 'PENDING', :reservation_name, NOW())";
        return $this->execute($sql, [
            ':user_id' => $userId,
            ':camping_id' => $campingId,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':num_people' => $numPeople,
            ':reservation_name' => $reservationName
        ]);
    }

    public function getById($id) {
        return $this->query("SELECT * FROM reservations WHERE id = :id", [':id' => $id], true);
    }

    public function getByUser($userId) {
        $sql = "SELECT r.*, c.name AS camping_name, c.capacity, c.description
                FROM reservations r
                JOIN campings c ON r.camping_id = c.id
                WHERE r.user_id = :user_id";
        return $this->query($sql, [':user_id' => $userId]);
    }

    public function getAll() {
        $sql = "SELECT r.*, u.first_name, u.last_name, c.name AS camping_name
                FROM reservations r
                JOIN users u ON r.user_id = u.id
                JOIN campings c ON r.camping_id = c.id";
        return $this->query($sql);
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE reservations SET status = :status WHERE id = :id";
        return $this->execute($sql, [
            ':id' => $id,
            ':status' => $status
        ]);
    }
    
    /**
     * Met à jour une réservation avec validation de capacité
     */
    public function updateReservation($id, $userId, $campingId, $startDate, $endDate, $numPeople, $reservationName = null) {
        // Récupère la réservation existante
        $existingReservation = $this->getById($id);
        if (!$existingReservation) {
            throw new \Exception('Réservation introuvable');
        }
        
        // Vérifie la capacité disponible (en excluant la réservation actuelle)
        $capacityCheck = $this->checkCapacityAvailable($campingId, $startDate, $endDate, $numPeople, $id);
        if (!$capacityCheck['available']) {
            throw new \Exception($capacityCheck['message']);
        }
        
        $sql = "UPDATE reservations 
                SET user_id = :user_id, camping_id = :camping_id, start_date = :start_date, 
                    end_date = :end_date, num_people = :num_people, reservation_name = :reservation_name
                WHERE id = :id";
        return $this->execute($sql, [
            ':id' => $id,
            ':user_id' => $userId,
            ':camping_id' => $campingId,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':num_people' => $numPeople,
            ':reservation_name' => $reservationName
        ]);
    }

    public function delete($id) {
        return $this->execute("DELETE FROM reservations WHERE id = :id", [':id' => $id]);
    }

    // --- MÉTHODES DE PAIEMENT ---

    public function updatePaymentStatus($id, $paymentStatus) {
        try {
            $sql = "UPDATE reservations 
                    SET payment_status = :payment_status,
                        status = CASE 
                            WHEN :payment_status = 'paid' THEN 'CONFIRMED'
                            ELSE status
                        END
                    WHERE id = :id";
            
            return $this->execute($sql, [
                ':id' => $id,
                ':payment_status' => $paymentStatus
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur lors de la mise à jour du statut de paiement : " . $e->getMessage());
            return false;
        }
    }

    public function updateAmount($id, $amount) {
        $sql = "UPDATE reservations SET amount = :amount WHERE id = :id";
        return $this->execute($sql, [
            ':id' => $id,
            ':amount' => $amount
        ]);
    }

    public function getReservationsWithPayments($userId = null) {
        $sql = "SELECT r.*, 
                       c.name AS camping_name, c.capacity, c.description,
                       p.id as payment_id, p.amount as payment_amount, p.status as payment_status,
                       p.stripe_payment_intent_id, p.payment_date
                FROM reservations r
                JOIN campings c ON r.camping_id = c.id
                LEFT JOIN payments p ON r.id = p.reservation_id
                WHERE 1=1";
        
        $params = [];
        if ($userId) {
            $sql .= " AND r.user_id = :user_id";
            $params[':user_id'] = $userId;
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        return $this->query($sql, $params);
    }

    public function getReservationsByPaymentStatus($paymentStatus) {
        $sql = "SELECT r.*, 
                       c.name AS camping_name,
                       u.first_name, u.last_name, u.email
                FROM reservations r
                JOIN campings c ON r.camping_id = c.id
                JOIN users u ON r.user_id = u.id
                WHERE r.payment_status = :payment_status
                ORDER BY r.created_at DESC";
        
        return $this->query($sql, [':payment_status' => $paymentStatus]);
    }
}
