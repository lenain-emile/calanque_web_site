<?php

namespace App\Controllers;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Reservation;
use App\Models\User;
use App\Config\StripeConfig;
use Exception;

class PaymentController extends BaseController {
    private $payment;
    private $subscription;
    private $reservation;
    private $user;
    private $stripe;
    private $initError;

    public function __construct() {
        $this->payment = new Payment();
        $this->subscription = new Subscription();
        $this->reservation = new Reservation();
        $this->user = new User();
        try {
            $this->stripe = StripeConfig::getStripeClient();
        } catch (\Throwable $e) {
            $this->stripe = null;
            $this->initError = $e->getMessage();
        }
    }

    // --- PAIEMENTS D'ABONNEMENT ---

    /**
     * Crée un PaymentIntent pour un abonnement
     */
    public function createSubscriptionPayment() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;

        $data = $this->input();
        $userId = $data['user_id'] ?? null;
        $subscriptionId = $data['subscription_id'] ?? null;
        $amount = $data['amount'] ?? null;

        if (!$userId || !$subscriptionId || !$amount) {
            return $this->response(false, 'Tous les champs sont obligatoires.');
        }

        try {
            // Vérifier que l'abonnement existe
            $subscription = $this->subscription->getById($subscriptionId);
            if (!$subscription) {
                return $this->response(false, 'Abonnement introuvable.');
            }

            // Vérifier que l'utilisateur existe
            $user = $this->user->getById($userId);
            if (!$user) {
                return $this->response(false, 'Utilisateur introuvable.');
            }

            // Créer ou récupérer le client Stripe
            $stripeCustomer = $this->getOrCreateStripeCustomer($user);

            // Créer le PaymentIntent
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amount * 100, // Convertir en centimes
                'currency' => StripeConfig::CURRENCY,
                'customer' => $stripeCustomer->id,
                'metadata' => [
                    'subscription_id' => $subscriptionId,
                    'user_id' => $userId,
                    'payment_type' => 'subscription'
                ]
            ]);

            // Enregistrer le paiement en base
            $paymentData = [
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_customer_id' => $stripeCustomer->id,
                'subscription_id' => $subscriptionId,
                'amount' => $amount,
                'status' => 'PENDING',
                'method' => 'stripe',
                'payment_type' => 'subscription',
                'payment_date' => date('Y-m-d'),
                'stripe_metadata' => [
                    'subscription_id' => $subscriptionId,
                    'user_id' => $userId,
                    'payment_type' => 'subscription'
                ]
            ];

            $this->payment->create($paymentData);

            return $this->response(true, 'PaymentIntent créé avec succès.', [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id
            ]);

        } catch (\Throwable $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Crée un PaymentIntent pour une réservation
     */
    public function createReservationPayment() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;

        $data = $this->input();
        $userId = $data['user_id'] ?? null;
        $reservationId = $data['reservation_id'] ?? null;
        $amount = $data['amount'] ?? null;

        if (!$userId || !$reservationId || !$amount) {
            return $this->response(false, 'Tous les champs sont obligatoires.');
        }

        try {
            if (!$this->stripe) {
                return $this->response(false, 'Stripe non initialisé: ' . ($this->initError ?: 'clé manquante'));
            }
            // Vérifier que la réservation existe
            $reservation = $this->reservation->getById($reservationId);
            if (!$reservation) {
                return $this->response(false, 'Réservation introuvable.');
            }

            // Vérifier que l'utilisateur existe
            $user = $this->user->getById($userId);
            if (!$user) {
                return $this->response(false, 'Utilisateur introuvable.');
            }

            // Créer ou récupérer le client Stripe
            $stripeCustomer = $this->getOrCreateStripeCustomer($user);

            // Créer le PaymentIntent
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amount * 100, // Convertir en centimes
                'currency' => StripeConfig::CURRENCY,
                'customer' => $stripeCustomer->id,
                'metadata' => [
                    'reservation_id' => $reservationId,
                    'user_id' => $userId,
                    'payment_type' => 'reservation'
                ]
            ]);

            // Enregistrer le paiement en base
            $paymentData = [
                'stripe_payment_intent_id' => $paymentIntent->id,
                'stripe_customer_id' => $stripeCustomer->id,
                'reservation_id' => $reservationId,
                'amount' => $amount,
                'status' => 'PENDING',
                'method' => 'stripe',
                'payment_type' => 'reservation',
                'payment_date' => date('Y-m-d'),
                'stripe_metadata' => [
                    'reservation_id' => $reservationId,
                    'user_id' => $userId,
                    'payment_type' => 'reservation'
                ]
            ];

            $this->payment->create($paymentData);

            return $this->response(true, 'PaymentIntent créé avec succès.', [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id
            ]);

        } catch (\Throwable $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Confirme un paiement après succès côté client
     */
    public function confirmPayment() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;

        $data = $this->input();
        $paymentIntentId = $data['payment_intent_id'] ?? null;

        if (!$paymentIntentId) {
            return $this->response(false, 'ID du PaymentIntent requis.');
        }

        try {
            // Récupérer le PaymentIntent depuis Stripe
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            if ($paymentIntent->status === 'succeeded') {
                // Mettre à jour le statut en base
                $payment = $this->payment->getByStripePaymentIntentId($paymentIntentId);
                if ($payment) {
                    $this->payment->updateStatus($payment['id'], 'SUCCESS', [
                        'stripe_status' => $paymentIntent->status,
                        'confirmed_at' => date('Y-m-d H:i:s')
                    ]);

                    // Mettre à jour le statut de l'abonnement ou de la réservation
                    $this->updateRelatedEntityStatus($payment, 'paid');

                    return $this->response(true, 'Paiement confirmé avec succès.', [
                        'payment_id' => $payment['id'],
                        'status' => 'SUCCESS'
                    ]);
                }
            }

            return $this->response(false, 'Paiement non confirmé.');

        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Crée une session Stripe Checkout pour une réservation
     */
    public function createReservationCheckoutSession() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;

        $data = $this->input();
        $userId = $data['user_id'] ?? null;
        $reservationId = $data['reservation_id'] ?? null;
        $amount = $data['amount'] ?? null; // en euros

        if (!$userId || !$reservationId || !$amount) {
            return $this->response(false, 'Tous les champs sont obligatoires.');
        }

        try {
            if (!$this->stripe) {
                return $this->response(false, 'Stripe non initialisé: ' . ($this->initError ?: 'clé manquante'));
            }
            // Vérification réservation et utilisateur
            $reservation = $this->reservation->getById($reservationId);
            if (!$reservation) return $this->response(false, 'Réservation introuvable.');
            $user = $this->user->getById($userId);
            if (!$user) return $this->response(false, 'Utilisateur introuvable.');

            // Client Stripe
            $stripeCustomer = $this->getOrCreateStripeCustomer($user);

            // Création session Checkout
            $successUrl = \App\Config\StripeConfig::SUCCESS_URL . '?status=success&reservation_id=' . $reservationId . '&session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = \App\Config\StripeConfig::CANCEL_URL . '?status=cancel';
            $session = $this->stripe->checkout->sessions->create([
                'mode' => 'payment',
                'customer' => $stripeCustomer->id,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => StripeConfig::CURRENCY,
                        'product_data' => [
                            'name' => 'Réservation camping #' . $reservationId
                        ],
                        'unit_amount' => (int) round($amount * 100)
                    ],
                    'quantity' => 1
                ]],
                'metadata' => [
                    'reservation_id' => $reservationId,
                    'user_id' => $userId,
                    'payment_type' => 'reservation'
                ],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl
            ]);

            // Enregistrer le paiement en base (PENDING)
            $paymentData = [
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
                'stripe_customer_id' => $stripeCustomer->id,
                'reservation_id' => $reservationId,
                'amount' => $amount,
                'status' => 'PENDING',
                'method' => 'stripe',
                'payment_type' => 'reservation',
                'payment_date' => date('Y-m-d'),
                'stripe_metadata' => [
                    'checkout_session_id' => $session->id,
                    'reservation_id' => $reservationId,
                    'user_id' => $userId
                ]
            ];
            $this->payment->create($paymentData);

            return $this->response(true, 'Session Checkout créée.', [
                'checkout_url' => $session->url,
                'session_id' => $session->id
            ]);

        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Confirme une session Stripe Checkout (sans webhook)
     */
    public function confirmCheckoutSession() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;

        $data = $this->input();
        $sessionId = $data['session_id'] ?? null;

        if (!$sessionId) {
            return $this->response(false, 'session_id requis');
        }

        try {
            if (!$this->stripe) {
                return $this->response(false, 'Stripe non initialisé: ' . ($this->initError ?: 'clé manquante'));
            }

            $session = $this->stripe->checkout->sessions->retrieve($sessionId);
            if (!$session || $session->status !== 'complete') {
                return $this->response(false, 'Session non complétée');
            }

            $paymentIntentId = $session->payment_intent;
            $reservationId = $session->metadata->reservation_id ?? null;
            $userId = $session->metadata->user_id ?? null;

            // Récupérer le paiement en base via la réservation
            $payments = $this->payment->getByReservationId($reservationId);
            if (!$payments || count($payments) === 0) {
                return $this->response(false, 'Paiement introuvable pour la réservation');
            }
            $payment = $payments[0];

            // Mettre à jour les infos Stripe
            $this->payment->updateStripeData($payment['id'], [
                'stripe_payment_intent_id' => $paymentIntentId,
                'stripe_customer_id' => $session->customer,
                'stripe_metadata' => [
                    'checkout_session_id' => $sessionId,
                    'confirmed_at' => date('Y-m-d H:i:s')
                ]
            ]);

            // Valider le PaymentIntent et MAJ statuts
            $pi = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            if ($pi && $pi->status === 'succeeded') {
                $this->payment->updateStatus($payment['id'], 'SUCCESS', [
                    'stripe_status' => $pi->status
                ]);
                $this->updateRelatedEntityStatus($payment, 'paid');
                return $this->response(true, 'Paiement confirmé avec succès.', [
                    'payment_id' => $payment['id'],
                    'status' => 'SUCCESS'
                ]);
            }

            return $this->response(false, 'Paiement non confirmé');

        } catch (\Throwable $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    // --- ABONNEMENTS RÉCURRENTS ---

    /**
     * Crée un abonnement récurrent avec Stripe
     */
    public function createRecurringSubscription() {
        if (($check = $this->requireMethod('POST')) !== true) return $check;

        $data = $this->input();
        $userId = $data['user_id'] ?? null;
        $priceId = $data['price_id'] ?? null; // ID du prix Stripe
        $subscriptionType = $data['subscription_type'] ?? null;

        if (!$userId || !$priceId || !$subscriptionType) {
            return $this->response(false, 'Tous les champs sont obligatoires.');
        }

        try {
            // Vérifier que l'utilisateur existe
            $user = $this->user->getById($userId);
            if (!$user) {
                return $this->response(false, 'Utilisateur introuvable.');
            }

            // Créer ou récupérer le client Stripe
            $stripeCustomer = $this->getOrCreateStripeCustomer($user);

            // Créer l'abonnement Stripe
            $stripeSubscription = $this->stripe->subscriptions->create([
                'customer' => $stripeCustomer->id,
                'items' => [
                    ['price' => $priceId]
                ],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                'expand' => ['latest_invoice.payment_intent']
            ]);

            // Calculer les dates
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d', strtotime('+1 month')); // Par défaut 1 mois

            // Créer l'abonnement en base
            $subscriptionCreated = $this->subscription->create(
                $userId,
                $subscriptionType,
                $startDate,
                $endDate,
                'ACTIVE'
            );

            if ($subscriptionCreated) {
                $subscriptionId = $this->subscription->connect->lastInsertId();
                
                // Enregistrer le paiement
                $paymentData = [
                    'stripe_subscription_id' => $stripeSubscription->id,
                    'stripe_customer_id' => $stripeCustomer->id,
                    'subscription_id' => $subscriptionId,
                    'amount' => $stripeSubscription->items->data[0]->price->unit_amount / 100,
                    'status' => 'PENDING',
                    'method' => 'stripe',
                    'payment_type' => 'subscription',
                    'payment_date' => date('Y-m-d'),
                    'stripe_metadata' => [
                        'subscription_id' => $subscriptionId,
                        'user_id' => $userId,
                        'stripe_subscription_id' => $stripeSubscription->id
                    ]
                ];

                $this->payment->create($paymentData);

                return $this->response(true, 'Abonnement récurrent créé.', [
                    'subscription_id' => $subscriptionId,
                    'stripe_subscription_id' => $stripeSubscription->id,
                    'client_secret' => $stripeSubscription->latest_invoice->payment_intent->client_secret
                ]);
            }

            return $this->response(false, 'Erreur lors de la création de l\'abonnement.');

        } catch (Exception $e) {
            return $this->response(false, 'Erreur: ' . $e->getMessage());
        }
    }

    // --- MÉTHODES UTILITAIRES ---

    /**
     * Récupère ou crée un client Stripe
     */
    private function getOrCreateStripeCustomer($user) {
        try {
            // Chercher un client existant par email
            $customers = $this->stripe->customers->all([
                'email' => $user['email'],
                'limit' => 1
            ]);

            if (!empty($customers->data)) {
                return $customers->data[0];
            }

            // Créer un nouveau client
            return $this->stripe->customers->create([
                'email' => $user['email'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'metadata' => [
                    'user_id' => $user['id']
                ]
            ]);

        } catch (Exception $e) {
            throw new Exception('Erreur lors de la création du client Stripe: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour le statut de l'entité liée (abonnement ou réservation)
     */
    private function updateRelatedEntityStatus($payment, $status) {
        if ($payment['subscription_id']) {
            // Mettre à jour le statut de l'abonnement
            $this->subscription->updateStatus($payment['subscription_id'], $status);
        } elseif ($payment['reservation_id']) {
            // Mettre à jour le statut de la réservation
            $this->reservation->updateStatus($payment['reservation_id'], $status);
        }
    }

    // --- GESTION DES PAIEMENTS ---

    public function getPaymentById($id) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        $payment = $this->payment->getById($id);
        return $payment ? $this->response(true, 'Paiement trouvé.', $payment)
                        : $this->response(false, 'Paiement introuvable.');
    }

    public function getPaymentsByUser($userId) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        $payments = $this->payment->getByUserId($userId);
        return $this->response(true, 'Paiements récupérés.', $payments);
    }

    public function getAllPayments() {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        $payments = $this->payment->getAll();
        return $this->response(true, 'Liste des paiements.', $payments);
    }

    public function getPaymentsByStatus($status) {
        if (($check = $this->requireMethod('GET')) !== true) return $check;
        $payments = $this->payment->getByStatus($status);
        return $this->response(true, "Paiements avec statut: $status", $payments);
    }
}
