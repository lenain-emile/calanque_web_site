<?php

namespace App\Config;

class StripeConfig {
    // Clés Stripe - MUST be configured via environment variables (.env file)
    // NEVER commit real API keys to Git!
    const STRIPE_PUBLISHABLE_KEY = ''; // Set via STRIPE_PUBLISHABLE env variable
    const STRIPE_SECRET_KEY = '';      // Set via STRIPE_SECRET env variable
    const STRIPE_WEBHOOK_SECRET = '';  // Set via STRIPE_WEBHOOK_SECRET env variable
    
    // Configuration des devises
    const CURRENCY = 'eur';
    
    // URLs de redirection (frontend)
    const SUCCESS_URL = 'http://localhost:5173/dashboard';
    const CANCEL_URL = 'http://localhost:5173/';
    
    public static function getStripeClient() {
        $secretKey = getenv('STRIPE_SECRET');
        if (!$secretKey) {
            throw new \Exception('Stripe secret key missing. Please set STRIPE_SECRET environment variable.');
        }
        if (!class_exists('Stripe\\Stripe') || !class_exists('Stripe\\StripeClient')) {
            throw new \Exception('Stripe library not found. Run "composer install" in backend/ directory.');
        }
        \Stripe\Stripe::setApiKey($secretKey);
        return new \Stripe\StripeClient($secretKey);
    }
    
    public static function getPublishableKey() {
        $key = getenv('STRIPE_PUBLISHABLE');
        if (!$key) {
            throw new \Exception('Stripe publishable key missing. Please set STRIPE_PUBLISHABLE environment variable.');
        }
        return $key;
    }
    
    public static function getWebhookSecret() {
        return getenv('STRIPE_WEBHOOK_SECRET') ?: '';
    }
}


