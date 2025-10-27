-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 22 oct. 2025 à 17:28
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bddcalanque`
--

-- --------------------------------------------------------

--
-- Structure de la table `campings`
--

DROP TABLE IF EXISTS `campings`;
CREATE TABLE IF NOT EXISTS `campings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `capacity` int NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `campings`
--

INSERT INTO `campings` (`id`, `name`, `capacity`, `description`) VALUES
(1, 'Camping du Soleil', 100, 'Camping familial avec piscine et activités');

-- --------------------------------------------------------

--
-- Structure de la table `natural_resources`
--

DROP TABLE IF EXISTS `natural_resources`;
CREATE TABLE IF NOT EXISTS `natural_resources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `natural_resources`
--

INSERT INTO `natural_resources` (`id`, `name`, `type`, `description`) VALUES
(1, 'Pins d’Alep', 'Flore', 'Espèce de pin typique des calanques, adaptée aux sols secs et rocheux.'),
(2, 'Chênes kermès', 'Flore', 'Petit chêne méditerranéen résistant à la sécheresse.'),
(3, 'Crête de calcaire', 'Géologie', 'Roches calcaires caractéristiques qui forment les falaises et calanques.'),
(4, 'Faucon pèlerin', 'Faune', 'Oiseau de proie qui niche dans les falaises du parc.'),
(5, 'Mouette rieuse', 'Faune', 'Oiseau marin fréquentant les criques et falaises.'),
(6, 'Posidonie oceanica', 'Flore aquatique', 'Plante marine endémique de la Méditerranée, protégée.'),
(7, 'Chêne vert', 'Flore', 'Arbre méditerranéen robuste, très présent dans le parc.'),
(8, 'Garrigue méditerranéenne', 'Écosystème', 'Formation végétale basse et dense typique des zones rocailleuses.'),
(9, 'Cap Canaille', 'Géologie', 'Falaises emblématiques de calcaire et grès surplombant la mer.'),
(10, 'Crabe de méditerranée', 'Faune', 'Crustacé fréquent dans les zones rocheuses côtières.');

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `stripe_payment_intent_id` varchar(255) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `subscription_id` int DEFAULT NULL,
  `reservation_id` int DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `method` varchar(50) NOT NULL,
  `payment_type` enum('subscription','reservation','one_time') NOT NULL DEFAULT 'one_time',
  `payment_date` date NOT NULL,
  `stripe_metadata` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_payments_subscriptions` (`subscription_id`),
  KEY `idx_stripe_payment_intent` (`stripe_payment_intent_id`(250)),
  KEY `idx_stripe_customer` (`stripe_customer_id`(250)),
  KEY `idx_stripe_subscription` (`stripe_subscription_id`(250)),
  KEY `idx_payment_type` (`payment_type`),
  KEY `idx_reservation_id` (`reservation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `payments`
--

INSERT INTO `payments` (`id`, `stripe_payment_intent_id`, `stripe_customer_id`, `stripe_subscription_id`, `subscription_id`, `reservation_id`, `amount`, `status`, `method`, `payment_type`, `payment_date`, `stripe_metadata`, `created_at`, `updated_at`) VALUES
(1, 'pi_test_68d5b769ec959', 'cus_test_68d5b769ec96c', NULL, 3, NULL, 29.99, 'SUCCESS', 'stripe', 'subscription', '2025-09-25', '{\"processed_at\": \"2025-09-25 21:43:05\", \"stripe_status\": \"succeeded\"}', '2025-09-25 21:43:05', '2025-09-25 21:43:05'),
(2, 'pi_test_68d5b9bde4658', 'cus_test_68d5b9bde465f', NULL, 4, NULL, 29.99, 'SUCCESS', 'stripe', 'subscription', '2025-09-25', '{\"processed_at\": \"2025-09-25 21:53:01\", \"stripe_status\": \"succeeded\"}', '2025-09-25 21:53:01', '2025-09-25 21:53:01'),
(3, 'pi_3SBN6gLK6FvkX8oc1ay8PUun', 'cus_T7cLe3MPVanRiB', NULL, 5, NULL, 29.99, 'SUCCESS', 'stripe', 'subscription', '2025-09-25', '{\"processed_at\": \"2025-09-25 21:53:40\", \"stripe_status\": \"succeeded\"}', '2025-09-25 21:53:40', '2025-09-25 21:53:40'),
(4, 'pi_test_68dea96f6471c', 'cus_test_68dea96f64721', NULL, NULL, 19, 100.00, 'PENDING', 'stripe', 'reservation', '2025-10-02', '{\"user_id\": 10, \"payment_type\": \"reservation\", \"reservation_id\": \"19\"}', '2025-10-02 16:33:51', NULL),
(5, 'pi_test_68dea983b8924', 'cus_test_68dea983b892d', NULL, NULL, 20, 100.00, 'PENDING', 'stripe', 'reservation', '2025-10-02', '{\"user_id\": 10, \"payment_type\": \"reservation\", \"reservation_id\": \"20\"}', '2025-10-02 16:34:11', NULL),
(6, 'pi_test_68dea9db17c22', 'cus_test_68dea9db17c27', NULL, NULL, 21, 125.00, 'PENDING', 'stripe', 'reservation', '2025-10-02', '{\"user_id\": 10, \"payment_type\": \"reservation\", \"reservation_id\": \"21\"}', '2025-10-02 16:35:39', NULL),
(7, NULL, 'cus_TC2ZURiD0AMjIq', NULL, NULL, 62, 25.00, 'PENDING', 'stripe', 'reservation', '2025-10-07', '{\"user_id\": 10, \"reservation_id\": \"62\", \"checkout_session_id\": \"cs_test_a130dqNXfSDutXQgJ2FYlaUJga5NuIA9c2AyVRMqo9xteqYBhXcGymu5Oe\"}', '2025-10-07 17:15:57', NULL),
(8, NULL, 'cus_TC2ZURiD0AMjIq', NULL, NULL, 63, 50.00, 'PENDING', 'stripe', 'reservation', '2025-10-07', '{\"user_id\": 10, \"reservation_id\": \"63\", \"checkout_session_id\": \"cs_test_a11Pq9Hs0CHFql4e3Rc1TbpESkvYbgZnUiRbnC19WQimuedaHPW6qAu49t\"}', '2025-10-07 18:01:45', NULL),
(9, NULL, 'cus_TC2ZURiD0AMjIq', NULL, NULL, 64, 75.00, 'PENDING', 'stripe', 'reservation', '2025-10-07', '{\"user_id\": 10, \"reservation_id\": \"64\", \"checkout_session_id\": \"cs_test_a10GICpVCPTcu4YFR7J961E9xRRUxLLvAcdHVzuBkHXyiPJbXODDv5LZPE\"}', '2025-10-07 18:13:27', NULL),
(10, NULL, 'cus_TC2ZURiD0AMjIq', NULL, NULL, 67, 50.00, 'PENDING', 'stripe', 'reservation', '2025-10-07', '{\"user_id\": 10, \"reservation_id\": \"67\", \"checkout_session_id\": \"cs_test_a1dTa0ZcM0bS0TPlScicz2T3b5Gp3thqAFQFnYuRDbvDCGLlbDCLt6zAqS\"}', '2025-10-07 18:35:36', NULL),
(11, 'pi_3SFfurLK6FvkX8oc15xBe9mH', 'cus_TC2ZURiD0AMjIq', NULL, NULL, 68, 4250.00, 'SUCCESS', 'stripe', 'reservation', '2025-10-07', '{\"stripe_status\": \"succeeded\"}', '2025-10-07 18:46:32', '2025-10-07 19:11:08'),
(12, 'pi_3SFgYZLK6FvkX8oc02Cz6kS1', 'cus_TC2ZURiD0AMjIq', NULL, NULL, 69, 50.00, 'SUCCESS', 'stripe', 'reservation', '2025-10-07', '{\"stripe_status\": \"succeeded\"}', '2025-10-07 19:27:48', '2025-10-07 19:50:05'),
(13, NULL, 'cus_TC2ZURiD0AMjIq', NULL, NULL, 70, 75.00, 'PENDING', 'stripe', 'reservation', '2025-10-22', '{\"user_id\": 10, \"reservation_id\": \"70\", \"checkout_session_id\": \"cs_test_a1bZObyHliLwYJZHNNiELD0SMlCavYFwoMe0y12jIu2OL7jfEFKH6bx1GE\"}', '2025-10-22 14:25:37', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `camping_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `num_people` int NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PENDING',
  `reservation_name` varchar(200) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_reservations_users` (`user_id`),
  KEY `fk_reservations_campings` (`camping_id`),
  KEY `idx_payment_status` (`payment_status`)
) ENGINE=MyISAM AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `camping_id`, `start_date`, `end_date`, `num_people`, `status`, `reservation_name`, `amount`, `payment_status`, `created_at`) VALUES
(68, 10, 1, '2025-10-08', '2025-11-11', 5, 'paid', 'undefined undefined', 4250.00, 'pending', '2025-10-07 18:46:31'),
(70, 10, 1, '2025-10-23', '2025-10-24', 3, 'PENDING', 'test', 75.00, 'pending', '2025-10-22 14:25:28'),
(69, 10, 1, '2025-11-11', '2025-11-12', 2, 'paid', 'undefined undefined', 50.00, 'pending', '2025-10-07 19:27:46');

-- --------------------------------------------------------

--
-- Structure de la table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `type` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
  `stripe_subscription_id` varchar(255) DEFAULT NULL,
  `stripe_customer_id` varchar(255) DEFAULT NULL,
  `price_id` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_subscriptions_users` (`user_id`),
  KEY `idx_stripe_subscription_id` (`stripe_subscription_id`(250)),
  KEY `idx_stripe_customer_sub` (`stripe_customer_id`(250))
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `type`, `start_date`, `end_date`, `status`, `stripe_subscription_id`, `stripe_customer_id`, `price_id`, `amount`, `created_at`) VALUES
(1, 10, 'BASIC', '2025-09-25', '2025-10-25', 'ACTIVE', NULL, NULL, NULL, NULL, '2025-09-25 21:42:04'),
(2, 12, 'BASIC', '2025-09-25', '2025-10-25', 'ACTIVE', NULL, NULL, NULL, NULL, '2025-09-25 21:42:19'),
(3, 13, 'BASIC', '2025-09-25', '2025-10-25', 'ACTIVE', NULL, NULL, NULL, NULL, '2025-09-25 21:43:05'),
(4, 14, 'BASIC', '2025-09-25', '2025-10-25', 'ACTIVE', NULL, NULL, NULL, NULL, '2025-09-25 21:53:01'),
(5, 15, 'BASIC', '2025-09-25', '2025-10-25', 'ACTIVE', NULL, NULL, NULL, NULL, '2025-09-25 21:53:38');

-- --------------------------------------------------------

--
-- Structure de la table `trails`
--

DROP TABLE IF EXISTS `trails`;
CREATE TABLE IF NOT EXISTS `trails` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text,
  `difficulty` smallint DEFAULT NULL,
  `length_km` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `path_locations` json DEFAULT NULL COMMENT 'Stocke les coordonnées GPS du sentier au format GeoJSON',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `trails`
--

INSERT INTO `trails` (`id`, `name`, `description`, `difficulty`, `length_km`, `created_at`, `path_locations`) VALUES
(1, 'Sentier Luminy – Belvédère de Sugiton', 'Randonnée depuis Le Redon / Luminy jusqu\'au belvédère de Sugiton. Magnifiques vues sur les Calanques, les falaises et la mer Méditerranée.', 2, 6.50, '2025-10-22 16:57:11', '{\"type\": \"LineString\", \"coordinates\": [[5.435921, 43.233584], [5.437, 43.2325], [5.4382, 43.2312], [5.4395, 43.2298], [5.441, 43.2285], [5.4425, 43.2272], [5.444, 43.2258], [5.446, 43.2238], [5.453, 43.2145], [5.455583, 43.21048]]}');

-- --------------------------------------------------------

--
-- Structure de la table `trails_natural_resources`
--

DROP TABLE IF EXISTS `trails_natural_resources`;
CREATE TABLE IF NOT EXISTS `trails_natural_resources` (
  `trail_id` int NOT NULL,
  `resource_id` int NOT NULL,
  PRIMARY KEY (`trail_id`,`resource_id`),
  KEY `fk_tnr_resources` (`resource_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `verification_token` varchar(255) DEFAULT NULL,
  `role_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_users_roles` (`role_id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password_hash`, `email_verified`, `verification_token`, `role_id`, `created_at`) VALUES
(10, 'Emile', 'Lenain', 'lenain.emile@gmail.com', '$2y$10$KJ8cfU47R3XoD4KRAaSgieX5TlQD4NylGePaYm5u7nlxq2zzW7u9O', 0, NULL, 1, '2025-09-22 16:41:08'),
(2, 'Jma', 'Dupgraent', 'jean.dupont@verifst.com', '$2y$10$haHuhmBOtuho1z91gjgRreE330LQcHACUNfNBS0zQNzE81J6.7ldu', 0, NULL, 1, '2025-09-15 21:42:14'),
(3, 'PLSWORK', 'noice', 'broIDK@example.com', '$2y$10$jpok3GJSYjJBwQepgMdSs.X4zZePlGYL8KyBVKUiIMZLnD14EIFTC', 0, NULL, 1, '2025-09-18 15:16:02'),
(9, 'Claire', 'Martin', 'lolola@gmail.com', '$2y$10$rLfuHLyCFyOszUhWc9o5WeJYxN3G4Lj37Doy0uT2B5tqrdmD5Jb1C', 0, NULL, 1, '2025-09-22 12:43:40'),
(11, 'alexis', 'nguembibibinananana', 'nguem@gmail.com', '$2y$10$An4XPOMGEesoM/I5C2dgPe.daAwDnmu70kDKGdTu6v/7Ny2W6yE42', 0, NULL, 1, '2025-09-22 16:51:40'),
(16, 'Test', 'User', 'test@example.com', '$2y$10$xQg0lEO.Lw5aYQ6abo1vqOLPQihhH0C7HvyA6S1QpDS60JcJz4PZO', 0, NULL, 1, '2025-09-30 11:35:00'),
(17, 'Emile', 'Lenain', 'lenain@gmail.com', '$2y$10$PGKJv/cNKgTTEN7Pq68Oh.LrYstBDN.5QCUf54f5s7zkor6AjvHRW', 0, NULL, 1, '2025-09-30 11:47:29'),
(18, 'Emile', 'Lenain', 'lenain.emile@gmail.co', '$2y$10$ag74AdFVbwXUAV34vdnomusUTmR9yNNqpTRSqxVKGt5EPbHAUfrMi', 0, NULL, 1, '2025-10-20 20:28:05');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
