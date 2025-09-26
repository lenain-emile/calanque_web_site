-- Mise à jour de la table payments pour Stripe
ALTER TABLE `payments` 
ADD COLUMN `stripe_payment_intent_id` VARCHAR(255) NULL AFTER `id`,
ADD COLUMN `stripe_customer_id` VARCHAR(255) NULL AFTER `stripe_payment_intent_id`,
ADD COLUMN `stripe_subscription_id` VARCHAR(255) NULL AFTER `stripe_customer_id`,
ADD COLUMN `payment_type` ENUM('subscription', 'reservation', 'one_time') NOT NULL DEFAULT 'one_time' AFTER `method`,
ADD COLUMN `reservation_id` INT NULL AFTER `subscription_id`,
ADD COLUMN `stripe_metadata` JSON NULL AFTER `payment_date`,
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- Ajouter les index pour les nouvelles colonnes
ALTER TABLE `payments` 
ADD INDEX `idx_stripe_payment_intent` (`stripe_payment_intent_id`),
ADD INDEX `idx_stripe_customer` (`stripe_customer_id`),
ADD INDEX `idx_stripe_subscription` (`stripe_subscription_id`),
ADD INDEX `idx_payment_type` (`payment_type`),
ADD INDEX `idx_reservation_id` (`reservation_id`);

-- Ajouter une clé étrangère pour reservation_id
ALTER TABLE `payments` 
ADD CONSTRAINT `fk_payments_reservations` 
FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`id`) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Mettre à jour la table subscriptions pour Stripe
ALTER TABLE `subscriptions` 
ADD COLUMN `stripe_subscription_id` VARCHAR(255) NULL AFTER `status`,
ADD COLUMN `stripe_customer_id` VARCHAR(255) NULL AFTER `stripe_subscription_id`,
ADD COLUMN `price_id` VARCHAR(255) NULL AFTER `stripe_customer_id`,
ADD COLUMN `amount` DECIMAL(10,2) NULL AFTER `price_id`;

-- Ajouter les index pour les nouvelles colonnes subscriptions
ALTER TABLE `subscriptions` 
ADD INDEX `idx_stripe_subscription_id` (`stripe_subscription_id`),
ADD INDEX `idx_stripe_customer_sub` (`stripe_customer_id`);

-- Mettre à jour la table reservations pour les paiements
ALTER TABLE `reservations` 
ADD COLUMN `amount` DECIMAL(10,2) NULL AFTER `reservation_name`,
ADD COLUMN `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending' AFTER `amount`;

-- Ajouter l'index pour payment_status
ALTER TABLE `reservations` 
ADD INDEX `idx_payment_status` (`payment_status`);
