<?php

namespace App\Controllers;
use App\Models\Camping;
use Exception;

class CampingController extends BaseController {
    private $camping;

    public function __construct() {
        $this->camping = new Camping();
    }

    // --- CRUD ---
    
    /**
     * Récupère tous les campings
     */
    public function getAllCampings() {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        
        try {
            $campings = $this->camping->getAll();
            return $this->response(true, 'Liste des campings récupérée.', $campings);
        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère un camping par son ID
     */
    public function getCampingById($id) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        
        try {
            $camping = $this->camping->getById($id);
            if ($camping) {
                return $this->response(true, 'Camping trouvé.', $camping);
            } else {
                return $this->response(false, 'Camping introuvable.');
            }
        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les campings avec leur disponibilité pour une période donnée
     */
    public function getCampingsWithAvailability() {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        try {
            $campings = $this->camping->getWithAvailability($startDate, $endDate);
            return $this->response(true, 'Campings avec disponibilité récupérés.', $campings);
        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Vérifie la disponibilité d'un camping pour une période donnée
     * Redirige vers le contrôleur Reservation pour éviter la duplication
     */
    public function checkCampingAvailability() {
        $reservationController = new \App\Controllers\ReservationController();
        return $reservationController->checkCapacity();
    }

    /**
     * Crée un nouveau camping
     */
    public function createCamping() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;
        
        $data = $this->input();
        $name = $data['name'] ?? null;
        $capacity = $data['capacity'] ?? null;
        $description = $data['description'] ?? null;
        
        if (!$name || !$capacity) {
            return $this->response(false, 'Nom et capacité sont obligatoires.');
        }
        
        try {
            $ok = $this->camping->create($name, $capacity, $description);
            return $this->response($ok, $ok ? 'Camping créé avec succès.' : 'Erreur lors de la création.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    /**
     * Met à jour un camping
     */
    public function updateCamping($id) {
        if (($check = $this->requireMethod('PUT')) !== true) return $check;
        
        $data = $this->input();
        $name = $data['name'] ?? null;
        $capacity = $data['capacity'] ?? null;
        $description = $data['description'] ?? null;
        
        if (!$name || !$capacity) {
            return $this->response(false, 'Nom et capacité sont obligatoires.');
        }
        
        try {
            $ok = $this->camping->update($id, $name, $capacity, $description);
            return $this->response($ok, $ok ? 'Camping mis à jour avec succès.' : 'Erreur lors de la mise à jour.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    /**
     * Supprime un camping
     */
    public function deleteCamping($id) {
        if (($check = $this->requireMethod('DELETE')) !== true) return $check;
        
        try {
            $ok = $this->camping->delete($id);
            return $this->response($ok, $ok ? 'Camping supprimé avec succès.' : 'Erreur lors de la suppression.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }
}
