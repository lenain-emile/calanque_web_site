<?php

namespace App\Config;

class StripeConfig {
    // Clés Stripe - À configurer via variables d'environnement ou fichier sécurisé
    const STRIPE_PUBLISHABLE_KEY = '';
    const STRIPE_SECRET_KEY = '';
    const STRIPE_WEBHOOK_SECRET = '';
    
    // Configuration des devises
    const CURRENCY = 'eur';
    
    // URLs de redirection
    const SUCCESS_URL = 'http://localhost:8000/payment/success';
    const CANCEL_URL = 'http://localhost:8000/payment/cancel';
    
    public static function getStripeClient() {
        $secretKey = getenv('STRIPE_SECRET') ?: self::STRIPE_SECRET_KEY;
        \Stripe\Stripe::setApiKey($secretKey);
        return new \Stripe\StripeClient($secretKey);
    }
    
    public static function getPublishableKey() {
        return getenv('STRIPE_PUBLISHABLE') ?: self::STRIPE_PUBLISHABLE_KEY;
    }
    
    public static function getWebhookSecret() {
        return getenv('STRIPE_WEBHOOK_SECRET') ?: self::STRIPE_WEBHOOK_SECRET;
    }
}
