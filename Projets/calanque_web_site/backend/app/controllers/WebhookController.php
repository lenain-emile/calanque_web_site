<?php

namespace App\Controllers;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\Reservation;
use App\Config\StripeConfig;
use Exception;

class WebhookController {
    private $payment;
    private $subscription;
    private $reservation;

    public function __construct() {
        $this->payment = new Payment();
        $this->subscription = new Subscription();
        $this->reservation = new Reservation();
    }

    /**
     * Traite les webhooks Stripe
     */
    public function handleStripeWebhook() {
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $endpoint_secret = StripeConfig::STRIPE_WEBHOOK_SECRET;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Payload invalide
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Signature invalide
            http_response_code(400);
            exit();
        }

        // Traiter l'événement
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;
            
            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;
            
            case 'invoice.payment_succeeded':
                $this->handleInvoicePaymentSucceeded($event->data->object);
                break;
            
            case 'invoice.payment_failed':
                $this->handleInvoicePaymentFailed($event->data->object);
                break;
            
            case 'customer.subscription.created':
                $this->handleSubscriptionCreated($event->data->object);
                break;
            
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdated($event->data->object);
                break;
            
            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;
            
            default:
                // Événement non géré
                error_log('Événement Stripe non géré: ' . $event->type);
        }

        http_response_code(200);
    }

    /**
     * Gère le succès d'un PaymentIntent
     */
    private function handlePaymentIntentSucceeded($paymentIntent) {
        try {
            $payment = $this->payment->getByStripePaymentIntentId($paymentIntent->id);
            
            if ($payment) {
                // Mettre à jour le statut du paiement
                $this->payment->updateStatus($payment['id'], 'SUCCESS', [
                    'stripe_status' => $paymentIntent->status,
                    'processed_at' => date('Y-m-d H:i:s')
                ]);

                // Mettre à jour l'entité liée
                $this->updateRelatedEntityStatus($payment, 'paid');

                error_log("Paiement réussi: {$paymentIntent->id}");
            }
        } catch (Exception $e) {
            error_log("Erreur lors du traitement du paiement réussi: " . $e->getMessage());
        }
    }

    /**
     * Gère l'échec d'un PaymentIntent
     */
    private function handlePaymentIntentFailed($paymentIntent) {
        try {
            $payment = $this->payment->getByStripePaymentIntentId($paymentIntent->id);
            
            if ($payment) {
                // Mettre à jour le statut du paiement
                $this->payment->updateStatus($payment['id'], 'FAILED', [
                    'stripe_status' => $paymentIntent->status,
                    'failure_reason' => $paymentIntent->last_payment_error->message ?? 'Raison inconnue',
                    'failed_at' => date('Y-m-d H:i:s')
                ]);

                // Mettre à jour l'entité liée
                $this->updateRelatedEntityStatus($payment, 'failed');

                error_log("Paiement échoué: {$paymentIntent->id}");
            }
        } catch (Exception $e) {
            error_log("Erreur lors du traitement du paiement échoué: " . $e->getMessage());
        }
    }

    /**
     * Gère le succès d'un paiement d'abonnement
     */
    private function handleInvoicePaymentSucceeded($invoice) {
        try {
            if ($invoice->subscription) {
                $subscription = $this->subscription->getByStripeSubscriptionId($invoice->subscription);
                
                if ($subscription) {
                    // Créer un enregistrement de paiement pour l'abonnement
                    $paymentData = [
                        'stripe_subscription_id' => $invoice->subscription,
                        'stripe_customer_id' => $invoice->customer,
                        'subscription_id' => $subscription['id'],
                        'amount' => $invoice->amount_paid / 100,
                        'status' => 'SUCCESS',
                        'method' => 'stripe',
                        'payment_type' => 'subscription',
                        'payment_date' => date('Y-m-d', $invoice->created),
                        'stripe_metadata' => [
                            'invoice_id' => $invoice->id,
                            'subscription_id' => $subscription['id']
                        ]
                    ];

                    $this->payment->create($paymentData);

                    // Renouveler l'abonnement
                    $newEndDate = date('Y-m-d', strtotime('+1 month'));
                    $this->subscription->renew($subscription['id'], $newEndDate);

                    error_log("Paiement d'abonnement réussi: {$invoice->id}");
                }
            }
        } catch (Exception $e) {
            error_log("Erreur lors du traitement du paiement d'abonnement: " . $e->getMessage());
        }
    }

    /**
     * Gère l'échec d'un paiement d'abonnement
     */
    private function handleInvoicePaymentFailed($invoice) {
        try {
            if ($invoice->subscription) {
                $subscription = $this->subscription->getByStripeSubscriptionId($invoice->subscription);
                
                if ($subscription) {
                    // Créer un enregistrement de paiement échoué
                    $paymentData = [
                        'stripe_subscription_id' => $invoice->subscription,
                        'stripe_customer_id' => $invoice->customer,
                        'subscription_id' => $subscription['id'],
                        'amount' => $invoice->amount_due / 100,
                        'status' => 'FAILED',
                        'method' => 'stripe',
                        'payment_type' => 'subscription',
                        'payment_date' => date('Y-m-d', $invoice->created),
                        'stripe_metadata' => [
                            'invoice_id' => $invoice->id,
                            'subscription_id' => $subscription['id'],
                            'failure_reason' => 'Paiement d\'abonnement échoué'
                        ]
                    ];

                    $this->payment->create($paymentData);

                    // Suspendre l'abonnement
                    $this->subscription->suspend($subscription['id']);

                    error_log("Paiement d'abonnement échoué: {$invoice->id}");
                }
            }
        } catch (Exception $e) {
            error_log("Erreur lors du traitement de l'échec de paiement d'abonnement: " . $e->getMessage());
        }
    }

    /**
     * Gère la création d'un abonnement
     */
    private function handleSubscriptionCreated($stripeSubscription) {
        try {
            // L'abonnement a déjà été créé en base lors de la création
            // On peut juste logger l'événement
            error_log("Abonnement Stripe créé: {$stripeSubscription->id}");
        } catch (Exception $e) {
            error_log("Erreur lors du traitement de la création d'abonnement: " . $e->getMessage());
        }
    }

    /**
     * Gère la mise à jour d'un abonnement
     */
    private function handleSubscriptionUpdated($stripeSubscription) {
        try {
            $subscription = $this->subscription->getByStripeSubscriptionId($stripeSubscription->id);
            
            if ($subscription) {
                // Mettre à jour le statut selon Stripe
                $status = $this->mapStripeStatusToLocal($stripeSubscription->status);
                $this->subscription->updateStatus($subscription['id'], $status);

                error_log("Abonnement mis à jour: {$stripeSubscription->id} - Statut: {$status}");
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la mise à jour d'abonnement: " . $e->getMessage());
        }
    }

    /**
     * Gère la suppression d'un abonnement
     */
    private function handleSubscriptionDeleted($stripeSubscription) {
        try {
            $subscription = $this->subscription->getByStripeSubscriptionId($stripeSubscription->id);
            
            if ($subscription) {
                // Annuler l'abonnement
                $this->subscription->cancel($subscription['id']);

                error_log("Abonnement annulé: {$stripeSubscription->id}");
            }
        } catch (Exception $e) {
            error_log("Erreur lors de l'annulation d'abonnement: " . $e->getMessage());
        }
    }

    /**
     * Met à jour le statut de l'entité liée
     */
    private function updateRelatedEntityStatus($payment, $status) {
        try {
            if ($payment['subscription_id']) {
                $this->subscription->updateStatus($payment['subscription_id'], $status);
            } elseif ($payment['reservation_id']) {
                $this->reservation->updateStatus($payment['reservation_id'], $status);
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la mise à jour du statut de l'entité: " . $e->getMessage());
        }
    }

    /**
     * Mappe les statuts Stripe vers les statuts locaux
     */
    private function mapStripeStatusToLocal($stripeStatus) {
        switch ($stripeStatus) {
            case 'active':
                return 'ACTIVE';
            case 'past_due':
                return 'PAST_DUE';
            case 'canceled':
                return 'CANCELLED';
            case 'unpaid':
                return 'SUSPENDED';
            default:
                return 'ACTIVE';
        }
    }
}
