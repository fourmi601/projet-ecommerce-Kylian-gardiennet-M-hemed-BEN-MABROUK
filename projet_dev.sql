-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 17 avr. 2026 à 10:35
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projet_dev`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

CREATE TABLE `avis` (
  `id_avis` int(11) NOT NULL,
  `id_jeu` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `note` int(11) NOT NULL,
  `commentaire` text NOT NULL,
  `date_avis` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id_avis`, `id_jeu`, `id_user`, `note`, `commentaire`, `date_avis`) VALUES
(1, 2, 7, 1, 'Nullllllll', '2026-04-15 14:30:07'),
(2, 5, 7, 5, 'Incroyable', '2026-04-15 15:20:41');

-- --------------------------------------------------------

--
-- Structure de la table `bibliotheque`
--

CREATE TABLE `bibliotheque` (
  `id_user` int(11) NOT NULL,
  `id_jeu` int(11) NOT NULL,
  `cle_cd` varchar(20) NOT NULL,
  `date_activation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id_cat` int(11) NOT NULL,
  `nom_cat` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id_cat`, `nom_cat`, `description`) VALUES
(1, 'FPS', 'Jeux à la première personne'),
(2, 'Sport', 'Jeux de sport'),
(3, 'Histoire', 'Jeux historiques'),
(4, 'Aventure', 'Jeux d\'exploration'),
(5, 'Sandbox', NULL),
(6, 'Action', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `code_promo`
--

CREATE TABLE `code_promo` (
  `id_promo` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `reduction_pourcentage` int(11) NOT NULL,
  `est_actif` tinyint(1) DEFAULT 1,
  `date_expiration` date DEFAULT NULL,
  `max_utilisations` int(11) DEFAULT 100,
  `nb_utilisations` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `code_promo`
--

INSERT INTO `code_promo` (`id_promo`, `code`, `reduction_pourcentage`, `est_actif`, `date_expiration`, `max_utilisations`, `nb_utilisations`) VALUES
(7, 'TEST', 1, 1, '2027-10-10', 1, 0);

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

CREATE TABLE `commande` (
  `id_commande` int(11) NOT NULL,
  `date_achat` datetime DEFAULT NULL,
  `prix_total` decimal(10,2) DEFAULT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commande`
--

INSERT INTO `commande` (`id_commande`, `date_achat`, `prix_total`, `id_user`) VALUES
(1, '2026-04-08 13:52:30', 29.99, 1),
(2, '2026-04-08 13:54:31', 29.99, 1),
(3, '2026-04-08 14:08:12', 29.99, 1),
(4, '2026-04-08 14:34:23', 76.98, 1),
(5, NULL, 34.99, 6),
(6, NULL, 34.99, 6),
(7, NULL, 34.99, 6),
(8, NULL, 34.99, 6),
(9, NULL, 34.99, 6),
(10, NULL, 34.99, 6),
(11, NULL, 34.99, 6),
(12, NULL, 34.99, 6),
(13, NULL, 34.99, 6),
(14, NULL, 34.99, 6),
(15, NULL, 34.99, 6),
(16, '2026-04-12 19:20:27', 34.99, 6),
(17, NULL, 20.00, 6),
(18, '2026-04-12 19:29:20', 20.00, 6),
(19, NULL, 29.99, 6),
(20, '2026-04-12 19:29:55', 29.99, 6),
(21, NULL, 41.99, 6),
(22, '2026-04-12 19:30:13', 41.99, 6),
(23, '2026-04-13 16:13:29', 64.98, 6),
(24, '2026-04-13 16:58:31', 148.96, 6),
(25, '2026-04-13 17:00:36', 148.96, 6),
(26, '2026-04-13 17:04:23', 29.99, 6),
(27, '2026-04-13 17:05:28', 41.99, 6);

-- --------------------------------------------------------

--
-- Structure de la table `contenir`
--

CREATE TABLE `contenir` (
  `id_jeu` int(11) NOT NULL,
  `id_commande` int(11) NOT NULL,
  `prix_achat` decimal(10,2) DEFAULT NULL,
  `cle_cd` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contenir`
--

INSERT INTO `contenir` (`id_jeu`, `id_commande`, `prix_achat`, `cle_cd`) VALUES
(1, 4, 41.99, '3AB7-F05D-5033'),
(1, 22, 41.99, 'A6CE-6BF4-718D'),
(1, 24, 41.99, '32E7-A024-8231'),
(1, 25, 41.99, 'BC36-3135-D6AD'),
(1, 27, 41.99, '3D36-7124-88CE'),
(2, 4, 34.99, '7701-6E6B-CE64'),
(2, 16, 34.99, 'E455-CC89-5086'),
(2, 23, 34.99, 'AB66-A19B-E7CF'),
(3, 1, 29.99, '693E-2F9E-D1E0'),
(3, 2, 29.99, 'DF8E-A360-C020'),
(3, 3, 29.99, '9ED4-8AA9-CB8C'),
(3, 20, 29.99, '31E6-EE8E-7375'),
(3, 23, 29.99, 'C9A8-ABEB-0F1A'),
(3, 26, 29.99, '0452-8D46-0C46'),
(4, 18, 20.00, '7898-3E38-04E9');

-- --------------------------------------------------------

--
-- Structure de la table `historique_ventes`
--

CREATE TABLE `historique_ventes` (
  `id_vente` int(11) NOT NULL,
  `id_jeu` int(11) NOT NULL,
  `prix_paye` decimal(10,2) NOT NULL,
  `date_vente` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `historique_ventes`
--

INSERT INTO `historique_ventes` (`id_vente`, `id_jeu`, `prix_paye`, `date_vente`) VALUES
(1, 1, 41.99, '2026-04-06 10:32:24'),
(2, 2, 34.99, '2026-04-08 10:32:24'),
(3, 2, 34.99, '2026-03-20 10:32:24'),
(4, 3, 29.99, '2026-03-20 10:32:24'),
(5, 3, 29.99, '2026-04-08 10:32:24'),
(6, 3, 29.99, '2026-04-03 10:32:24'),
(7, 3, 29.99, '2026-04-11 10:32:24'),
(8, 4, 20.00, '2026-03-28 10:32:24'),
(9, 4, 20.00, '2026-04-06 10:32:24'),
(10, 4, 20.00, '2026-04-05 10:32:24'),
(11, 4, 20.00, '2026-04-12 10:32:24'),
(12, 4, 20.00, '2026-04-11 10:32:24'),
(13, 4, 20.00, '2026-04-02 10:32:24'),
(14, 5, 69.99, '2026-04-05 10:32:24'),
(15, 5, 69.99, '2026-04-16 10:32:24'),
(16, 5, 69.99, '2026-03-24 10:32:24'),
(17, 5, 69.99, '2026-04-02 10:32:24'),
(18, 5, 69.99, '2026-03-22 10:32:24'),
(19, 5, 69.99, '2026-04-13 10:32:24'),
(20, 5, 69.99, '2026-04-07 10:32:24'),
(21, 5, 69.99, '2026-04-14 10:32:24'),
(22, 5, 69.99, '2026-03-20 10:32:24'),
(23, 5, 69.99, '2026-03-30 10:32:24'),
(24, 5, 69.99, '2026-04-17 10:32:24'),
(25, 5, 69.99, '2026-04-13 10:32:24'),
(26, 5, 69.99, '2026-03-22 10:32:24'),
(27, 5, 69.99, '2026-04-08 10:32:24'),
(28, 5, 69.99, '2026-03-25 10:32:24'),
(29, 5, 69.99, '2026-04-02 10:32:24'),
(30, 5, 69.99, '2026-03-23 10:32:24'),
(31, 5, 69.99, '2026-03-25 10:32:24'),
(32, 5, 69.99, '2026-03-20 10:32:24'),
(33, 5, 69.99, '2026-03-27 10:32:24'),
(34, 5, 69.99, '2026-04-13 10:32:24'),
(35, 5, 69.99, '2026-04-11 10:32:24'),
(36, 5, 69.99, '2026-04-05 10:32:24'),
(37, 5, 69.99, '2026-03-23 10:32:24'),
(38, 5, 69.99, '2026-04-01 10:32:24'),
(39, 5, 69.99, '2026-04-05 10:32:24'),
(40, 5, 69.99, '2026-04-13 10:32:24'),
(41, 5, 69.99, '2026-03-30 10:32:24'),
(42, 5, 69.99, '2026-04-08 10:32:24'),
(43, 5, 69.99, '2026-04-07 10:32:24'),
(44, 5, 69.99, '2026-04-03 10:32:24'),
(45, 6, 0.00, '2026-03-20 10:32:24'),
(46, 6, 0.00, '2026-03-21 10:32:24'),
(47, 6, 0.00, '2026-03-26 10:32:24'),
(48, 6, 0.00, '2026-04-13 10:32:24'),
(49, 7, 59.99, '2026-04-03 10:32:24');

-- --------------------------------------------------------

--
-- Structure de la table `jeu`
--

CREATE TABLE `jeu` (
  `id_jeu` int(11) NOT NULL,
  `titre` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `note` int(11) DEFAULT NULL,
  `id_cat` int(11) NOT NULL,
  `id_vendeur` int(11) DEFAULT NULL,
  `prix_solde` decimal(10,2) DEFAULT 0.00,
  `date_sortie` datetime DEFAULT NULL,
  `note_steam` int(11) DEFAULT NULL,
  `id_steam` int(11) DEFAULT NULL,
  `ventes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `jeu`
--

INSERT INTO `jeu` (`id_jeu`, `titre`, `description`, `prix`, `image`, `note`, `id_cat`, `id_vendeur`, `prix_solde`, `date_sortie`, `note_steam`, `id_steam`, `ventes`) VALUES
(1, 'Elden Ring', 'Plongez dans l\'Entre-Terre et devenez le Seigneur d\'Elden.', 41.99, 'elden.jpg', 10, 4, NULL, 0.00, '2022-02-25 14:40:00', 93, 1245620, 1),
(2, 'EA Sports FC 24', 'Simulation de football avec plus de 19 000 joueurs.', 34.99, 'fc24.jpg', 8, 2, NULL, 0.00, NULL, 46, 3405690, 2),
(3, 'Cyberpunk 2077', 'Un jeu de rôle d\'action en monde ouvert dans Night City.', 29.99, 'cyberpunk.jpg', 9, 1, NULL, 0.00, '2020-12-10 14:43:45', 86, 1091500, 4),
(4, 'Minecraft', 'Minecraft est un jeu vidéo de type aventure « bac à sable » développé par le Suédois Markus Persson, alias Notch, puis par la société Mojang Studios.', 20.00, 'minecraft.jpg', NULL, 5, NULL, 0.00, NULL, NULL, NULL, 6),
(5, 'Crimson Desert', 'Crimson Desert est un jeu d\'action-aventure en monde ouvert sur le continent de Pywel. Accompagnez Kliff pour rebâtir les Crinières Grises et sauver ces terres d\'une menace grandissante. Des étendues sauvages aux ruines et au mystérieux Abysse, forgez votre voie entre combats et découvertes.', 69.99, 'steam_3321460.jpg', NULL, 4, NULL, 0.00, NULL, NULL, 3321460, 31),
(6, 'Grand Theft Auto V Version originale', 'Grand Theft Auto V sur PC offre aux joueurs la possibilité d\'explorer le monde de Los Santos et Blaine County en haute résolution (jusqu\'à 4K) et à 60 images par seconde.', 0.00, 'steam_271590.jpg', NULL, 6, NULL, 0.00, NULL, NULL, 271590, 4),
(7, 'PRAGMATA', 'Pragmata est un jeu d\'action-aventure de science-fiction développé par Capcom. Membre d\'une équipe envoyée sur la Lune, Hugh rencontre Diana, une jeune androïde. Ensemble, ils parcourront une station lunaire dirigée par une IA malfaisante, afin de trouver un moyen de retourner sur Terre.', 59.99, 'steam_3357650.jpg', NULL, 6, NULL, 0.00, '2026-04-17 12:00:00', NULL, 3357650, 1),
(8, 'Little Nightmares VR: Altered Echoes', 'Little Nightmares VR: Altered Echoes est un jeu d\'énigme et d\'aventure dans lequel vous incarnez Dark Six, une mystérieuse silhouette de petite fille. Explorez un monde angoissant, résolvez des énigmes complexes et échappez à de terrifiants géants. Votre quête : redevenir vous-même.', 0.00, 'steam_2482940.jpg', NULL, 6, NULL, 0.00, '2026-04-23 00:00:00', NULL, 2482940, 0);

-- --------------------------------------------------------

--
-- Structure de la table `jeu_plateforme`
--

CREATE TABLE `jeu_plateforme` (
  `id_jeu` int(11) NOT NULL,
  `id_plateforme` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `jeu_plateforme`
--

INSERT INTO `jeu_plateforme` (`id_jeu`, `id_plateforme`) VALUES
(1, 1),
(1, 2),
(1, 3),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(3, 1),
(3, 2),
(3, 3),
(3, 4),
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(5, 1),
(5, 2),
(5, 3),
(6, 1),
(6, 2),
(6, 3),
(7, 1),
(8, 1);

-- --------------------------------------------------------

--
-- Structure de la table `plateforme`
--

CREATE TABLE `plateforme` (
  `id_plateforme` int(11) NOT NULL,
  `nom_plateforme` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `plateforme`
--

INSERT INTO `plateforme` (`id_plateforme`, `nom_plateforme`) VALUES
(1, 'PC'),
(2, 'PS5'),
(3, 'Xbox'),
(4, 'Nintendo Switch');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_user` int(11) NOT NULL,
  `pseudo` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_user`, `pseudo`, `email`, `password`, `role`) VALUES
(1, 'Admin1', 'admin@digitalgames.fr', 'admin123', 'admin'),
(6, 'kylian', 'kylian@test.test', '$2y$10$FjBBOmX7rF5ktLnWCD1.feWdEjy8SCHEySFjnOZGBAdm2F7zprXLe', 'client'),
(7, 'admin', 'admin1@digitalgames.fr', '$2y$10$VEStpZVS48kWk4d4WqhrcOQ3E9FP4hrPBRHOeKF01XSvj0XdSh.Zu', 'admin');

-- --------------------------------------------------------

--
-- Structure de la table `wishlist`
--

CREATE TABLE `wishlist` (
  `id_user` int(11) NOT NULL,
  `id_jeu` int(11) NOT NULL,
  `date_ajout` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `wishlist`
--

INSERT INTO `wishlist` (`id_user`, `id_jeu`, `date_ajout`) VALUES
(1, 3, '2026-04-08 15:52:41'),
(7, 2, '2026-04-15 15:46:06'),
(7, 7, '2026-04-17 09:50:24');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `avis`
--
ALTER TABLE `avis`
  ADD PRIMARY KEY (`id_avis`),
  ADD KEY `id_jeu` (`id_jeu`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `bibliotheque`
--
ALTER TABLE `bibliotheque`
  ADD PRIMARY KEY (`cle_cd`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_jeu` (`id_jeu`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_cat`);

--
-- Index pour la table `code_promo`
--
ALTER TABLE `code_promo`
  ADD PRIMARY KEY (`id_promo`);

--
-- Index pour la table `commande`
--
ALTER TABLE `commande`
  ADD PRIMARY KEY (`id_commande`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `contenir`
--
ALTER TABLE `contenir`
  ADD PRIMARY KEY (`id_jeu`,`id_commande`),
  ADD KEY `id_commande` (`id_commande`);

--
-- Index pour la table `historique_ventes`
--
ALTER TABLE `historique_ventes`
  ADD PRIMARY KEY (`id_vente`),
  ADD KEY `id_jeu` (`id_jeu`);

--
-- Index pour la table `jeu`
--
ALTER TABLE `jeu`
  ADD PRIMARY KEY (`id_jeu`),
  ADD KEY `id_cat` (`id_cat`),
  ADD KEY `fk_vendeur` (`id_vendeur`);

--
-- Index pour la table `jeu_plateforme`
--
ALTER TABLE `jeu_plateforme`
  ADD PRIMARY KEY (`id_jeu`,`id_plateforme`),
  ADD KEY `id_plateforme` (`id_plateforme`);

--
-- Index pour la table `plateforme`
--
ALTER TABLE `plateforme`
  ADD PRIMARY KEY (`id_plateforme`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_user`);

--
-- Index pour la table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id_user`,`id_jeu`),
  ADD KEY `id_jeu` (`id_jeu`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `avis`
--
ALTER TABLE `avis`
  MODIFY `id_avis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id_cat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `code_promo`
--
ALTER TABLE `code_promo`
  MODIFY `id_promo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `commande`
--
ALTER TABLE `commande`
  MODIFY `id_commande` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `historique_ventes`
--
ALTER TABLE `historique_ventes`
  MODIFY `id_vente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT pour la table `jeu`
--
ALTER TABLE `jeu`
  MODIFY `id_jeu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `plateforme`
--
ALTER TABLE `plateforme`
  MODIFY `id_plateforme` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `avis_ibfk_1` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `bibliotheque`
--
ALTER TABLE `bibliotheque`
  ADD CONSTRAINT `bibliotheque_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`),
  ADD CONSTRAINT `bibliotheque_ibfk_2` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`);

--
-- Contraintes pour la table `commande`
--
ALTER TABLE `commande`
  ADD CONSTRAINT `commande_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`);

--
-- Contraintes pour la table `contenir`
--
ALTER TABLE `contenir`
  ADD CONSTRAINT `contenir_ibfk_1` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`),
  ADD CONSTRAINT `contenir_ibfk_2` FOREIGN KEY (`id_commande`) REFERENCES `commande` (`id_commande`);

--
-- Contraintes pour la table `historique_ventes`
--
ALTER TABLE `historique_ventes`
  ADD CONSTRAINT `historique_ventes_ibfk_1` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`) ON DELETE CASCADE;

--
-- Contraintes pour la table `jeu`
--
ALTER TABLE `jeu`
  ADD CONSTRAINT `fk_vendeur` FOREIGN KEY (`id_vendeur`) REFERENCES `utilisateur` (`id_user`),
  ADD CONSTRAINT `jeu_ibfk_1` FOREIGN KEY (`id_cat`) REFERENCES `categorie` (`id_cat`);

--
-- Contraintes pour la table `jeu_plateforme`
--
ALTER TABLE `jeu_plateforme`
  ADD CONSTRAINT `jeu_plateforme_ibfk_1` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`) ON DELETE CASCADE,
  ADD CONSTRAINT `jeu_plateforme_ibfk_2` FOREIGN KEY (`id_plateforme`) REFERENCES `plateforme` (`id_plateforme`) ON DELETE CASCADE;

--
-- Contraintes pour la table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `utilisateur` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
