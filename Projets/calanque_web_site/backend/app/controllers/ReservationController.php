<?php

namespace App\Controllers;
use App\Models\Reservation;
use Exception;

class ReservationController {
    private $reservation;

    public function __construct() {
        $this->reservation = new Reservation();
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
    public function createReservation() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;

        $data = $this->input();
        $userId   = $data['user_id'] ?? null;
        $campingId = $data['camping_id'] ?? null;
        $start    = $data['start_date'] ?? null;
        $end      = $data['end_date'] ?? null;
        $people   = $data['num_people'] ?? null;
        $name     = $data['reservation_name'] ?? null;

        if (!$userId || !$campingId || !$start || !$end || !$people) {
            return $this->response(false, 'Tous les champs sont obligatoires.');
        }

        try {
            $ok = $this->reservation->create($userId, $campingId, $start, $end, $people, $name);
            return $this->response($ok, $ok ? 'Réservation créée avec succès.' : 'Erreur lors de la création.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    public function getReservationById($id) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        $res = $this->reservation->getById($id);
        return $res ? $this->response(true, 'Réservation trouvée.', $res)
                    : $this->response(false, 'Réservation introuvable.');
    }

    public function getReservationsByUser($userId) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        return $this->response(true, 'Réservations récupérées.', $this->reservation->getByUser($userId));
    }

    public function getAllReservations() {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        return $this->response(true, 'Liste des réservations.', $this->reservation->getAll());
    }

    public function updateReservationStatus($id) {
        if (($check = $this->requireMethod('PUT')) !== true) return $check;
        $data = $this->input();
        $status = $data['status'] ?? null;
        if (!$status) return $this->response(false, 'Champ status obligatoire.');
        $ok = $this->reservation->updateStatus($id, $status);
        return $this->response($ok, $ok ? 'Mise à jour réussie.' : 'Erreur mise à jour.');
    }

    public function deleteReservation($id) {
        if (($check = $this->requireMethod('DELETE')) !== true) return $check;
        $ok = $this->reservation->delete($id);
        return $this->response($ok, $ok ? 'Suppression réussie.' : 'Erreur suppression.');
    }
    
    // --- Méthodes de gestion des capacités ---
    
    /**
     * Vérifie la capacité disponible d'un camping pour une période donnée
     */
    public function checkCapacity() {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        
        $campingId = $_GET['camping_id'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $numPeople = $_GET['num_people'] ?? 0;
        
        if (!$campingId || !$startDate || !$endDate) {
            return $this->response(false, 'Paramètres manquants: camping_id, start_date, end_date requis.');
        }
        
        try {
            $capacityInfo = $this->reservation->checkCapacityAvailable($campingId, $startDate, $endDate, $numPeople);
            return $this->response(true, 'Vérification de capacité effectuée.', $capacityInfo);
        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }
    
    /**
     * Récupère les informations de capacité d'un camping
     */
    public function getCampingCapacityInfo() {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        
        $campingId = $_GET['camping_id'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        if (!$campingId) {
            return $this->response(false, 'Paramètre camping_id requis.');
        }
        
        try {
            $capacityInfo = $this->reservation->getCampingCapacityInfo($campingId, $startDate, $endDate);
            if (!$capacityInfo) {
                return $this->response(false, 'Camping introuvable.');
            }
            return $this->response(true, 'Informations de capacité récupérées.', $capacityInfo);
        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }
    
    /**
     * Met à jour une réservation complète (pas seulement le statut)
     */
    public function updateReservation($id) {
        if (($check = $this->requireMethod('PUT')) !== true) return $check;
        
        $data = $this->input();
        $userId = $data['user_id'] ?? null;
        $campingId = $data['camping_id'] ?? null;
        $start = $data['start_date'] ?? null;
        $end = $data['end_date'] ?? null;
        $people = $data['num_people'] ?? null;
        $name = $data['reservation_name'] ?? null;
        
        if (!$userId || !$campingId || !$start || !$end || !$people) {
            return $this->response(false, 'Tous les champs sont obligatoires.');
        }
        
        try {
            $ok = $this->reservation->updateReservation($id, $userId, $campingId, $start, $end, $people, $name);
            return $this->response($ok, $ok ? 'Réservation mise à jour avec succès.' : 'Erreur lors de la mise à jour.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    // --- MÉTHODES DE PAIEMENT ---

    /**
     * Crée une réservation avec paiement Stripe
     */
    public function createReservationWithPayment() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;

        $data = $this->input();
        $userId = $data['user_id'] ?? null;
        $campingId = $data['camping_id'] ?? null;
        $start = $data['start_date'] ?? null;
        $end = $data['end_date'] ?? null;
        $people = $data['num_people'] ?? null;
        $name = $data['reservation_name'] ?? null;
        $amount = $data['amount'] ?? null;

        if (!$userId || !$campingId || !$start || !$end || !$people || !$amount) {
            return $this->response(false, 'Tous les champs sont obligatoires.');
        }

        try {
            // Créer la réservation en base
            $ok = $this->reservation->create($userId, $campingId, $start, $end, $people, $name);
            if (!$ok) {
                return $this->response(false, 'Erreur lors de la création de la réservation.');
            }

            $reservationId = $this->reservation->connect->lastInsertId();

            // Mettre à jour le montant de la réservation
            $this->reservation->updateAmount($reservationId, $amount);

            // Créer le paiement
            $paymentController = new \App\Controllers\PaymentController();
            $paymentData = [
                'user_id' => $userId,
                'reservation_id' => $reservationId,
                'amount' => $amount
            ];

            // Simuler l'appel au contrôleur de paiement
            $_POST = $paymentData;
            $paymentResult = $paymentController->createReservationPayment();

            if ($paymentResult['success']) {
                return $this->response(true, 'Réservation créée avec paiement.', [
                    'reservation_id' => $reservationId,
                    'payment_data' => $paymentResult['data']
                ]);
            } else {
                // Supprimer la réservation si le paiement échoue
                $this->reservation->delete($reservationId);
                return $this->response(false, 'Erreur lors de la création du paiement: ' . $paymentResult['message']);
            }

        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    /**
     * Récupère les réservations avec informations de paiement
     */
    public function getReservationsWithPayments($userId = null) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;

        try {
            $reservations = $this->reservation->getReservationsWithPayments($userId);
            return $this->response(true, 'Réservations avec paiements récupérées.', $reservations);

        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les réservations par statut de paiement
     */
    public function getReservationsByPaymentStatus() {
        if (($check = $this->requireMethod('GET')) !== true) return $check;

        $paymentStatus = $_GET['payment_status'] ?? null;
        if (!$paymentStatus) {
            return $this->response(false, 'Statut de paiement requis.');
        }

        try {
            $reservations = $this->reservation->getReservationsByPaymentStatus($paymentStatus);
            return $this->response(true, "Réservations avec statut de paiement: $paymentStatus", $reservations);

        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour le statut de paiement d'une réservation
     */
    public function updateReservationPaymentStatus($id) {
        if (($check = $this->requireMethod('PUT')) !== true) return $check;

        $data = $this->input();
        $paymentStatus = $data['payment_status'] ?? null;

        if (!$paymentStatus) {
            return $this->response(false, 'Statut de paiement requis.');
        }

        try {
            $ok = $this->reservation->updatePaymentStatus($id, $paymentStatus);
            return $this->response($ok, $ok ? 'Statut de paiement mis à jour.' : 'Erreur lors de la mise à jour.');

        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }
}
