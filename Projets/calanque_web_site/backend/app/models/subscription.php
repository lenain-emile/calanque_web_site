<?php

namespace App\Models;
use App\Config\Database;

class Subscription {
    private $connect;

    public function __construct() {
        $pdo = new Database();
        $this->connect = $pdo->connect();
    }



public function create($user_id, $type, $start_date, $end_date, $status = 'ACTIVE') {
    try {
        $sql = "INSERT INTO subscriptions (user_id, type, start_date, end_date, status)
                VALUES (:user_id, :type, :start_date, :end_date, :status)";
        $stm = $this->connect->prepare($sql);
        $stm->execute([
            ':user_id'    => $user_id,
            ':type'       => $type,
            ':start_date' => $start_date,
            ':end_date'   => $end_date,
            ':status'     => $status
        ]);
        return true;
    } catch (\PDOException $e) {
        error_log("Erreur lors de la création de la subscription : " . $e->getMessage());
        return false;
    }
}


 public function getById($id){

try{
    $sql="SELECT * FROM subscriptions WHERE id = :id";
    $stm=$this->connect->prepare($sql);
    $stm->execute([':id'=>$id]);
    return $stm->fetch(\PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Erreur lors de la récupération de la subscription : " . $e->getMessage());
    return false;
}
}


Public function getAll(){

try{ 
    $sql= "SELECT * FROM subscriptions";
    $stm=$this->connect->prepare($sql);
    $stm->execute();
    return $stm->fetchAll(\PDO::FETCH_ASSOC);
} catch (\PDOException $e) {
    error_log("Erreur lors de la récupération des subscriptions : " . $e->getMessage());
    return false;
}

}


public function update($id, $type, $start_date, $end_date, $status){

    try{
        $sql="UPDATE subscriptions SET type=:type, start_date=:start_date, end_date=:end_date, status=:status WHERE id=:id";
        $stm=$this->connect->prepare($sql);
        $stm->execute([
            ':type'=>$type,
            ':start_date'=>$start_date,
            ':end_date'=>$end_date,
            ':status'=>$status,
            ':id'=>$id
        ]);
        return true;
    } catch (\PDOException $e) {
        error_log("Erreur lors de la mise à jour de la subscription : " . $e->getMessage());
        return false;




    }

}

public function delete($id) {
    try {
        $sql = "DELETE FROM subscriptions WHERE id = :id";
        $stm = $this->connect->prepare($sql);
        $stm->execute([':id' => $id]);
        return true;
    } catch (\PDOException $e) {
        error_log("Erreur lors de la suppression de la subscription : " . $e->getMessage());
        return false;
    }
}

public function getByUserId($userId) {
    try {
        $sql = "SELECT * FROM subscriptions WHERE user_id = :user_id";
        $stm = $this->connect->prepare($sql);
        $stm->execute([':user_id' => $userId]);
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Erreur lors de la récupération des subscriptions par utilisateur : " . $e->getMessage());
        return false;
    }
}
//  abonnement actif d'un utilisateur
public function getActiveByUserId($userId) {
    try {
        $sql = "SELECT * FROM subscriptions WHERE user_id = :user_id AND status = 'ACTIVE' LIMIT 1";
        $stm = $this->connect->prepare($sql);
        $stm->execute([':user_id' => $userId]);
        return $stm->fetch(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Erreur lors de la récupération de l'abonnement actif : " . $e->getMessage());
        return false;
    }
}
//  abandonements actifs
public function getActiveSubscriptions() {
    try {
        $sql = "SELECT * FROM subscriptions WHERE status = 'ACTIVE'";
        $stm = $this->connect->prepare($sql);
        $stm->execute();
        return $stm->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Erreur lors de la récupération des abonnements actifs : " . $e->getMessage());
        return false;
    }
}

// Renouveler un abonnement
public function renew($subscriptionId, $newEndDate) {
    try {
        $sql = "UPDATE subscriptions SET end_date = :end_date WHERE id = :id";
        $stm = $this->connect->prepare($sql);
        $stm->execute([
            ':end_date' => $newEndDate,
            ':id' => $subscriptionId
        ]);
        return true;
    } catch (\PDOException $e) {
        error_log("Erreur lors du renouvellement de l'abonnement : " . $e->getMessage());
        return false;
    }
}

// Suspendre un abonnement
public function suspend($subscriptionId) {
    try {
        $sql = "UPDATE subscriptions SET status = 'SUSPENDED' WHERE id = :id";
        $stm = $this->connect->prepare($sql);
        $stm->execute([':id' => $subscriptionId]);
        return true;
    } catch (\PDOException $e) {
        error_log("Erreur lors de la suspension de l'abonnement : " . $e->getMessage());
        return false;
    }
}

// Annuler un abonnement
public function cancel($subscriptionId) {
    try {
        $sql = "UPDATE subscriptions SET status = 'CANCELLED' WHERE id = :id";
        $stm = $this->connect->prepare($sql);
        $stm->execute([':id' => $subscriptionId]);
        return true;
    } catch (\PDOException $e) {
        error_log("Erreur lors de l'annulation de l'abonnement : " . $e->getMessage());
        return false;
    }
}

// Méthodes Stripe
public function getByStripeSubscriptionId($stripeSubscriptionId) {
    try {
        $sql = "SELECT * FROM subscriptions WHERE stripe_subscription_id = :stripe_subscription_id";
        $stm = $this->connect->prepare($sql);
        $stm->execute([':stripe_subscription_id' => $stripeSubscriptionId]);
        return $stm->fetch(\PDO::FETCH_ASSOC);
    } catch (\PDOException $e) {
        error_log("Erreur lors de la récupération de l'abonnement par ID Stripe : " . $e->getMessage());
        return false;
    }
}

public function updateStripeData($id, $stripeSubscriptionId, $stripeCustomerId, $priceId, $amount) {
    try {
        $sql = "UPDATE subscriptions SET 
                    stripe_subscription_id = :stripe_subscription_id,
                    stripe_customer_id = :stripe_customer_id,
                    price_id = :price_id,
                    amount = :amount
                WHERE id = :id";
        $stm = $this->connect->prepare($sql);
        $stm->execute([
            ':id' => $id,
            ':stripe_subscription_id' => $stripeSubscriptionId,
            ':stripe_customer_id' => $stripeCustomerId,
            ':price_id' => $priceId,
            ':amount' => $amount
        ]);
        return true;
    } catch (\PDOException $e) {
        error_log("Erreur lors de la mise à jour des données Stripe : " . $e->getMessage());
        return false;
    }
}

public function updateStatus($id, $status) {
    try {
        $sql = "UPDATE subscriptions SET status = :status WHERE id = :id";
        $stm = $this->connect->prepare($sql);
        $stm->execute([':id' => $id, ':status' => $status]);
        return true;
    } catch (\PDOException $e) {
        error_log("Erreur lors de la mise à jour du statut : " . $e->getMessage());
        return false;
    }
}

}











