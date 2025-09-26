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