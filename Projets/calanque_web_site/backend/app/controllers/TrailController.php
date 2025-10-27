<?php

namespace App\Controllers;
use App\Models\Trail;
use Exception;

class TrailController extends BaseController {
    private $trail;

    public function __construct() {
        $this->trail = new Trail();
    }

    /**
     * Récupère tous les sentiers
     */
    public function getAllTrails() {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        
        try {
            $trails = $this->trail->getAll();
            return $this->response(true, 'Liste des sentiers récupérée.', $trails);
        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère un sentier par son ID
     */
    public function getTrailById($id) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        
        try {
            $trail = $this->trail->getById($id);
            if ($trail) {
                return $this->response(true, 'Sentier trouvé.', $trail);
            } else {
                return $this->response(false, 'Sentier introuvable.');
            }
        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les ressources naturelles d'un sentier
     */
    public function getTrailResources($id) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        
        try {
            $resources = $this->trail->getNaturalResourcesByTrailId($id);
            return $this->response(true, 'Ressources du sentier récupérées.', $resources);
        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau sentier
     */
    public function createTrail() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;
        
        $data = $this->input();
        $name = $data['name'] ?? null;
        $description = $data['description'] ?? null;
        $difficulty = $data['difficulty'] ?? null;
        $lengthKm = $data['length_km'] ?? null;
        $pathLocations = isset($data['path_locations']) ? json_encode($data['path_locations']) : null;
        
        if (!$name || !$difficulty || !$lengthKm) {
            return $this->response(false, 'Nom, difficulté et longueur sont obligatoires.');
        }
        
        try {
            $ok = $this->trail->create($name, $description, $difficulty, $lengthKm, $pathLocations);
            return $this->response($ok, $ok ? 'Sentier créé avec succès.' : 'Erreur lors de la création.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    /**
     * Met à jour un sentier
     */
    public function updateTrail($id) {
        if (($check = $this->requireMethod('PUT')) !== true) return $check;
        
        $data = $this->input();
        $name = $data['name'] ?? null;
        $description = $data['description'] ?? null;
        $difficulty = $data['difficulty'] ?? null;
        $lengthKm = $data['length_km'] ?? null;
        $pathLocations = isset($data['path_locations']) ? json_encode($data['path_locations']) : null;
        
        if (!$name || !$difficulty || !$lengthKm) {
            return $this->response(false, 'Nom, difficulté et longueur sont obligatoires.');
        }
        
        try {
            $ok = $this->trail->update($id, $name, $description, $difficulty, $lengthKm, $pathLocations);
            return $this->response($ok, $ok ? 'Sentier mis à jour avec succès.' : 'Erreur lors de la mise à jour.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    /**
     * Supprime un sentier
     */
    public function deleteTrail($id) {
        if (($check = $this->requireMethod('DELETE')) !== true) return $check;
        
        try {
            $ok = $this->trail->delete($id);
            return $this->response($ok, $ok ? 'Sentier supprimé avec succès.' : 'Erreur lors de la suppression.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    /**
     * Associe une ressource naturelle à un sentier
     */
    public function addResourceToTrail($trailId, $resourceId) {
        if (($check = $this->requireMethod('POST')) !== true) return $check;
        
        try {
            $ok = $this->trail->addNaturalResource($trailId, $resourceId);
            return $this->response($ok, $ok ? 'Ressource ajoutée au sentier.' : 'Erreur lors de l\'ajout.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    /**
     * Dissocie une ressource naturelle d'un sentier
     */
    public function removeResourceFromTrail($trailId, $resourceId) {
        if (($check = $this->requireMethod('DELETE')) !== true) return $check;
        
        try {
            $ok = $this->trail->removeNaturalResource($trailId, $resourceId);
            return $this->response($ok, $ok ? 'Ressource retirée du sentier.' : 'Erreur lors du retrait.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }
}
