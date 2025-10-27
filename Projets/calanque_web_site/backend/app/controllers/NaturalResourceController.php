<?php

namespace App\Controllers;
use App\Models\NaturalResource;
use Exception;

class NaturalResourceController extends BaseController {
    private $resource;

    public function __construct() {
        $this->resource = new NaturalResource();
    }

    /**
     * Récupère toutes les ressources naturelles
     */
    public function getAllResources() {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        
        try {
            $resources = $this->resource->getAll();
            return $this->response(true, 'Liste des ressources naturelles récupérée.', $resources);
        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère une ressource par son ID
     */
    public function getResourceById($id) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        
        try {
            $resource = $this->resource->getById($id);
            if ($resource) {
                return $this->response(true, 'Ressource trouvée.', $resource);
            } else {
                return $this->response(false, 'Ressource introuvable.');
            }
        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les ressources par type
     */
    public function getResourcesByType($type) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        
        try {
            $resources = $this->resource->getByType($type);
            return $this->response(true, 'Ressources du type ' . $type . ' récupérées.', $resources);
        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Crée une nouvelle ressource naturelle
     */
    public function createResource() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;
        
        $data = $this->input();
        $name = $data['name'] ?? null;
        $type = $data['type'] ?? null;
        $description = $data['description'] ?? null;
        
        if (!$name || !$type) {
            return $this->response(false, 'Nom et type sont obligatoires.');
        }
        
        try {
            $ok = $this->resource->create($name, $type, $description);
            return $this->response($ok, $ok ? 'Ressource créée avec succès.' : 'Erreur lors de la création.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    /**
     * Met à jour une ressource naturelle
     */
    public function updateResource($id) {
        if (($check = $this->requireMethod('PUT')) !== true) return $check;
        
        $data = $this->input();
        $name = $data['name'] ?? null;
        $type = $data['type'] ?? null;
        $description = $data['description'] ?? null;
        
        if (!$name || !$type) {
            return $this->response(false, 'Nom et type sont obligatoires.');
        }
        
        try {
            $ok = $this->resource->update($id, $name, $type, $description);
            return $this->response($ok, $ok ? 'Ressource mise à jour avec succès.' : 'Erreur lors de la mise à jour.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }

    /**
     * Supprime une ressource naturelle
     */
    public function deleteResource($id) {
        if (($check = $this->requireMethod('DELETE')) !== true) return $check;
        
        try {
            $ok = $this->resource->delete($id);
            return $this->response($ok, $ok ? 'Ressource supprimée avec succès.' : 'Erreur lors de la suppression.');
        } catch (Exception $e) {
            return $this->response(false, $e->getMessage());
        }
    }
}
