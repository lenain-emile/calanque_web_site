<?php

namespace App\Controllers;
use App\Models\Subscription;
use Exception;

class SubscriptionController {
    private $subscription;

    public function __construct() {
        $this->subscription = new Subscription();
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

    private function input() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    // --- CRUD DE BASE ---
    
    public function createSubscription() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;

        $data = $this->input();
        $userId = $data['user_id'] ?? null;
        $type = $data['type'] ?? null;
        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;
        $status = $data['status'] ?? 'ACTIVE';

        if (!$userId || !$type || !$startDate || !$endDate) {
            return $this->response(false, 'Tous les champs sont obligatoires.');
        }

        try {
            $ok = $this->subscription->create($userId, $type, $startDate, $endDate, $status);
            return $this->response($ok, $ok ? 'Abonnement créé.' : 'Erreur création.');
        } catch (Exception $e) {
            return $this->response(false, 'Erreur base de données: ' . $e->getMessage());
        }
    }

    public function getSubscriptionById($id) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        $subscription = $this->subscription->getById($id);
        return $subscription ? $this->response(true, 'Abonnement trouvé.', $subscription)
                            : $this->response(false, 'Abonnement introuvable.');
    }

    public function getAllSubscriptions() {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        return $this->response(true, 'Liste récupérée.', $this->subscription->getAll());
    }

    public function updateSubscription($id) {
        if (($check = $this->requireMethod('PUT')) !== true) return $check;
        
        $data = $this->input();
        $type = $data['type'] ?? null;
        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;
        $status = $data['status'] ?? null;

        if (!$type || !$startDate || !$endDate || !$status) {
            return $this->response(false, 'Tous les champs sont obligatoires.');
        }

        $ok = $this->subscription->update($id, $type, $startDate, $endDate, $status);
        return $this->response($ok, $ok ? 'Mise à jour réussie.' : 'Erreur mise à jour.');
    }

    public function deleteSubscription($id) {
        if (($check = $this->requireMethod('DELETE')) !== true) return $check;
        $ok = $this->subscription->delete($id);
        return $this->response($ok, $ok ? 'Suppression réussie.' : 'Erreur suppression.');
    }

    // --- GESTION DES ABONNEMENTS UTILISATEUR ---

    public function getUserSubscriptions($userId) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        $subscriptions = $this->subscription->getByUserId($userId);
        return $this->response(true, 'Abonnements utilisateur récupérés.', $subscriptions);
    }

    public function getActiveUserSubscription($userId) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        $subscription = $this->subscription->getActiveByUserId($userId);
        return $subscription ? $this->response(true, 'Abonnement actif trouvé.', $subscription)
                            : $this->response(false, 'Aucun abonnement actif.');
    }

    public function getActiveSubscriptions() {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        $subscriptions = $this->subscription->getActiveSubscriptions();
        return $this->response(true, 'Abonnements actifs récupérés.', $subscriptions);
    }

    // --- CYCLE DE VIE ---

    public function cancelSubscription($id) {
        if (($check = $this->requireMethod('POST')) !== true) return $check;
        
        // Vérifier que l'abonnement existe
        $subscription = $this->subscription->getById($id);
        if (!$subscription) {
            return $this->response(false, 'Abonnement introuvable.');
        }

        try {
            $ok = $this->subscription->cancel($id);
            return $this->response($ok, $ok ? 'Abonnement annulé.' : 'Erreur annulation.');
        } catch (Exception $e) {
            return $this->response(false, 'Erreur base de données: ' . $e->getMessage());
        }
    }

    public function renewSubscription($id) {
        if (($check = $this->requireMethod('POST')) !== true) return $check;
        
        $data = $this->input();
        $newEndDate = $data['new_end_date'] ?? null;

        if (!$newEndDate) {
            return $this->response(false, 'La nouvelle date de fin est obligatoire.');
        }

        // Vérifier que l'abonnement existe
        $subscription = $this->subscription->getById($id);
        if (!$subscription) {
            return $this->response(false, 'Abonnement introuvable.');
        }

        try {
            $ok = $this->subscription->renew($id, $newEndDate);
            return $this->response($ok, $ok ? 'Abonnement renouvelé.' : 'Erreur renouvellement.');
        } catch (Exception $e) {
            return $this->response(false, 'Erreur base de données: ' . $e->getMessage());
        }
    }

    public function suspendSubscription($id) {
        if (($check = $this->requireMethod('POST')) !== true) return $check;
        
        // Vérifier que l'abonnement existe
        $subscription = $this->subscription->getById($id);
        if (!$subscription) {
            return $this->response(false, 'Abonnement introuvable.');
        }

        try {
            $ok = $this->subscription->suspend($id);
            return $this->response($ok, $ok ? 'Abonnement suspendu.' : 'Erreur suspension.');
        } catch (Exception $e) {
            return $this->response(false, 'Erreur base de données: ' . $e->getMessage());
        }
    }

    // --- MÉTHODES DE PAIEMENT ---

    /**
     * Crée un abonnement avec paiement Stripe
     */
    public function createSubscriptionWithPayment() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;

        $data = $this->input();
        $userId = $data['user_id'] ?? null;
        $type = $data['type'] ?? null;
        $amount = $data['amount'] ?? null;
        $startDate = $data['start_date'] ?? date('Y-m-d');
        $endDate = $data['end_date'] ?? null;

        if (!$userId || !$type || !$amount) {
            return $this->response(false, 'Tous les champs sont obligatoires.');
        }

        try {
            // Créer l'abonnement en base
            $ok = $this->subscription->create($userId, $type, $startDate, $endDate, 'PENDING');
            if (!$ok) {
                return $this->response(false, 'Erreur lors de la création de l\'abonnement.');
            }

            $subscriptionId = $this->subscription->connect->lastInsertId();

            // Rediriger vers le contrôleur de paiement
            $paymentController = new \App\Controllers\PaymentController();
            $paymentData = [
                'user_id' => $userId,
                'subscription_id' => $subscriptionId,
                'amount' => $amount
            ];

            // Simuler l'appel au contrôleur de paiement
            $_POST = $paymentData;
            $paymentResult = $paymentController->createSubscriptionPayment();

            if ($paymentResult['success']) {
                return $this->response(true, 'Abonnement créé avec paiement.', [
                    'subscription_id' => $subscriptionId,
                    'payment_data' => $paymentResult['data']
                ]);
            } else {
                // Supprimer l'abonnement si le paiement échoue
                $this->subscription->delete($subscriptionId);
                return $this->response(false, 'Erreur lors de la création du paiement: ' . $paymentResult['message']);
            }

        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les abonnements avec informations de paiement
     */
    public function getSubscriptionsWithPayments($userId = null) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;

        try {
            if ($userId) {
                $subscriptions = $this->subscription->getByUserId($userId);
            } else {
                $subscriptions = $this->subscription->getAll();
            }

            // Ajouter les informations de paiement
            $paymentModel = new \App\Models\Payment();
            foreach ($subscriptions as &$subscription) {
                $payments = $paymentModel->getBySubscriptionId($subscription['id']);
                $subscription['payments'] = $payments;
                $subscription['last_payment'] = !empty($payments) ? end($payments) : null;
            }

            return $this->response(true, 'Abonnements avec paiements récupérés.', $subscriptions);

        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }
}