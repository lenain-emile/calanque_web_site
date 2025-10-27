<?php
// Démarrage automatique de la session pour toutes les requêtes
session_start();

// Headers CORS pour React
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:5173'); // URL de ton app React
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Chargement de l'autoloader
require_once '../app/config/autoloader.php';

use App\Controllers\UserController;
use App\Controllers\SubscriptionController;
use App\Controllers\ReservationController;
use App\Controllers\PaymentController;
use App\Controllers\WebhookController;
use App\Controllers\CampingController;
use App\Controllers\TrailController;
use App\Controllers\NaturalResourceController;

// Routage simple basé sur l'URL
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Suppression du chemin de base si nécessaire
// Ajuste ce chemin selon ta configuration Apache
$basePath = '';
if (strpos($path, '/Projets/calanque_web_site/backend/public') === 0) {
    $basePath = '/Projets/calanque_web_site/backend/public';
} elseif (strpos($path, '/calanque_web_site/backend/public') === 0) {
    $basePath = '/calanque_web_site/backend/public';
} elseif (strpos($path, '/backend/public') === 0) {
    $basePath = '/backend/public';
}
$path = str_replace($basePath, '', $path);

// Si le chemin est vide, on met '/'
if (empty($path)) {
    $path = '/';
}

// Routage
switch ($path) {
    case '/debug':
        echo json_encode([
            'REQUEST_URI' => $_SERVER['REQUEST_URI'],
            'path' => $path,
            'method' => $method,
            'basePath' => $basePath ?? 'none'
        ]);
        break;
        
    case '/api/users':
        $userController = new UserController();
        switch ($method) {
            case 'GET':
                echo json_encode($userController->getAllUsers());
                break;
            case 'POST':
                echo json_encode($userController->createUser());
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
        }
        break;
        
    case '/api/users/login':
        if ($method === 'POST') {
            $userController = new UserController();
            echo json_encode($userController->loginUser());
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
        }
        break;
        
    case '/api/users/logout':
        if ($method === 'POST') {
            $userController = new UserController();
            echo json_encode($userController->logoutUser());
        } else {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
        }
        break;
        
    default:
        // Gestion des routes dynamiques comme /api/users/{id}
        if (preg_match('/^\/api\/users\/(\d+)$/', $path, $matches)) {
            $userId = $matches[1];
            $userController = new UserController();
            
            switch ($method) {
                case 'GET':
                    echo json_encode($userController->getUserById($userId));
                    break;
                case 'PUT':
                    echo json_encode($userController->updateUser($userId));
                    break;
                case 'DELETE':
                    echo json_encode($userController->deleteUser($userId));
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Routes pour les abonnements généraux
        elseif (preg_match('/^\/api\/subscriptions$/', $path)) {
            $subscriptionController = new SubscriptionController();
            switch ($method) {
                case 'GET':
                    echo json_encode($subscriptionController->getAllSubscriptions());
                    break;
                case 'POST':
                    echo json_encode($subscriptionController->createSubscription());
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Routes pour un abonnement spécifique
        elseif (preg_match('/^\/api\/subscriptions\/(\d+)$/', $path, $matches)) {
            $subscriptionId = $matches[1];
            $subscriptionController = new SubscriptionController();
            
            switch ($method) {
                case 'GET':
                    echo json_encode($subscriptionController->getSubscriptionById($subscriptionId));
                    break;
                case 'PUT':
                    echo json_encode($subscriptionController->updateSubscription($subscriptionId));
                    break;
                case 'DELETE':
                    echo json_encode($subscriptionController->deleteSubscription($subscriptionId));
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Routes pour les actions sur les abonnements
        elseif (preg_match('/^\/api\/subscriptions\/(\d+)\/(cancel|renew|suspend)$/', $path, $matches)) {
            $subscriptionId = $matches[1];
            $action = $matches[2];
            $subscriptionController = new SubscriptionController();
            
            if ($method === 'POST') {
                switch ($action) {
                    case 'cancel':
                        echo json_encode($subscriptionController->cancelSubscription($subscriptionId));
                        break;
                    case 'renew':
                        echo json_encode($subscriptionController->renewSubscription($subscriptionId));
                        break;
                    case 'suspend':
                        echo json_encode($subscriptionController->suspendSubscription($subscriptionId));
                        break;
                }
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Routes pour les abonnements d'un utilisateur
        elseif (preg_match('/^\/api\/users\/(\d+)\/subscriptions$/', $path, $matches)) {
            $userId = $matches[1];
            $subscriptionController = new SubscriptionController();
            
            if ($method === 'GET') {
                echo json_encode($subscriptionController->getUserSubscriptions($userId));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Route pour l'abonnement actif d'un utilisateur
        elseif (preg_match('/^\/api\/users\/(\d+)\/subscription\/active$/', $path, $matches)) {
            $userId = $matches[1];
            $subscriptionController = new SubscriptionController();
            
            if ($method === 'GET') {
                echo json_encode($subscriptionController->getActiveUserSubscription($userId));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Routes pour les réservations générales
        elseif (preg_match('/^\/api\/reservations$/', $path)) {
            $reservationController = new ReservationController();
            switch ($method) {
                case 'GET':
                    echo json_encode($reservationController->getAllReservations());
                    break;
                case 'POST':
                    echo json_encode($reservationController->createReservation());
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Routes pour une réservation spécifique
        elseif (preg_match('/^\/api\/reservations\/(\d+)$/', $path, $matches)) {
            $reservationId = $matches[1];
            $reservationController = new ReservationController();
            
            switch ($method) {
                case 'GET':
                    echo json_encode($reservationController->getReservationById($reservationId));
                    break;
                case 'PUT':
                    echo json_encode($reservationController->updateReservationStatus($reservationId));
                    break;
                case 'DELETE':
                    echo json_encode($reservationController->deleteReservation($reservationId));
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Routes pour les réservations d'un utilisateur
        elseif (preg_match('/^\/api\/users\/(\d+)\/reservations$/', $path, $matches)) {
            $userId = $matches[1];
            $reservationController = new ReservationController();
            
            if ($method === 'GET') {
                echo json_encode($reservationController->getReservationsByUser($userId));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Route pour vérifier la capacité d'un camping
        elseif (preg_match('/^\/api\/reservations\/check-capacity$/', $path)) {
            $reservationController = new ReservationController();
            
            if ($method === 'GET') {
                echo json_encode($reservationController->checkCapacity());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Route pour récupérer les informations de capacité d'un camping
        elseif (preg_match('/^\/api\/reservations\/camping-capacity$/', $path)) {
            $reservationController = new ReservationController();
            
            if ($method === 'GET') {
                echo json_encode($reservationController->getCampingCapacityInfo());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }

        // Route pour récupérer les réservations d'un utilisateur
        elseif (preg_match('/^\/api\/reservations\/user\/(\d+)$/', $path, $matches)) {
            $userId = $matches[1];
            $reservationController = new ReservationController();
            
            if ($method === 'GET') {
                echo json_encode($reservationController->getReservationsByUser($userId));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // === ROUTES CAMPINGS ===
        
        // Routes pour les campings généraux
        elseif (preg_match('/^\/api\/campings$/', $path)) {
            $campingController = new CampingController();
            switch ($method) {
                case 'GET':
                    echo json_encode($campingController->getAllCampings());
                    break;
                case 'POST':
                    echo json_encode($campingController->createCamping());
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Routes pour un camping spécifique
        elseif (preg_match('/^\/api\/campings\/(\d+)$/', $path, $matches)) {
            $campingId = $matches[1];
            $campingController = new CampingController();
            
            switch ($method) {
                case 'GET':
                    echo json_encode($campingController->getCampingById($campingId));
                    break;
                case 'PUT':
                    echo json_encode($campingController->updateCamping($campingId));
                    break;
                case 'DELETE':
                    echo json_encode($campingController->deleteCamping($campingId));
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Route pour récupérer les campings avec disponibilité
        elseif (preg_match('/^\/api\/campings\/availability$/', $path)) {
            $campingController = new CampingController();
            
            if ($method === 'GET') {
                echo json_encode($campingController->getCampingsWithAvailability());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Route pour vérifier la disponibilité d'un camping (utilise ReservationController)
        elseif (preg_match('/^\/api\/campings\/check-availability$/', $path)) {
            $reservationController = new ReservationController();
            
            if ($method === 'GET') {
                echo json_encode($reservationController->checkCapacity());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // === ROUTES TRAILS (SENTIERS) ===
        
        // Routes pour les sentiers généraux
        elseif (preg_match('/^\/api\/trails$/', $path)) {
            $trailController = new TrailController();
            switch ($method) {
                case 'GET':
                    echo json_encode($trailController->getAllTrails());
                    break;
                case 'POST':
                    echo json_encode($trailController->createTrail());
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Routes pour un sentier spécifique
        elseif (preg_match('/^\/api\/trails\/(\d+)$/', $path, $matches)) {
            $trailId = $matches[1];
            $trailController = new TrailController();
            
            switch ($method) {
                case 'GET':
                    echo json_encode($trailController->getTrailById($trailId));
                    break;
                case 'PUT':
                    echo json_encode($trailController->updateTrail($trailId));
                    break;
                case 'DELETE':
                    echo json_encode($trailController->deleteTrail($trailId));
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Route pour récupérer les ressources d'un sentier
        elseif (preg_match('/^\/api\/trails\/(\d+)\/resources$/', $path, $matches)) {
            $trailId = $matches[1];
            $trailController = new TrailController();
            
            if ($method === 'GET') {
                echo json_encode($trailController->getTrailResources($trailId));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Route pour ajouter une ressource à un sentier
        elseif (preg_match('/^\/api\/trails\/(\d+)\/resources\/(\d+)$/', $path, $matches)) {
            $trailId = $matches[1];
            $resourceId = $matches[2];
            $trailController = new TrailController();
            
            switch ($method) {
                case 'POST':
                    echo json_encode($trailController->addResourceToTrail($trailId, $resourceId));
                    break;
                case 'DELETE':
                    echo json_encode($trailController->removeResourceFromTrail($trailId, $resourceId));
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // === ROUTES NATURAL RESOURCES (RESSOURCES NATURELLES) ===
        
        // Routes pour les ressources naturelles générales
        elseif (preg_match('/^\/api\/natural-resources$/', $path)) {
            $resourceController = new NaturalResourceController();
            switch ($method) {
                case 'GET':
                    echo json_encode($resourceController->getAllResources());
                    break;
                case 'POST':
                    echo json_encode($resourceController->createResource());
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Route pour récupérer les ressources par type
        elseif (preg_match('/^\/api\/natural-resources\/type\/(\w+)$/', $path, $matches)) {
            $type = $matches[1];
            $resourceController = new NaturalResourceController();
            
            if ($method === 'GET') {
                echo json_encode($resourceController->getResourcesByType($type));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Routes pour une ressource naturelle spécifique
        elseif (preg_match('/^\/api\/natural-resources\/(\d+)$/', $path, $matches)) {
            $resourceId = $matches[1];
            $resourceController = new NaturalResourceController();
            
            switch ($method) {
                case 'GET':
                    echo json_encode($resourceController->getResourceById($resourceId));
                    break;
                case 'PUT':
                    echo json_encode($resourceController->updateResource($resourceId));
                    break;
                case 'DELETE':
                    echo json_encode($resourceController->deleteResource($resourceId));
                    break;
                default:
                    http_response_code(405);
                    echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // === ROUTES STRIPE - PAIEMENTS D'ABONNEMENT ===
        
        // Créer un abonnement avec paiement
        elseif (preg_match('/^\/api\/subscription\/payment\/create$/', $path)) {
            $subscriptionController = new SubscriptionController();
            
            if ($method === 'POST') {
                echo json_encode($subscriptionController->createSubscriptionWithPayment());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Créer un PaymentIntent pour abonnement
        elseif (preg_match('/^\/api\/subscription\/payment\/stripe$/', $path)) {
            $paymentController = new PaymentController();
            
            if ($method === 'POST') {
                echo json_encode($paymentController->createSubscriptionPayment());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Créer un abonnement récurrent
        elseif (preg_match('/^\/api\/subscription\/recurring\/create$/', $path)) {
            $paymentController = new PaymentController();
            
            if ($method === 'POST') {
                echo json_encode($paymentController->createRecurringSubscription());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Récupérer les abonnements avec paiements
        elseif (preg_match('/^\/api\/subscription\/payments$/', $path)) {
            $subscriptionController = new SubscriptionController();
            
            if ($method === 'GET') {
                $userId = $_GET['user_id'] ?? null;
                echo json_encode($subscriptionController->getSubscriptionsWithPayments($userId));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // === ROUTES STRIPE - PAIEMENTS DE RÉSERVATION ===
        
        // Créer une réservation avec paiement
        elseif (preg_match('/^\/api\/reservation\/payment\/create$/', $path)) {
            $reservationController = new ReservationController();
            
            if ($method === 'POST') {
                echo json_encode($reservationController->createReservationWithPayment());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Créer une réservation pour Stripe Checkout (retourne reservation_id)
        elseif (preg_match('/^\/api\/reservation\/checkout\/create$/', $path)) {
            $reservationController = new ReservationController();
            
            if ($method === 'POST') {
                echo json_encode($reservationController->createReservationForCheckout());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Créer un PaymentIntent pour réservation
        elseif (preg_match('/^\/api\/reservation\/payment\/stripe$/', $path)) {
            $paymentController = new PaymentController();
            
            if ($method === 'POST') {
                echo json_encode($paymentController->createReservationPayment());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Créer une session Stripe Checkout pour réservation
        elseif (preg_match('/^\/api\/reservation\/checkout\/session$/', $path)) {
            $paymentController = new PaymentController();
            
            if ($method === 'POST') {
                echo json_encode($paymentController->createReservationCheckoutSession());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        // Confirmer une session Stripe Checkout (fallback sans webhook)
        elseif (preg_match('/^\/api\/reservation\/checkout\/confirm$/', $path)) {
            $paymentController = new PaymentController();
            
            if ($method === 'POST') {
                echo json_encode($paymentController->confirmCheckoutSession());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Récupérer les réservations avec paiements
        elseif (preg_match('/^\/api\/reservation\/payments$/', $path)) {
            $reservationController = new ReservationController();
            
            if ($method === 'GET') {
                $userId = $_GET['user_id'] ?? null;
                echo json_encode($reservationController->getReservationsWithPayments($userId));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Récupérer les réservations par statut de paiement
        elseif (preg_match('/^\/api\/reservation\/payments\/status$/', $path)) {
            $reservationController = new ReservationController();
            
            if ($method === 'GET') {
                echo json_encode($reservationController->getReservationsByPaymentStatus());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Mettre à jour le statut de paiement d'une réservation
        elseif (preg_match('/^\/api\/reservation\/(\d+)\/payment-status$/', $path, $matches)) {
            $reservationId = $matches[1];
            $reservationController = new ReservationController();
            
            if ($method === 'PUT') {
                echo json_encode($reservationController->updateReservationPaymentStatus($reservationId));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // === ROUTES STRIPE - GESTION DES PAIEMENTS ===
        
        // Confirmer un paiement
        elseif (preg_match('/^\/api\/payment\/confirm$/', $path)) {
            $paymentController = new PaymentController();
            
            if ($method === 'POST') {
                echo json_encode($paymentController->confirmPayment());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Récupérer tous les paiements
        elseif (preg_match('/^\/api\/payments$/', $path)) {
            $paymentController = new PaymentController();
            
            if ($method === 'GET') {
                echo json_encode($paymentController->getAllPayments());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Récupérer les paiements d'un utilisateur
        elseif (preg_match('/^\/api\/payments\/user\/(\d+)$/', $path, $matches)) {
            $userId = $matches[1];
            $paymentController = new PaymentController();
            
            if ($method === 'GET') {
                echo json_encode($paymentController->getPaymentsByUser($userId));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Récupérer les paiements par statut
        elseif (preg_match('/^\/api\/payments\/status\/(\w+)$/', $path, $matches)) {
            $status = $matches[1];
            $paymentController = new PaymentController();
            
            if ($method === 'GET') {
                echo json_encode($paymentController->getPaymentsByStatus($status));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Récupérer un paiement par ID
        elseif (preg_match('/^\/api\/payment\/(\d+)$/', $path, $matches)) {
            $paymentId = $matches[1];
            $paymentController = new PaymentController();
            
            if ($method === 'GET') {
                echo json_encode($paymentController->getPaymentById($paymentId));
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // === ROUTES STRIPE - WEBHOOKS ===
        
        // Webhook Stripe
        elseif (preg_match('/^\/api\/webhook\/stripe$/', $path)) {
            $webhookController = new WebhookController();
            
            if ($method === 'POST') {
                echo json_encode($webhookController->handleStripeWebhook());
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // === ROUTES STRIPE - REDIRECTIONS ===
        
        // Page de succès de paiement
        elseif (preg_match('/^\/payment\/success$/', $path)) {
            if ($method === 'GET') {
                echo json_encode(['success' => true, 'message' => 'Paiement réussi']);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        // Page d'annulation de paiement
        elseif (preg_match('/^\/payment\/cancel$/', $path)) {
            if ($method === 'GET') {
                echo json_encode(['success' => false, 'message' => 'Paiement annulé']);
            } else {
                http_response_code(405);
                echo json_encode(['error' => 'Méthode non autorisée']);
            }
        }
        
        else {
            http_response_code(404);
            echo json_encode(['error' => 'Route non trouvée']);
        }
}
?>