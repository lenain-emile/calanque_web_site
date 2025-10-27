<?php

namespace App\Models;
use App\Config\Database;
use PDO;

class Payment {
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

    // --- CRUD ---
    public function create($data) {
        $sql = "INSERT INTO payments (
                    stripe_payment_intent_id, stripe_customer_id, stripe_subscription_id,
                    subscription_id, reservation_id, amount, status, method, 
                    payment_type, payment_date, stripe_metadata
                ) VALUES (
                    :stripe_payment_intent_id, :stripe_customer_id, :stripe_subscription_id,
                    :subscription_id, :reservation_id, :amount, :status, :method,
                    :payment_type, :payment_date, :stripe_metadata
                )";
        
        return $this->execute($sql, [
            ':stripe_payment_intent_id' => $data['stripe_payment_intent_id'] ?? null,
            ':stripe_customer_id' => $data['stripe_customer_id'] ?? null,
            ':stripe_subscription_id' => $data['stripe_subscription_id'] ?? null,
            ':subscription_id' => $data['subscription_id'] ?? null,
            ':reservation_id' => $data['reservation_id'] ?? null,
            ':amount' => $data['amount'],
            ':status' => $data['status'] ?? 'PENDING',
            ':method' => $data['method'] ?? 'stripe',
            ':payment_type' => $data['payment_type'] ?? 'one_time',
            ':payment_date' => $data['payment_date'] ?? date('Y-m-d'),
            ':stripe_metadata' => isset($data['stripe_metadata']) ? json_encode($data['stripe_metadata']) : null
        ]);
    }

    public function getById($id) {
        return $this->query("SELECT * FROM payments WHERE id = :id", [':id' => $id], true);
    }

    public function getByStripePaymentIntentId($paymentIntentId) {
        return $this->query(
            "SELECT * FROM payments WHERE stripe_payment_intent_id = :payment_intent_id", 
            [':payment_intent_id' => $paymentIntentId], 
            true
        );
    }

    public function getByStripeCustomerId($customerId) {
        return $this->query(
            "SELECT * FROM payments WHERE stripe_customer_id = :customer_id", 
            [':customer_id' => $customerId]
        );
    }

    public function getBySubscriptionId($subscriptionId) {
        return $this->query(
            "SELECT * FROM payments WHERE subscription_id = :subscription_id", 
            [':subscription_id' => $subscriptionId]
        );
    }

    public function getByReservationId($reservationId) {
        return $this->query(
            "SELECT * FROM payments WHERE reservation_id = :reservation_id", 
            [':reservation_id' => $reservationId]
        );
    }

    public function updateStatus($id, $status, $stripeMetadata = null) {
        $sql = "UPDATE payments SET status = :status";
        $params = [':id' => $id, ':status' => $status];
        
        if ($stripeMetadata) {
            $sql .= ", stripe_metadata = :stripe_metadata";
            $params[':stripe_metadata'] = json_encode($stripeMetadata);
        }
        
        $sql .= " WHERE id = :id";
        return $this->execute($sql, $params);
    }

    public function updateStripeData($id, $stripeData) {
        $sql = "UPDATE payments SET 
                    stripe_payment_intent_id = :stripe_payment_intent_id,
                    stripe_customer_id = :stripe_customer_id,
                    stripe_subscription_id = :stripe_subscription_id,
                    stripe_metadata = :stripe_metadata
                WHERE id = :id";
        
        return $this->execute($sql, [
            ':id' => $id,
            ':stripe_payment_intent_id' => $stripeData['stripe_payment_intent_id'] ?? null,
            ':stripe_customer_id' => $stripeData['stripe_customer_id'] ?? null,
            ':stripe_subscription_id' => $stripeData['stripe_subscription_id'] ?? null,
            ':stripe_metadata' => isset($stripeData['stripe_metadata']) ? json_encode($stripeData['stripe_metadata']) : null
        ]);
    }

    public function getAll() {
        $sql = "SELECT p.*, 
                       s.type as subscription_type,
                       r.reservation_name,
                       u.first_name, u.last_name, u.email
                FROM payments p
                LEFT JOIN subscriptions s ON p.subscription_id = s.id
                LEFT JOIN reservations r ON p.reservation_id = r.id
                LEFT JOIN users u ON (s.user_id = u.id OR r.user_id = u.id)
                ORDER BY p.created_at DESC";
        return $this->query($sql);
    }

    public function getByUserId($userId) {
        $sql = "SELECT p.*, 
                       s.type as subscription_type,
                       r.reservation_name
                FROM payments p
                LEFT JOIN subscriptions s ON p.subscription_id = s.id
                LEFT JOIN reservations r ON p.reservation_id = r.id
                WHERE (s.user_id = :user_id OR r.user_id = :user_id)
                ORDER BY p.created_at DESC";
        return $this->query($sql, [':user_id' => $userId]);
    }

    public function getByStatus($status) {
        return $this->query(
            "SELECT * FROM payments WHERE status = :status ORDER BY created_at DESC", 
            [':status' => $status]
        );
    }

    public function delete($id) {
        return $this->execute("DELETE FROM payments WHERE id = :id", [':id' => $id]);
    }

}
